<?php
define('PATH_BOTWITH', str_replace('\\', '/', getcwd()) . '/');

require(PATH_BOTWITH . '/botwith.php');
$botwith_instance = new botwith();
$botwith_instance->init();

$render->parse(array('header'));

foreach ($db->get('categories') as $forum) {

    $botwith->cache['cate'] = new stdClass();
    $botwith->cache['cate']->title = $forum->title;
    $render->parse('categories');

    //print_r($forum);
}


$render->parse(array('online', 'footer'));
?>