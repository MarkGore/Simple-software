<?php

class theme
{
    private $templates = null;

    function theme()
    {
        global $timer, $db, $botwith;
        $this->templates = $db->get('templates');
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
        }
        print $this->get($class);
    }

    function get($class)
    {
        global $timer, $db, $botwith;
        foreach ($this->templates as $template) {
            if ($template->title == $class) {
                $data = $template->template;
                if ($class == 'footer') {
                    $botwith->cache['main_timer'] = $timer->stop('main', 3);
                }
                eval("\$data = \"$data\";");

                return $data;
            }
        }
        return null;
    }
}