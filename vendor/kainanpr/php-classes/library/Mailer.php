<?php

namespace Kainanpr;

use Rain\Tpl;

class Mailer {

	private  $mail;
	const USERNAME = "teste.ttt955@gmail.com";  //"teste_95@outlook.com";
	const PASSWORD = "asdf123456";              //"Asdf654321";
	const NAME_FROM = "Hcode Store";

	public function __construct($toAddress, $toName, $subject, $tplName, $data = array(), $tpl_dir = '/views/email/')
	{
		
		$config = array(
		"tpl_dir"=>$_SERVER["DOCUMENT_ROOT"]. $tpl_dir,
		"cache_dir"=>$_SERVER["DOCUMENT_ROOT"]. "/views-cache/",
		
		"debug"=> false //definido como falso para melhorar a velocidade
		);
		Tpl::configure( $config );
		$tpl = new Tpl;		
		
 		foreach ($data as $key => $value) {
		
		$tpl->assign($key, $value);
		
		}
		$html = $tpl->draw($tplName, true);
		//Create a new PHPMailer instance
		$this->mail = new \PHPMailer;
		//Tell PHPMailer to use SMTP
		$this->mail->isSMTP();
		$this->mail->SMTPOptions = array(
    		'ssl' => array(
    		'verify_peer' => false,
    		'verify_peer_name' => false,
    		'allow_self_signed' => true
    		)
 		);
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$this->mail->SMTPDebug = 0;
		//Set the hostname of the mail server
		$this->mail->Debugoutput = 'html';
		$this->mail->Host = 'smtp.gmail.com';
		// use
		// $this->mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6
		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$this->mail->Port = 587;
		//Set the encryption system to use - ssl (deprecated) or tls
		$this->mail->SMTPSecure = 'tls';
		//Whether to use SMTP authentication
		$this->mail->SMTPAuth = true;
		//Username to use for SMTP authentication - use full email address for gmail
		$this->mail->Username = Mailer::USERNAME;
		//Password to use for SMTP authentication
		$this->mail->Password = Mailer::PASSWORD;
		//Set who the message is to be sent from
		$this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);
		//Set an alternative reply-to address
		//$this->mail->addReplyTo($toAddress, $toName);
		//Set who the message is to be sent to
		$this->mail->addAddress($toAddress, $toName);
		//Set the subject line
		$this->mail->Subject = $subject;
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$this->mail->msgHTML($html);
		//Replace the plain text body with one created manually
		$this->mail->AltBody = 'This is a plain-text message body';
		//Attach an image file
		//$this->mail->addAttachment('images/phpmailer_mini.png');

		$this->mail->CharSet = 'UTF-8';
	}
	
	public function send()
	{
		return $this->mail->send();
	}
}
?>