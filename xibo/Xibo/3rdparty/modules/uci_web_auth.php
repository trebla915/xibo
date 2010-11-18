<?php
/*
 *
 */
 defined('XIBO') or die("Sorry, you are not allowed to directly access this page.<br /> Please press the back button in your browser.");
 
 class WebAuth
{
    // The URLs to the web authentication at login.uci.edu
	var $login_url    = 'https://login.uci.edu/ucinetid/webauth';
	var $logout_url   = 'https://login.uci.edu/ucinetid/webauth_logout';
	var $check_url    = 'http://login.uci.edu/ucinetid/webauth_check';

    // The cookie - the name of the cookie is 'ucinetid_auth'
	var $cookie;

    // The user's URL - indicates where to goes upon authentication
	var $url;

    // The user's remote address - matched against the auth_host
	var $remote_addr;

    // The various errors that might crop up are stored in this array
    var $errors = array();

    // These are the defined vars from login.uci.edu
    var $time_created = 0;
    var $ucinetid = '';
    var $campus_id = '';
    var $age_in_seconds = 0;
    var $uci_affilitaions = '';
    var $max_idle_time = 0;
    var $auth_fail = '';
    var $seconds_since_checked = 0;
    var $last_checked = 0;
    var $auth_host = '';

    // Constructor for the web authentication
	function WebAuth() {
        
        // First, let's check the PHP version
        $php_version = phpversion();
        if ($php_version < 4) {
            $this->errors[1] = "Warning, designed to work with PHP 4.x";
        }

        // Next, we'll grab some key global variables
        $cookie_vars_array = $GLOBALS[_COOKIE];
        $get_vars_array = $GLOBALS[_GET];
        $server_vars_array = $GLOBALS[_SERVER];

        // Let's get the client's ip address
        $this->remote_addr = $server_vars_array[REMOTE_ADDR];

        // Time to construct the client's URL
        // Check the server port first
        switch ($server_vars_array[SERVER_PORT]) {
            case "443":
                $prefix = "https://";
                break;
            default:
                $prefix = "http://";
                break;
        }

        // Now, we'll add the HTTP_HOST name
        $this->url = $prefix . $server_vars_array[HTTP_HOST];

        // Let's add the script name
        $this->url .= $server_vars_array[SCRIPT_NAME];

        // Reconstruct the GET variables
        if (is_array($get_vars_array) && sizeof($get_vars_array) > 0) {
            $i = 0;
            $get_string = '';
            while (list($k, $v) = each($get_vars_array)) {
                if ($k != 'login' && $k != 'logout') {
                    $get_string .= (($i++ == 0) ? '?' : '&') 
                        . urlencode($k) . '=' . urlencode($v);
                }
            }
            $this->url .= $get_string;
        }
        // Done with URL construction

        // Modify the various login.uci.edu URLs with our return URL
        $this->login_url .= '?return_url=' . urlencode($this->url);
        $this->logout_url .= '?return_url=' . urlencode($this->url);

        // Let's add the cookie called 'ucinetid_auth'
        if ($cookie_vars_array[ucinetid_auth]) {
            $this->cookie = $cookie_vars_array[ucinetid_auth];
            $this->check_url .= '?ucinetid_auth=' . $this->cookie;
        }

        // Now, let's check authentication
        $this->checkAuth();

	} // end Constructor

    // Check the authentication based on cookie
    function checkAuth() {

        // First, we'll check that we even have a cookie
        if (empty($this->cookie) || $this->cookie == 'no_key') {
            return false;
        }
        
        // Check that we can connect to login.uci.edu
        if (!$auth_array = @file($this->check_url)) {
            $this->errors[2] = "Unable to connect to login.uci.edu";
            return false;
        }

        // Make sure we have an array, and build the auth values
        if (is_array($auth_array)) {
            while (list($k,$v) = each($auth_array)) {
                if (!empty($v)) {
                    $v = trim($v);
                    $auth_values = split("=", $v);
                    if (!empty($auth_values[0]) && !empty($auth_values[1])) 
                        $this->$auth_values[0] = $auth_values[1];
                }
            }

            // Check to ensure auth_host is verified
            if ($this->auth_host != $this->remote_addr) {
                $this->errors[3] = "Warning, the auth host doesn't match.";
                return false;
            }
            return true;
        }
    } // end check_auth

    // Boolean, determines if someone's logged in
    function isLoggedIn() {
        if ($this->time_created) return true;
        else return false;
    }

    // The login function
    function login() {
        print Header('Location: ' . $this->login_url);
        exit;
    }

    // The logout function
    function logout() {
        print Header('Location: ' . $this->logout_url);
        exit;
    }
}
?>
