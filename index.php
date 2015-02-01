<?php
define('PATH_BOTWITH', str_replace('\\', '/', getcwd()) . '/');

require(PATH_BOTWITH . '/botwith.php');
$botwith_instance = new botwith();
$botwith_instance->init();

$render->parse(array('header'));

$botwith_instance->init_module('forums');

$render->parse(array('online', 'footer'));

?>