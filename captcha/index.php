<?php

include('kcaptcha.php');
$captcha = new KCAPTCHA();
$_SESSION['captcha_key'] = $captcha->getKeyString();

?>