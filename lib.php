<?php

class Credential {

protected $paypalUrl = "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_ap-payment&paykey=";
protected $apiUrl = "https://svcs.sandbox.paypal.com/AdaptivePayments/";
protected $user = < user_id >;
protected $password = "< user_password >";
protected $signature = "< signature >";
protected $idapp = "APP-80W284485P519543T";	//ID APP Default Sandbox 

protected function __construct(){
	
	$this->paypalUrl = $paypalUrl;
	$this->apiUrl = $apiUrl;
	$this->user = $user;
	$this->password = $password;
	$this->signature = $signature;
	$this->idapp = $idapp;
	
	
	
}

protected function get_paypalUrl(){
	return $this->paypalUrl;
}

protected function get_apiUrl(){
	return $this->apiUrl;
}

protected function get_user(){
	return $this->user;
}

protected function get_password(){
	return $this->password;
}

protected function get_signature(){
	return $this->signature;
}

protected function get_idapp(){
	return $this->idapp;
}

}

class Paypal extends Credential{	

function __construct(){
	$this->headers = array(
	"Content-Type: text/html; charset=utf-8",
	"X-PAYPAL-SECURITY-USERID: " . $this->get_user(),
	"X-PAYPAL-SECURITY-PASSWORD: " . $this->get_password(),
	"X-PAYPAL-SECURITY-SIGNATURE: " . $this->get_signature() ,
	"X-PAYPAL-REQUEST-DATA-FORMAT: JSON" ,
	"X-PAYPAL-RESPONSE-DATA-FORMAT: JSON" ,
	"X-PAYPAL-APPLICATION-ID: " . $this->get_idapp()	
	);
	
	$this->apiUrl = $this->get_apiUrl();
	$this->paypalUrl = $this->get_paypalUrl();
	

}

function _paypalSend($data,$call){
	
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $this->apiUrl.$call);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER,$this->headers);

return json_decode(curl_exec($ch),TRUE);

}


function splitPay(){
	
// pay request
$createPacketPay = array(

"actionType"=>"PAY",
"currencyCode"=>"EUR",
"feesPayer"=>"EACHRECEIVER",
"memo"=>"Nome Product", 
"clientDetails"=>array(
"applicationId"=> $this->get_idapp(), 
"partnerName"=>"Your Name",
"ipAddress"=>"127.0.0.1"

),
"receiverList"=> array(
					"receiver"=>array(
									array(
									"amount"=>"0.10", // example 0.10 euro
									"email" => "< email_primary_receverer >",
									"primary"=>false,
									"paymentType"=>"SERVICE",
									
									),
									array(
									"amount"=>"2.00", // example 2.00 euro
									"email"=>"< email_secondary_receverer >", 
									"primary"=>true,
									"paymentType"=>"SERVICE"
																		
									)
									)
						),

"returnUrl"=>"http://localhost/returnUrl.php",	   //return url
"cancelUrl"=>"http://localhost/cancelUrl.php",	  //return url in case of cancellation
"requestEnvelope"=>array(
                          "errorLanguage"=>"en_US",
                           "detailLevel"=>"ReturnAll"						  
                         
                        ),
"reverseAllParallelPaymentsOnError"=>true
);

$responsePay = $this->_paypalSend($createPacketPay,"Pay");
return $responsePay;	
}


function getPayKey(){
	
$res = $this->splitPay();
$payKey = $res['payKey'];
return $payKey;
	
}


function getJSONresponse(){
	
$res = $this->splitPay();
$jsonresponse =  json_encode($res);
return $jsonresponse;
	
}

function getPaymentDetails($paykey){
	
$createPaymentDetails = array(

"payKey"=>$paykey,
"requestEnvelope"=>array("errorLanguage"=>"en_US")

);

//It returns the information of the payment request
$responsePaymentDetails = $this->_paypalSend($createPaymentDetails,"PaymentDetails");
return json_encode($responsePaymentDetails);	
	
}

}


$instt = new Paypal();
echo $instt->getJSONresponse();
echo "<hr>";
echo $instt->getPayKey();
echo "<hr>";
echo $instt->getPaymentDetails($instt->getPayKey());

//after obtaining the key pay redirect the user to the following url
//header("Location: https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_ap-payment&paykey=".$instt->splitPay());

?>
