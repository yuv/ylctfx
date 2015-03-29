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

// get session information
if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
  $ra = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
  $ra = $_SERVER['REMOTE_ADDR'];
}
$br = $_SERVER['HTTP_USER_AGENT'];
$ti  = gmdate('Y m d H:i:s', time());
//              $ht = gethostbyaddr($ra);
// 08-jul-04 removed gethostbyaddress because of problems with 1% of internet addresses
$ht=$_SERVER['REMOTE_HOST'];

// time in seconds allowed for user to fill the form
define(TIMEOUT,600);
// if we're around the change of the day, there is an exception, so find out
$a0=time();
$a1=$a0-TIMEOUT;
$d0=date('Ymd',$a0);
$d1=date('Ymd',$a1);
$t0=date('His',$a0);
// OLD: $hash=base64_encode($data['en_sysfname'].'_'.hash('sha256',$data['en_sysfname'].$salt.$t1.$t2).'_'.$t2);
// v.2: $hash=base64_encode($data['en_event'].'.'.$data['en_user'].'.'.$data['en_sequence'].'_'.hash('sha256',$data['en_event'].'.'.$data['en_user'].'.'.$data['en_sequence'].$salt.$t1.$t2).'_'.$t2);
$t = filter_input (INPUT_POST, 'i', FILTER_SANITIZE_STRING);
$hash=base64_decode($t);
$h=explode('_',$hash);
// check if the hash is OK
$v0=hash('sha256',$h[0].$salt.$d0.$h[2]);
$v1=hash('sha256',$h[0].$salt.$d1.$h[2]);
$d='';
if($v0==$h[1]){
    $d=$d0;
}
if($v1==$h[1]){
    $d=$d1;
}
if($d==''){
    // invalid / obsolete hash
    $valid_hash='NO';
    $return_diag.='_invalid time token';
}
// get y/m/d for now and for hash
$x0=str_split($d,2);
$x1=str_split($d0,2);
// get h/m/s for now and for hash
$y0=str_split($h[2],2);
$y1=str_split($t0,2);
// transform into timestamps
$z0=mktime($y0[0],$y0[1],$y0[2],$x0[1],$x0[2],$x0[0]);
$z1=mktime($y0[0],$y1[1],$y1[2],$x1[1],$x1[2],$x1[0]);
//die('a'.($z1-$z0));
if(($z1-$z0)>TIMEOUT){
  $valid_hash='NO';
  $return_diag.='_time token expired';
// for now we do not insist on time out
//  $return.='_Time Out.';
}else{
  $valid_hash='YES';
}

// validate captcha
$t = filter_input (INPUT_POST, 'l', FILTER_SANITIZE_STRING);
$c = filter_input (INPUT_POST, 'ccaptcha', FILTER_SANITIZE_NUMBER_INT);

if ($t==base64_encode(hash('sha256',$salt.$c))){
  $valid_captcha="YES";
} else {
  $valid_captcha="NO";
  $return.='_Bad Captcha.';
}

// subjet
$t = filter_input (INPUT_POST, 'csubject', FILTER_SANITIZE_STRING);
if (strlen($t)>1){
  $subject=$t;
}else{
  $subject = 'Web Form Message '.$_SERVER['HTTP_HOST'];
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
  $recipient = 'yuval@levy.ch';
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
$body.='                '.$_SERVER['HTTP_HOST'].CRLF;
$body.='Time-Valid:     '.$valid_hash.CRLF;
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