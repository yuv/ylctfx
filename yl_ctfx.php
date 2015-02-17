<?php
/**
** yl_ctfx.php
**
** (C) 2002-2015 Yuval Levy, http://www.photopla.net
**
** This file is part of the YLCTFX project.
**
** Released under the MIT license (see LICENSE file).
**
** USAGE: include within HTML <HEAD> tag of a page pre-processed by PHP
**
*/

// make sure the salt is the same as in the form processor yl_ajctc.php
$salt="SALT";

// get session information
if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
  $ra = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
  $ra = $_SERVER['REMOTE_ADDR'];
}
$br = $_SERVER['HTTP_USER_AGENT'];
$t  = gmdate('Y m d H:i:s', time());
//              $ht = gethostbyaddr($ra);
// 08-jul-04 removed gethostbyaddress because of problems with 1% of internet addresses
$ht=$_SERVER['REMOTE_HOST'];

// todaysdate as YYYYMMDDHHMMSS as part of the token
$t1=date('Ymd');
$t2=date('His');
$hash=base64_encode($e[0].'.'.$e[1].'.'.$e[2].'_'.hash('sha256',$e[0].'.'.$e[1].'.'.$e[2].$salt.$t1.$t2).'_'.$t2);

// generate captcha and hashed solution
// minimum standard captcha difficulty
// roughly one out of 2*MAX_CAPTCHA*(number of operators) spambot messages will pass through
if(!defined('MAX_CAPTCHA')){define('MAX_CAPTCHA',10);}
// array of possible captcha operations
$capop=array('+','-','*');
// generate a new captcha
$result=0;
while($result==0){
  $f1=rand(0,MAX_CAPTCHA);
  $f2=rand(0,MAX_CAPTCHA);
  $op=$capop[rand(0,count($capop)-1)];
  $captcha=$f1.' '.$op.' '.$f2;
  $result=eval('return('.$captcha.');');
  $captcha=$f1.'&nbsp;'.$op.'&nbsp;'.$f2;
  $hash_captcha=base64_encode(hash('sha256',$salt.$result));
}
?> 
<link rel="stylesheet" type="text/css" media="screen" href="./css/yl_ctfx.css" />
<script type="text/javascript" src="./js/jquery.min.js"></script>
<script type="text/javascript" src="./js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./js/jquery.form.js"></script>
<script type="text/javascript" src="./js/yl_recipients.js"></script>
<script type="text/javascript">
  var yl_c = "<?php echo $captcha; ?>";
  var yl_h = "<?php echo $hash; ?>";
  var yl_hc = "<?php echo $hash_captcha; ?>";
</script>
<script type="text/javascript" src="./js/yl_ctfx.js"></script>