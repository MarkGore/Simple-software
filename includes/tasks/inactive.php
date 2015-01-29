<?php

/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 29/01/2015
 * Time: 17:04
 */
class inactive
{

    public function run()
    {
        global $db;
        $time = time() - (15 * 60); //15 mins

        $db->where('time' <= $time);
        foreach ($db->get('online') as $online) {
            $db->where('uid', $online->uid);
            $db->delete('online');
        }
    }
}