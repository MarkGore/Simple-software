<?php

error_reporting(E_ERROR | E_PARSE | E_WARNING);

define('BOTWITH_VERSION', '1.0'); // [MAJOR].[MINOR]
require(PATH_BOTWITH . 'includes/classes/timer.php');
$timer = new Timer();
$timer->start('main');
include(PATH_BOTWITH . 'config.php');

/**
 * From here we will redirect to install if the site hasn't been installed! :o
 */
if (empty($config['db_username'])) {
    header('Location: install/index.php');
    echo "<meta http-equiv='Refresh: 3;install/index.php' /><a href='install/index.php'>Click here if you are not redirected...</a>";
    exit();
}

require(PATH_BOTWITH . 'includes/classes/database.php');
$db = new database($config['db_host'], $config['db_database'], $config['db_username'], $config['db_password']);

require(PATH_BOTWITH . 'modules/templates/theme.php');
$render = new theme();

$botwith = new botwith_globals();

class botwith_globals
{
    var $config;
    var $tasks;
    var $cache;
    var $input;
    var $functions;

    function botwith_globals()
    {
        global $config;
        $this->config = $config;
    }
}

class botwith
{
    public $completed = false;

    function init()
    {
        global $botwith;
        $this->init_functions();
        $this->init_session();
        $this->init_cache();
        $this->init_tasks();
    }

    function init_functions()
    {
        global $botwith, $db;
        require(PATH_BOTWITH . 'includes/functions.php');
        $botwith->functions = new functions();
        $botwith->input = $botwith->functions->inputs();
        $botwith->functions->check_cookies();
    }

    function init_session()
    {
        global $botwith;
        foreach ($_COOKIE as $cook => $dat) {
            if (strpos($cook, $botwith->config['cookie_prefix'])) {
                $cook = str_replace($botwith->config['cookie_prefix'], '', $cook);
                echo wash_key($dat);
            }
        }
    }

    function init_cache()
    {
        global $botwith, $db;
        foreach ($db->get('cache') as $cache) {
            $botwith->cache[$cache->name] = $cache->content;
        }
    }

    function init_tasks()
    {
        global $botwith;
        include(PATH_BOTWITH . 'includes/classes/tasks.php');
        $tasks = new Tasks();
    }

}