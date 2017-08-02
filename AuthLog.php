<?php
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

//branch based on what happens with the auth attempt
function logAuth($response, $user, $username)
{
    // grab the MediaWiki global vars
    global $fail2banfile;
    global $fail2banid;

    //set vars to log
    $time = date("Y-m-d H:i:s T");
    $ip = $_SERVER['REMOTE_ADDR'];

    //successful login
    if ($response->status == "PASS") {
        error_log("$time Successful login by $username from $ip on $fail2banid\n", 3, $fail2banfile);
        return true; //continue to next hook
    } else {
        error_log("$time Authentication error by $username from $ip on $fail2banid\n", 3, $fail2banfile);
        return true; //continue to next hook
    }
}
