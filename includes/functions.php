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

    public function valid_cookie($cookie_name)
    {
        global $botwith;
        if (isset($_COOKIE[$botwith->config['cookie_prefix'] . $cookie_name])) {
            return $this->wash_key($_COOKIE[$botwith->config['cookie_prefix'] . $cookie_name]);
        } else {
            return false;
        }
    }

    public function auth_login($username, $password)
    {
        global $botwith, $db;
        $db->where('username', $username);
        $user = $db->getOne('users');
        if (!empty($user)) {
            if ($user->email_verfied == 0) {
                return "Please verify your email address";
            }
            $this->create_session($user);
            return 'sucessful';
            //return $user;
        }
        return "invaild username or password";
    }

    public function create_session($user)
    {
        global $botwith, $db;
        if ($user->uid > 0) {
            //$db->where('uid', $user->uid);
            //$db->delete('sessions');
        }
        $sessionID = md5(uniqid(microtime()));
        $this->cookie('sid', $sessionID);
    }

    public function cookie($name, $val, $type = 0)
    {
        global $botwith, $db;
        switch (type) {
            case 0:
                //Store the cookie for a day
                $expiry = time() + 60 * 60 * 24;
                break;
            case 1:
                //store the cookie for ever
                $expiry = time() - 60 * 60 * 24;
                break;
            default:
                $expiryDate = 0;
                break;
        }
        $_COOKIE[$botwith->config['cookie_prefix'] . $name] = $val;
        return setcookie($botwith->config['cookie_prefix'] . $name, $val, $expiry, $botwith->config['cookie_path'], $botwith->config['cookie_domain']);
    }

}