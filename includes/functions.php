<?php

/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 28/01/2015
 * Time: 15:17
 */
class functions
{

    public function inputs()
    {
        if (is_array($_GET)) {
            foreach ($_GET as $gname => $gdata) {
                if (is_array($_GET[$gname])) {
                    foreach ($_GET[$gname] as $gname2 => $gdata2) {
                        $input[$this->clean_key($gname)][$this->clean_key($gname2)] = $this->_clean_val($gdata2);
                    }
                } else {
                    $input[$this->clean_key($gname)] = $this->_clean_val($gdata);
                }
            }
        }
        if (is_array($_POST)) {
            foreach ($_POST as $pname => $pdata) {
                if (is_array($_POST[$pname])) {
                    foreach ($_POST[$pname] as $pname2 => $pdata2) {
                        $input[$this->clean_key($pname)][$this->clean_key($pname2)] = $this->_clean_val($pdata2);
                    }
                } else {
                    $input[$this->clean_key($pname)] = $this->_clean_val($pdata);
                }
            }
        }
        return $input;
    }

    private function clean_key($k)
    {
        return $this->wash_key($k);
    }

    private function wash_key($key)
    {
        $key = htmlspecialchars($key, ENT_QUOTES);
        return $key;
    }

    private function _clean_val($v)
    {
        return $this->clean_string($v);
    }

    private function clean_string($clean)
    {
        if (!get_magic_quotes_gpc()) {
            $clean = addslashes($clean);
        }
        $clean = htmlspecialchars($clean);
        $clean = preg_replace("/&amp;#0*([0-9]*);?/", '&#\\1;', $clean);
        return $clean;
    }

} 