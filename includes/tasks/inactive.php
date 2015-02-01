<?php

/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 29/01/2015
 * Time: 17:04
 */
class inactive
{

    public function execute()
    {
        global $db;
        $time = time() + (15 * 60); //15 mins

        $db->where('time', $time, '<=');
        $db->delete('online');
    }
}