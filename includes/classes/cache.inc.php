<?php

class cache_func
{

    function cache_func()
    {
        global $botwith, $db;
        if (is_array($botwith->cache)) {
            $this->cache = $botwith->cache;
        }
    }

    public function rebuild_cache($cache)
    {
        switch (cache) {
            case 'stats': {
                break;
            }
        }
    }

    public function get($cache)
    {
        return $this->cache[$cache];
    }

}