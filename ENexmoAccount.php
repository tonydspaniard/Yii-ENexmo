<?php

/**
 * 
 * ENexmoAccount
 * 
 * Handles nexmo's Rest API calls for accounts
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
class ENexmoAccount extends ENexmoBase {

	/**
	 *
	 * @var array $api_commands the available api calls for accounts
	 */
	private $api_commands = array(
		'get_balance' => array(
			'method' => 'GET', 
			'url' => 'account/get-balance/{k}/{s}'),
		'get_pricing' => array(
			'method' => 'GET', 
			'url' => 'account/get-pricing/outbound/{k}/{s}/{country-code}'),
		'get_own_numbers' => array(
			'method' => 'GET', 
			'url' => 'account/numbers/{k}/{s}'),
		'search_numbers' => array(
			'method' => 'GET', 
			'url' => 'number/search/{k}/{s}/{country-code}?pattern={search-pattern}'),
		'buy_number' => array(
			'method' => 'POST', 
			'url' => 'number/buy/{k}/{s}/{country-code}/{msisdn}'),
		'cancel_number' => array(
			'method' => 'POST', 
			'url' => 'number/cancel/{k}/{s}/{country-code}/{msisdn}'),
		'search_message' => array(
			'method' => 'GET', 
			'url' => 'search/message/{k}/{s}/{message-id}'),
		'search_messages' => array(
			'method' => 'GET', 
			'url' => '/search/messages/{k}/{s}?')
	);

	/**
	 *
	 * @var array maintains cached responses with non-variant results
	 */
	private $_cache = array();

	/**
	 * Holds the possible error codes
	 * @var array
	 */
	protected $error_codes;

	/**
	 *
	 * @param string $key the key provided by nexmo
	 * @param string $secret the secret provided by nexmo
	 */
	public function __construct($key, $secret)
	{
		foreach ($this->api_commands as $k => $conf)
			$this->api_commands[$k]['url'] = strtr($conf['url'], array('{k}' => $key, '{s}' => $secret));

		parent::__construct($key, $secret);
	}

	/**
	 *
	 * Retrieve your account balance
	 * @return JSON / XML response string - null if request has failed 
	 * @see http://www.nexmo.com/documentation/index.html#balance
	 */
	public function getBalance()
	{

		$call = $this->api_commands['get_balance'];
		$uri = $this->getApiUrl() . $call['url'];

		$response = $this->request($uri, array(), $call['method']);

		return $response->isSuccessful() ? $response->getBody() : null;
	}

	/**
	 * Retrieve our outbound pricing for a given country.
	 * @param $country_code Country code to return the SMS price for
	 * @return JSON / XML response string - null if request has failed 
	 * @see http://www.nexmo.com/documentation/index.html#pricing
	 */
	public function getSmsPricing($country_code)
	{
		$country_code = strtoupper($country_code);

		if (!isset($this->_cache['country_codes']))
			$this->_cache['country_codes'] = array();

		if (!isset($this->_cache['country_codes'][$country_code]))
		{
			$call = $this->api_commands['get_pricing'];
			$uri = $this->getApiUrl() . strtr($call['url'], array('{country-code}' => $country_code));

			$response = $this->request($uri, array(), $call['method']);

			$this->_cache['country_codes'][$country_code] = $response->isSuccessful() ? $response->getBody() : null;
		}

		return $this->_cache['country_codes'][$country_code];
	}

	/**
	 * Get all inbound numbers associated with your Nexmo account.
	 * @return JSON / XML response string - null if request has failed 
	 * @see http://www.nexmo.com/documentation/index.html#numbers
	 */
	public function getOwnNumbers()
	{
		$call = $this->api_commands['get_own_numbers'];
		$uri = $this->getApiUrl() . $call['url'];

		$response = $this->request($uri, array(), $call['method']);

		return $response->isSuccessful() ? $response->getBody() : null;
	}

	/* Get available inbound numbers for a given country.
	 * @param $country_code Country code to search available numbers in
	 * @return JSON / XML response string - null if request has failed 
	 * @see http://www.nexmo.com/documentation/index.html#search
	 */

	public function searchNumbers($country_code, $pattern)
	{

		if (!isset($this->_cache['search_numbers']))
			$this->_cache['search_numbers'] = array();

		$hash = base64_encode($country_code . $pattern);

		if (!isset($this->_cache['search_numbers'][$hash]))
		{
			$call = $this->api_commands['search_numbers'];

			$uri = $this->getApiUrl() . strtr(
					$call['url'], array('{country-code}' => strtoupper($country_code), '{pattern}' => $pattern));

			$response = $this->request($uri, array(), $call['method']);

			$this->_cache['search_numbers'][$hash] = $response->isSuccessful() ? $response->getBody() : null;
		}
		return $this->_cache['search_numbers'][$hash];
	}

	/**
	 * Purchase a given inbound number.
	 * @param string $country_code Country code. Ex: ES
	 * @param string $msidn An available inbound number Ex: 34911067000
	 * @return boolean true if successful, false otherwise
	 * @see http://www.nexmo.com/documentation/index.html#buy 
	 */
	public function buyNumber($country_code, $msidn)
	{
		$call = $this->api_commands['buy_number'];
		$uri = $this->getApiUrl() . strtr($call['url'], array('{country-code}' => strtoupper($country_code), '{msidn}' => $msidn));

		$response = $this->request($uri, array(), $call['method']);

		return $response->getStatus() == 200;
	}

	/**
	 * Cancel a given inbound number subscription.
	 * @param string $country_code
	 * @param string $msidn 
	 * @return boolean true if successful, false otherwise
	 * @see http://www.nexmo.com/documentation/index.html#cancel
	 */
	public function cancelNumber($country_code, $msidn)
	{
		$call = $this->api_commands['cancel_number'];
		$uri = $this->getApiUrl() . strtr($call['url'], array('{country-code}' => strtoupper($country_code), '{msidn}' => $msidn));

		$response = $this->request($uri, array(), $call['method']);

		return $response->getStatus() == 200;
	}

	/**
	 * Search a previously sent message for a given message id. 
	 * Please note a message become searchable a few minutes after 
	 * submission for real-time delivery notification implement our DLR call back.
	 * @param string $message_id Your message id received at submission time Ex: 00A0B0C0
	 * @return JSON / XML response string - null if request has failed 
	 * @see http://www.nexmo.com/documentation/index.html#message 
	 */
	public function searchMessage($message_id)
	{
		if (!isset($this->_cache['search_message']))
			$this->_cache['search_message'] = array();

		if (!isset($this->_cache['search_message'][$message_id]))
		{
			$call = $this->api_commands['search_message'];
			$uri = $this->getApiUrl() . strtr($call['url'], array('{message-id}' => $message_id));

			$response = $this->request($uri, array(), $call['method']);

			$this->_cache['search_message'][$message_id] = $response->isSuccessful() ? $response->getBody() : null;
		}

		return $this->_cache['search_message'][$message_id];
	}

	/**
	 * Search sent messages. Please note a message become searchable a few 
	 * minutes after submission for real-time delivery notification implement 
	 * nexmo DLR call back.
	 * @param array $ids the ids to search. They will converted to 
	 * a list of message ids, up to 10 Ex: ids=00A0B0C0&ids=00A0B0C1&ids=00A0B0C2
	 * @return JSON / XML response string - null if request has failed 
	 * @see http://www.nexmo.com/documentation/index.html#messages  
	 */
	public function searchMessagesByIds(array $ids)
	{
		if (!empty($ids) && count($ids) < 10)
		{
			$params = 'ids=' . implode('&ids=', $ids);

			$call = $this->api_commands['search_messages'];

			$uri = $this->getApiUrl() . $call['url'] . $params;

			$response = $this->request($uri, array(), $call['method']);

			return $response->isSuccessful() ? $response->getBody() : null;
		}
		return null;
	}

	/**
	 * Search sent messages. Please note a message become searchable a few 
	 * minutes after submission for real-time delivery notification implement 
	 * nexmo DLR call back.
	 * @param string $date Message date submission YYYY-MM-DD Ex: 2011-11-15
	 * @param string $to A recipient number Ex: 1234567890
	 * @return JSON / XML response string - null if request has failed 
	 * @see http://www.nexmo.com/documentation/index.html#messages 
	 */
	public function searchMessagesByDateAndRecipient($date, $to)
	{
		$call = $this->api_commands['search_messages'];

		$params = 'date=' . $date . '&to=' . $to;

		$uri = $this->getApiUrl() . $call['url'] . $params;

		$response = $this->request($uri, array(), $call['method']);

		return $response->isSuccessful() ? $response->getBody() : null;
	}

}