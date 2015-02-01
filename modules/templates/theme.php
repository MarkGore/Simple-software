<?php

class theme
{
    private $templates = null;

    function theme()
    {
        global $timer, $db, $botwith;
        $temp = $db->get('templates');
        foreach ($db->get('templates') as $temp) {
            $this->templates[$temp->title] = $temp;
        }
        //$this->templates = $db->get('templates');
    }

    function parse($class)
    {
        if (is_array($class)) {
            foreach ($class as $rend) {
                $parse = $this->get($rend);
                if (!empty($parse)) {
                    print $parse;
                }
            }
        } else {
            print $this->get($class);
        }
    }

    private function get($class)
    {
        global $timer, $db, $botwith;
        $temp = $this->templates[$class];
        if (!is_array($class) && isset($temp)) {
            $data = $temp->template;
            if ($class == 'footer') {
                $botwith->cache['main_timer'] = $timer->stop('main', 3);
            }
            eval("\$data = \"$data\";");
            return $data;
        }
        return null;
    }

    function render($data)
    {
        eval("\$data = \"$data\";");
        print $data;
    }
}