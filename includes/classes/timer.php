<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 27/01/2015
 * Time: 23:43
 */
$timer = new timer();

class timer
{
    var $timers = array();

    function start($name)
    {
        $this->timers[$name] = -microtime(true);
    }

    function stop($name, $round = 6)
    {
        //$stoptime = microtime();
        //$stoptime = explode(' ', $stoptime);
        //$stoptime = $stoptime[0] + $stoptime[1];

        //$totaltime = $stoptime - $this->timers[$name];
        //$totaltime = round($totaltime, $round);
        $time = $this->timers[$name];
        $time += microtime(true);
        return round($time, $round);
    }
}