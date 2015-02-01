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

    public function loggedin()
    {
        global $botwith;
        return $botwith->cache['user']->guest ? FALSE : true;
    }

    public function check_cookies()
    {
        global $db, $botwith;
        $cookie = $this->valid_cookie('user');
        if (isset($cookie) && preg_match('%^(\d+)\|([0-9a-fA-F]+)\|(\d+)\|([0-9a-fA-F]+)$%', $cookie, $matches)) {
            $cookie = array(
                'user_id' => intval($matches[1]),
                'password_hash' => $matches[2],
                'expiration_time' => intval($matches[3]),
                'cookie_hash' => $matches[4],
            );
        }
        $now = time();
        //Ignore user_id because 1 is a guest, Darn it guests :(, Then we check there expiry date.
        if (isset($cookie) && $cookie['user_id'] > 1 && $cookie['expiration_time'] > $now) {
            //What would be mean if they fuck with our cookies
            if ($this->forum_hmac($cookie['user_id'] . '|' . $cookie['expiration_time'], '_cookie_hash') != $cookie['cookie_hash']) {
                //Now we've got to reset the user to a default user and reset it's cookies
                $this->reset_cookie('user');
                return;
            }
            //Let's check if there cookie password equals there forum password.
            $db->where('uid', $cookie['user_id']);
            $user = $db->getOne('users');
            if (isset($user)) {
                //Set the user cache here, So we can call it at a later date without executing a new queriey
                $botwith->cache['user'] = $user;
                if ($this->forum_hmac($botwith->cache['user']->password, '_passwordhash') !== $cookie['password_hash']) {
                    $this->reset_cookie('user');
                    //Got to reset the cookie at a later date I guess..
                    return;
                }
                //From here we shall generate the template to view user to display..
                $data = array('uid' => $botwith->cache['user']->uid, 'name' => $botwith->cache['user']->username,
                    'ip' => $_SERVER['REMOTE_ADDR'], 'time' => time(), 'display' => 'test');
                $db->replace('online', $data);
                $botwith->cache['user']->guest = false;
            } else {
                $this->reset_cookie('user');
                return;
            }
        } else {
            //Stops the php warning
            $botwith->cache['user'] = new stdClass();
            $botwith->cache['user']->guest = true;
            $data = array('uid' => '-1', 'name' => 'guest', 'ip' => $_SERVER['REMOTE_ADDR'],
                'time' => time(), 'display' => 'test');
            $db->replace('online', $data);
            //I guess there cookie expired or there a guest?
        }
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

    public function forum_hmac($data, $key, $raw_output = false)
    {
        if (function_exists('hash_hmac'))
            return hash_hmac('sha1', $data, $key, $raw_output);
        if (strlen($key) > 64)
            $key = pack('H*', sha1($key));
        $key = str_pad($key, 64, chr(0x00));
        $hmac_opad = str_repeat(chr(0x5C), 64);
        $hmac_ipad = str_repeat(chr(0x36), 64);
        for ($i = 0; $i < 64; $i++) {
            $hmac_opad[$i] = $hmac_opad[$i] ^ $key[$i];
            $hmac_ipad[$i] = $hmac_ipad[$i] ^ $key[$i];
        }
        $hash = sha1($hmac_opad . pack('H*', sha1($hmac_ipad . $data)));
        if ($raw_output)
            $hash = pack('H*', $hash);
        return $hash;
    }

    public function reset_cookie($name)
    {
        global $botwith, $db;
        $_COOKIE[$botwith->config['cookie_prefix'] . $name] = '';
        $expiry = time() - 3600;
        return setcookie($botwith->config['cookie_prefix'] . $name, '', $expiry, $botwith->config['cookie_path'], $botwith->config['cookie_domain']);
    }

    public function auth_login($username, $password)
    {
        global $botwith, $db;
        $db->where('username', $username);
        $user = $db->getOne('users');
        if (!empty($user)) {
            if ($user->email_verfied == 0) {
                $botwith->cache['danger-alert'] = 'Verfiy email address';
                return;
            }
            if ($user->password != sha1($password)) {
                $botwith->cache['danger-alert'] = 'Invaild password';
                return;
            }
            $this->create_session($user, 0);
            $this->redirect();
            return true;
        }
        return "invaild username or password";
    }

    public function create_session($user, $type)
    {
        global $botwith, $db;
        if ($user->uid > 0) {
            //$db->where('uid', $user->uid);
            //$db->delete('sessions');
        }
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
        $this->cookie('user', $user->uid . '|' . $this->forum_hmac($user->password, '_passwordhash') . '|' . $expiry . '|' . $this->forum_hmac($user->uid . '|' . $expiry, '_cookie_hash'), $expiry);
    }

    public function cookie($name, $val, $expiry)
    {
        global $botwith, $db;

        $_COOKIE[$botwith->config['cookie_prefix'] . $name] = $val;
        return setcookie($botwith->config['cookie_prefix'] . $name, $val, $expiry, $botwith->config['cookie_path'], $botwith->config['cookie_domain']);
    }

    public function redirect($url = 'index.php')
    {
        header('Location: ' . $url);
    }
}