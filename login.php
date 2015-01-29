<?php
define('PATH_BOTWITH', str_replace('\\', '/', getcwd()) . '/');

require(PATH_BOTWITH . '/botwith.php');

$botwith_instance = new botwith();
$botwith_instance->init();

if ($botwith->functions->loggedin()) {
    $botwith->functions->redirect();
    exit();
}
if (isset($botwith->input)) {
    $username = $botwith->input['login'];
    $password = $botwith->input['password'];
    $botwith->functions->auth_login($username, $password);
}
$render->parse(array('header', isset($botwith->cache['danger-alert']) ? 'alert-danger' : '', 'login', 'footer'));
?>