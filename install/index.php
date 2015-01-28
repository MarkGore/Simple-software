<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 27/01/2015
 * Time: 23:52
 */

@include('../config.php');

require('../includes/classes/timer.php');


if (empty($_GET)) {
    require_once('/modules/index.php');
} else {
    require_once('/modules/' . key($_GET) . '.php');
}

function execute_queries()
{
    include('dbstructure.php');
    foreach ($creates as $query) {
    }
}

?>