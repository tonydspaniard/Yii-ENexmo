<?php

/**
 * ENexmoSms class
 * 
 * Sends SMS messages -text, binary and push
 * 
 * @see http://www.nexmo.com/documentation/#txt
 * 
 * @author Antonio Ramirez Cobos
 * @link www.ramirezcobos.com
 * 
 * 
 * @copyright 
 * 
 * Copyright (c) 2012 Antonio Ramirez
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software 
 * and associated documentation files (the "Software"), to deal in the Software without restriction, 
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, 
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, 
 * subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial 
 * portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
 * LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN
 * NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, 
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE 
 * OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */
class ENexmoSms extends ENexmoBase {

	/**
	 *
	 * @var string the API constructed uri to send messages
	 */
	protected $_uri;

	/**
	 *
	 * @param string $key the key provided by nexmo
	 * @param string $secret the secret provided by nexmo
	 */
	public function __construct($key, $secret)
	{
		$this->_uri = $this->_api . 'sms/';

		parent::__construct($key, $secret);
	}

	/**
	 *
	 * @return string the api uri with chosen format response
	 */
	public function getApiUrl()
	{
		return $this->_uri . $this->getFormat();
	}

	/**
	 * Executes a call to the api. Parent request returns EHttpResponse type
	 * this overrided function makes sure the returned value is the body.
	 * @param string $url
	 * @param array $params
	 * @param string $method
	 * @param array $curlOptions
	 * @return string 
	 */
	public function request($url, array $params = array(), $method = 'GET', $curlOptions = array())
	{
		$response = parent::request($url, $params, $method, $curlOptions);
		return ($response->isSuccessful()) ? $response->getBody() : null;
	}

	/**
	 * Sends a SMS message
	 * @param string $to Mobile number in international format. Ex: to=447525856424 or to=00447525856424
	 * @param string $from Sender address could be alphanumeric (Ex: from=MyCompany20). Restrictions may
	 * 	apply depending on destination: https://nexmo.zendesk.com/entries/20427093-what-to-specify-in-the-from-field-in-us-and-canada
	 * @param string $message
	 * @param boolean $unicode if null try to detect message type, otherwise
	 * 	set to TRUE if unicode characters are required.
	 * @param array $optional optional parameters to the call
	 * @param boolean $forceNumericRecipient forces $from
	 * @return JSON / XML response string - null if request has failed 
	 * @see http://www.nexmo.com/documentation/index.html#txt
	 */
	public function sendTextMessage($to, $from, $message, $unicode=null, $optional = array(), $forceNumericSender = false)
	{


		if ($forceNumericSender && !is_numeric($from))
			throw new CException(Yii::t('ENexmo', '{from} requires to be a numeric value.', array('{from}' => $from)));

		$to = $this->_validateUTF8($to);
		$from = $this->_validateUTF8($from);

		$from = $this->_validateOriginator($from);

		$from = urlencode($from);
		//$message = urlencode($message);

		if (null === $unicode)
			$containsUnicode = max(array_map('ord', str_split($message))) > 127;
		else
			$containsUnicode = (bool) $unicode;

		$params = array(
			'from' => $from,
			'to' => $to,
			'text' => $message,
			'type' => $containsUnicode ? 'unicode' : 'text'
		);

		if (is_array($optional))
			$params = array_merge($optional, $params);

		return $this->request($this->getApiUrl(), $params, EHttpClient::POST);
	}

	/**
	 * Sends a binary message
	 * 
	 * @param string $to Mobile number in international format. Ex: to=447525856424 or to=00447525856424
	 * @param string $from Sender address could be alphanumeric (Ex: from=MyCompany20). Restrictions may
	 * @param string $body Content of the message
	 * @param string $udh User Data Header
	 * @return JSON / XML response string - null if request has failed 
	 * @see http://www.nexmo.com/documentation/index.html#bin
	 */
	public function sendBinary($to, $from, $body, $udh)
	{
		$to = $this->_validateUTF8($to);
		$from = $this->_validateUTF8($from);

		// Make sure $from is valid
		$from = $this->_validateOriginator($from);


		// Hex encoded binary data. Ex: body=0011223344556677
		$body = bin2hex($body);
		// Hex encoded udh. Ex: udh=06050415811581
		$udh = bin2hex($udh);

		$params = array(
			'from' => $from,
			'to' => $to,
			'type' => 'binary',
			'body' => $body,
			'udh' => $udh
		);
		return $this->request($this->getApiUrl(), $params, EHttpClient::POST);
	}

	/**
	 * Sends a wap push
	 *
	 * @param string $to
	 * @param string $from
	 * @param string $title Title of WAP Push. Ex: title=MySite
	 * @param string $url WAP Push URL. Ex: url=http://www.mysite.com
	 * @param  $validity Set how long WAP Push is available in milliseconds. Ex: validity=86400000 Default: 48 hours.
	 * @return  JSON / XML response string - null if request has failed 
	 * @see  http://www.nexmo.com/documentation/index.html#wap
	 */
	public function pushWap($to, $from, $title, $url, $validity = 172800000)
	{

		$to = $this->_validateUTF8($to);
		$from = $this->_validateUTF8($from);
		$title = $this->_validateUTF8($title);
		$url = $this->_validateUTF8($url);

		$from = $this->_validateOriginator($from);

		// Send away!
		$params = array(
			'from' => $from,
			'to' => $to,
			'type' => 'wappush',
			'url' => $url,
			'title' => $title,
			'validity' => $validity
		);
		return $this->request($this->getApiUrl(), $params, EHttpClient::POST);
	}

	/**
	 * All requests are submitted through the HTTP POST or GET method. 
	 * All requests require UTF-8 encoding
	 * 
	 * @param string $text to check for UTF-8 encoding
	 * @return string 
	 */
	private function _validateUTF8($text)
	{
		return (!mb_check_encoding($text, 'UTF-8')) ? utf8_encode($text) : $text;
	}

	/**
	 * Validate an originator string
	 *
	 * If the originator ('from' field) is invalid, some networks may reject the network
	 * whilst stinging you with the financial cost! While this cannot correct them, it
	 * will try its best to correctly format them.
	 */
	private function _validateOriginator($inp)
	{
		$ret = preg_replace('/[^a-zA-Z0-9]/', '', (string) $inp);

		if (preg_match('/[a-zA-Z]/', $inp))
			$ret = substr($ret, 0, 11);
		else
		{
			if (substr($ret, 0, 2) == '00')
			{
				$ret = substr($ret, 2);
				$ret = substr($ret, 0, 15);
			}
		}
		return (string) $ret;
	}

}