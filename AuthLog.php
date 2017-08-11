<?php

use MediaWiki\Auth\AuthManager;
use MediaWiki\Auth\AuthenticationResponse;
use MediaWiki\Auth\AbstractPreAuthenticationProvider;

/**
 * AuthLog - MediaWiki extension to add auth events to the log
 *
 * @file
 * @author Roger Creasy
 * @email roger@rogercreasy.com
 * @copyright © 2017 Container Graphics Corporation
 * @licence MIT
 */

if (!defined('MEDIAWIKI')) {
    die('Not an entry point.');
}
define('AUTH_LOG_VERSION', '0.1, 2017-01-08');
$wgServerUser = 1; # User ID to use for logging if no user exists
$wgExtensionCredits['other'][] = array(
    'name'        => 'AuthLog',
    'author'      => 'Roger Creasy',
    'description' => 'Creates a new MediaWiki log for user logins and logout events',
    'url'         => '',
    'version'     => AUTH_VERSION
);

// Add hooks to the login/logout events
$wgHooks['AuthManagerLoginAuthenticateAudit'][] = 'logAuth';

function logAuth($response, $user, $username, $time, $ip)
{
    // grab the MediaWiki global vars
    global $fail2banfile;
    global $fail2banid;

    // set vars for logfile
    $time = date("Y-m-d H:i:s T");
    $ip = $_SERVER['REMOTE_ADDR'];

    if ($response->status === AuthenticationResponse::PASS) {
        error_log("$time Successful login by $username from $ip on $fail2banid\n", 3, $fail2banfile);
        return true; //continue to next hook
    } elseif ($response->status === AuthenticationResponse::FAIL) {
        error_log("$time Authentication error by $username from $ip on $fail2banid\n", 3, $fail2banfile);
        return true; //continue to next hook
    }
}

class LoggingAuthenticationProvider extends AbstractPreAuthenticationProvider
{
    public function postAuthentication($user, AuthenticationResponse $response)
    {
        global $fail2banfile;
        global $fail2banid;
        
        // set vars for logfile
        $time = date("Y-m-d H:i:s T");
        $ip = $_SERVER['REMOTE_ADDR'];

        // if the username is not found in the database, log the attempt
        if ($response->username === null) {
            error_log("$time Authentication error by $user from $ip on $fail2banid\n", 3, $fail2banfile);
            return true;
        } else {
            return true;
        }
    }
}

$wgAuthManagerAutoConfig['preauth'] = [
    'LoggingAuthenticationProvider' => [
        'class' => 'LoggingAuthenticationProvider',
    ],
];
