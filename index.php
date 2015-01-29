<?php
define('PATH_BOTWITH', str_replace('\\', '/', getcwd()) . '/');

require(PATH_BOTWITH . '/botwith.php');
$botwith_instance = new botwith();
$botwith_instance->init();
//$botwith->functions->reset_cookie('user');
$render->parse(array('header', 'categories', 'online', 'footer'));
?>