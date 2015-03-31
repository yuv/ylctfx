<?php
/**
** yl_ajctc.php
**
** (C) 2002-2015 Yuval Levy, http://www.photopla.net
**
** This file is part of the YLCTFX project.
**
** Released under the MIT license (see LICENSE file).
**
** CONFIGURATION:
**  * synchronize contacts with js/yl_recipients.js
**  * synchronize $salt with yl_ctfx.php
**
*/
/****************************************************
** definition of sitewide points of e-mail contact **
** when updating here, update yl_recipients.js too **
** Yuval Levy 28-jul-02                            **
****************************************************/
$trgt=array();
$trgt['webmaster']  =array('name' => 'Webmaster'
                          ,'rec'  => 'webmasterl@example.com');
$trgt['mrsample']      =array('name' => 'Mr. Sample'
                          ,'rec'  => 'sample@example.com');
$trgt['desk']        =array('name' => 'Information Desk'
                          ,'rec' => 'info@example.com');
// make sure the salt is the same as in the form generation in contact.php
$salt="SALT";

define('CR',"\n");
define ('CRLF',"\r\n");

/**
* return is a list of messages to be returned, separated by an underscore
*/
$return='';
/**
* return_diag is an added list diagnostic strings separated by an underscore
* returned in diagnostic mode only
*/
$return_diag='';

// recipient
$t = filter_input (INPUT_POST, 'target', FILTER_SANITIZE_STRING);
if(array_key_exists($t, $trgt)){
  $recipient_name = $trgt[$t]['name'];
  $recipient = $trgt[$t]['rec'];
}else{
  $recipient_name = 'emergency_contact_'.$t;
  $recipient = 'webmaster@example.com';
}

// get session information
$ra = getenv('REMOTE_ADDR');
if (getenv('HTTP_X_FORWARDED_FOR')) {
  $ra = $ra.' forwarded for '.getenv('HTTP_X_FORWARDED_FOR');
}
$br = getenv('HTTP_USER_AGENT');
$ti = gmdate('Y m d H:i:s', time());
$ht = getenv('REMOTE_HOST');

// time in seconds allowed for user to fill the form
define(TIMEOUT,600);

// validate captcha
$t = filter_input (INPUT_POST, 'l', FILTER_SANITIZE_STRING);
$c = filter_input (INPUT_POST, 'ccaptcha', FILTER_SANITIZE_NUMBER_INT);

$hash=base64_decode($t);
$h=explode('_',$hash);

$v=hash('sha256',$salt.$h[1].$h[2].$c).'_'.$h[1].'_'.$h[2];

if ($v==$hash){
  $valid_captcha="YES";
} else {
    // invalid / obsolete hash
    $valid_captcha='NO';
    $return.='_Bad Captcha';
}

// validate timestamp
$cutoff=date('YmdHis',(time()-TIMEOUT));
//$return.='_Time_'.$cutoff.'_'.$h[1].$h[2];
if($h[1].$h[2]<$cutoff){
  $return.='_Timeout: Save your entry and reload the page.';
}

// subjet
$t = filter_input (INPUT_POST, 'csubject', FILTER_SANITIZE_STRING);
if (strlen($t)>1){
  $subject=$t;
}else{
  $subject = 'Web Form Message '.getenv('HTTP_HOST');
  $return_diag.='_no subject';
}

// message
$message = filter_input (INPUT_POST, 'ccomment', FILTER_SANITIZE_STRING);

// diagnostics
$diag_post = print_r ( $_POST , true );

// sender
$t = filter_input (INPUT_POST, 'cemail', FILTER_VALIDATE_EMAIL);
if (filter_var($t, FILTER_VALIDATE_EMAIL)) {
  $sender = $t;
  $sender_name = filter_input (INPUT_POST, 'cname', FILTER_SANITIZE_STRING);
} else {
  $valid_sender = 'NO';
  $return.='_Invalid Sender.';
}

// recipient of last resort
if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
  $recipient = 'webmaster@example.com';
  $return_diag.='_fallback recipient of last resort';
}

// body
$body.='-Message---------------------------------------------------'.CRLF;
$body.=$message.CRLF;
$body.='-Session Variables-----------------------------------------'.CRLF;
$body.='Remote Address: '.$ra.CRLF;
$body.='User Agent:     '.$br.CRLF;
$body.='GMT Time:       '.$ti.CRLF;
$body.='Host:           '.$ht.CRLF;
$body.='                '.getenv('HTTP_HOST').CRLF;
$body.='Captcha:        '.$valid_captcha.CRLF;
$body.='-----------------------------------------------------------'.CRLF;

// change this condition to get some diagnostic code
if (0==1){
  $body.=$diag_post;
  $body.=CRLF;
  $body.='subject         '.$csubject.CRLF;
  $body.='recipient       '.$recipient.CRLF;
  $body.='                '.$recipient_name.CRLF;
  $body.='sender          '.$sender.CRLF;
  $body.='                '.$sender_name.CRLF;

  // $a=mail('debug@example.com', $subject, $body, $sender);
  // $return=mail($recipient, $subject, $body, $sender);
}

//$return='artificial error';

// send the message only if all conditions are met
if ($return==''){

  // send (temporary - craft better email later on)
  require_once __DIR__.'/swift/lib/swift_required.php';

  //Create the message
  $message = Swift_Message::newInstance();

  //Give the message a subject
  $message->setSubject($subject);

  //Give it a body
  $message->addPart($body, 'text/plain');

  $message->setTo(array($recipient => $recipient_name));
  // Set the return path
//  $message->setReturnPath('yuval@levy.ch');

  //Set the From address with an associative array
  $message->setFrom(array($sender => $sender_name));

  $transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');

  //Create the Mailer using your created Transport
  $mailer = Swift_Mailer::newInstance($transport);

  //send the message
  $numSent = $mailer->send($message);

  $return='_Sent.';

}

// Debug wait to test ajax
// sleep(5);

$xml = "<p>'.$return.'</p>";
// header('Content-type: text/html');
header('Content-type: text/plain');
//echo $xml;
echo $return;
?>