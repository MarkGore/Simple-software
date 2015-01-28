<?php
define('PATH_BOTWITH', str_replace('\\', '/', getcwd()) . '/');

require(PATH_BOTWITH . '/botwith.php');

$botwith_instance = new botwith();

$botwith_instance->init();

if (!empty($botwith->input)) {
    $username = $botwith->input['login'];
    $password = $botwith->input['password'];
    $test = $botwith->functions->auth_login($username, $password);
    print_r($test);

}

$render->parse(array('header', 'login', 'footer'));
?>