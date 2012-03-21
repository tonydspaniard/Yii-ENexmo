<?php
/**
 * 
 * ENexmoInboundCallback class
 * 
 * Checks whether an inbound message has arrived and fires an event. Useful for
 * inbound sms nexmo's callback checks
 * 
 * @author Antonio Ramirez Cobos
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
class ENexmoInboundCallback extends CAction {
	
	/**
	 * Checks whether a message inbound has been sent and raises the event
	 * with Inbound message data
	 * @return void
	 */
	public function run(){

		$data = array_merge($_GET, $_POST);
		
		if(isset($data['text'], $data['msisdn'], $data['to']))
		{

			$event = new ENexmoInboundEvent($this, $data);

			$this->onInboundMessage($event);
		}
		else
			Yii::log(Yii::t('ENexmo', 'Invalid nexmo inbound call.'));
	}
	
	/**
	 * Raised when a new message has been received
	 * @param ENexmoInboundEvent $event 
	 */
	public function onInboundMessage($event)
	{
		$this->raiseEvent('onInboundMessage', $event);
	}
}
/**
 * ENexmoInboundEvent class
 *
 * @author Antonio Ramirez <antonio@ramirezcobos.com>
 */
class ENexmoInboundEvent extends CEvent {
	
	/**
	 * Constructor.
	 * @param mixed $sender sender of the event
	 * @param mixed $params additional parameters for the event
	 */
	public function __construct($sender = null,  $params = null)
	{
		$this->params = array(
			'type'=>null,
			'username'=>null,
			'password'=>null,
			'to'=>null,
			'msisdn'=>null,
			'network-code'=>null,
			'messageId'=>null,
			'text'=>null,
			'concat'=>null,
			'concat-ref'=>null,
			'concat-total'=>null,
			'concat-part'=>null,
			'data'=>null,
			'udh'=>null
		);
		parent::__construct($sender, $this->_collectParameters($params));	
	}

	/**
	 * Collects the parameters from the passed array (so user could pass $_GET)
	 * @param array $params 
	 */
	private function _collectParameters($params)
	{
		if(is_array($params))
		{
			foreach(array_keys($this->params) as $key)
			{
				if(isset($params[$key]))
					$this->params[$key] = $params[$key];
			}
		}
		
	}
	/**
	 * Message ID.
	 * @return string
	 */
	public function getMessageId()
	{
		return $this->params['messageId'];
	}
	/**
	 * Optional username for authentication.
	 * @return string 
	 */
	public function getUsername(){
		return $this->params['username'];
	}
	
	/**
	 * Optional password for authentication.
	 * @return string
	 */
	public function getPassword() {
		return $this->params['password'];
	}
	/**
	 * Expected values are: "text" (valid for standard GSM, arabic, chinese ... characters) or "binary"
	 * @return string
	 */
	public function getType()
	{
		return $this->params['type'];
	}
	/**
	 * Recipient number (your long virtual number).
	 * @return string 
	 */
	public function getTo()
	{
		return $this->params['to'];
	}
	/**
	 * Sender ID
	 * @var string
	 */
	public function getFrom(){
		return $this->params['msisdn'];
	}
	
	/**
	 * Content of the message
	 * @return string
	 */
	public function getText(){
		return $this->params['text'];
	}
	/** specific parameters for binary **/
	/**
	 * User Data Header (hex encoded)
	 * @return string
	 */
	public function getUDH(){
		return $this->params['udh'];
	}
	/**
	 * Content of the message
	 * @return string
	 */
	public function getData(){
		return $this->params['data'];
	}
	/** specific parameters for long 'cocatenated' inbound **/
	/**
	 * The part number of this message within the set
	 * @return integer
	 */
	public function getConcatenatedPart(){
		return intval($this->params['concat-part']);
	}
	/**
	 * The total number of parts in this concatenated message set
	 * @return integer
	 */
	public function getConcatenatedTotal(){
		return intval($this->params['concat-total']);
	}
	/**
	 * Transaction reference, all message parts will shared the same 
	 * transaction reference
	 * @return string 
	 */
	public function getConcatenatedRef(){
		return $this->params['concat-ref'];
	}
	/**
	 * Set to true if a MO concatenated is detected
	 * @return boolean
	 */
	public function getConcatenated()
	{
		return (bool)$this->params['concat'];
	}
	

	
	
}