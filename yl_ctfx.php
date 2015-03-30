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

// generate captcha and hash

// NOTE: keep problem simple for humans to solve
// avoid increasing the cost of solution because it is
// more expensive to human than to machine

// NOTE: the math problem is displayed in the clear.
// since it must be shown to the user, it can't be secret.
// might be useful to introduce machine-obfuscation such as
// graphic display

// minimum standard captcha difficulty

// roughly one out of 2*MAX_CAPTCHA*(number of operators) spambot messages will pass through if brute-forcing
// TODO: in yl_ajctc.php create a counter for failed attempts per IP address and limit them to stay below this maximum per timeout period.

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
  $captcha=$f1.'&#160;'.$op.'&#160;'.$f2;
  // timestamp
  $t1=date('Ymd');
  $t2=date('His');

  // hash the salt, the timestamp and the result
  // pass the timestamp in the clear as well for verification
  $hash_captcha=base64_encode(hash('sha256',$salt.$t1.$t2.$result).'_'.$t1.'_'.$t2);

}
?> 
<link rel="stylesheet" type="text/css" media="screen" href="./css/yl_ctfx.css" />
<script type="text/javascript" src="./js/jquery.min.js"></script>
<script type="text/javascript" src="./js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./js/jquery.form.js"></script>
<script type="text/javascript" src="./js/yl_recipients.js"></script>
<script type="text/javascript">
  var yl_c = "<?php echo $captcha; ?>";
  var yl_hc = "<?php echo $hash_captcha; ?>";
</script>
<script type="text/javascript" src="./js/yl_ctfx.js"></script>