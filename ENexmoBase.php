<?php

/**
 * 
 * ENexmoBase
 * 
 * Nexmo API base class
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
/**
 * 
 * @uses EHttpClient http://www.yiiframework.com/extension/ehttpclient/
 */
class ENexmoBase extends CComponent {
	/**
	 * Nexmo provides you with an option of a response as a JSON object, 
	 * or an XML string-you get to choose which response by selecting the 
	 * appropriate base URL for your request.
	 */
	const FORMAT_JSON = 'json';
	const FORMAT_XML = 'xml';

	/**
	 *
	 * @var string response format (defaults to json)
	 */
	protected $format = 'json';
	
	/**
	 *
	 * @var string $_api nexmo default's rest uri
	 */
	protected $_api = 'https://rest.nexmo.com/';
	
	/**
	 *
	 * @var string $_api_key your API key
	 */
	protected $_api_key;
	
	/**
	 *
	 * @var string $_api_secret from nexmo
	 */
	protected $_api_secret;

	/**
	 * Class constructor 
	 * @param string $key
	 * @param string $secret
	 */
	public function __construct($key, $secret)
	{
		$this->_api_key = $key;
		$this->_api_secret = $secret;
	}

	/**
	 * Returns api url
	 * @return string
	 */
	public function getApiUrl()
	{
		return $this->_api;
	}

	/**
	 * Returns api key
	 * @return string
	 */
	public function getKey()
	{
		return $this->_api_key;
	}

	/**
	 * Returns secret
	 * @return string your nexmo secret
	 */
	public function getSecret()
	{
		return $this->_api_secret;
	}

	/**
	 * @return string the format response (JSON or XML)
	 */
	public function getFormat()
	{
		return $this->format;
	}

	/**
	 * Sets the format response (JSON or XML)
	 * @param string $format 
	 */
	public function setFormat($format)
	{
		$format = trim(strtolower($format));
		if ($format != self::FORMAT_JSON && $format != self::FORMAT_XML)
			$format = self::FORMAT_JSON;

		$this->format = $format;
	}

	/**
	 * 
	 * Makes a Url request and returns its response
	 * @param string $url the url to call
	 * @param array $params the parameters to bound to the call
	 * @param string $method POST | GET
	 * @param array $curlOptions CURLOPTS ... *careful with certain options
	 *	and they may lead to cause issues. Use default EHttpClient methods.
	 * @return EHttpResponse 
	 */
	public function request($url, array $params=array(), $method='GET', $curlOptions=array())
	{

		$curlOptions[CURLOPT_USERAGENT] = 'Mozilla/5.0 (X11; U; Linux x86_64; de; rv:1.9.2.8) Gecko/20100723 Ubuntu/10.04 (lucid) Firefox/3.6.8';
		
		$config = array(
			'adapter' => 'EHttpClientAdapterCurl',
			'timeout' => '60',
			'curloptions' => $curlOptions );

		$client = new EHttpClient(EUri::factory($url), $config);

		$params = array_merge($params, array('username' => $this->getKey(), 'password' => $this->getSecret()));

		if (preg_match('/get/i', $method) && !empty($params))
			$client->setParameterGet($params);
		if (preg_match('/post/i', $method) && !empty($params))
			$client->setParameterPost($params);

		$client->setHeaders('Accept', 'application/'.$this->getFormat());
		
		return $client->request($method);
 
	}
}