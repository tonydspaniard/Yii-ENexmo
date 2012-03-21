=========================
ENexmo Library 1.0.1 Manual 
=========================
ENexmo Library allows Yii programmers to use the Restful API offered by Mobile 
Messaging provider `Nexmo <http://www.nexmo.com/index.html`_.

Nexmo is a cloud-based SMS API that lets you send and receive high volume 
of messages at wholesale rates.


========
Requires
========

    * `Nexmo API credentials <http://dashboard.nexmo.com/register>`_
    * `EHttpClient Extension <http://www.yiiframework.com/extension/ehttpclient/>`_
    * `Yii Framework <http://www.yiiframework.com>`_

===========
Quick Start
===========

Once you have created your Nexmo account and include ENexmo library and EHttpClient
on the extension folder is as easy as this::
	
	/* import extensions before any call */
	Yii::import('ext.httpclient.*');
	Yii::import('ext.nexmo.*');

	/* to send a message */
	$nexmo_sms = new ENexmoSms('YOURAPIKEY','YOURAPISECRET');
		
	$response = $nexmo_sms->sendTextMessage('RECIPIENTSNUMBER','SENDERID','Howdy testing! Please, search on http://www.google.com.');

	/* responses are on JSON or XML. Defaults to JSON, but you can change that */
	/* we use CHtml::encode for demo purposes only, to check for responses */
	echo CHtml::encode($response);

	/* to make requests for account */
	$nexmo_account = new ENexmoAccount('key','secret');
	$nexmo_account->format = ENexmoBase::FORMAT_JSON;

	/* to search for a message */
	echo CHtml::encode($nexmo_account->searchMessage('09AFDA98'));
	//echo CHtml::encode($nexmo_account->searchMessagesByIds(array('09AFCC5B','09AFDA98')));
	//echo CHtml::encode($nexmo_account->searchMessagesByDateAndRecipient('2012-03-20','34607040932'));
	//echo CHtml::encode($nexmo_account->balance);
	//echo CHtml::encode($nexmo_account->ownNumbers);
	//echo CHtml::encode($nexmo_account->getSmsPricing('ES'));

Please check Nexmo API Documentation `Nexmo API Documentation <http://www.nexmo.com/documentation/>`_
