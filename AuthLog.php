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
$wgExtensionMessagesFiles['AuthLog'] = dirname(__FILE__) . '/' . 'AuthLog.i18n.php';
// Add a new log type
$wgLogTypes[]                      = 'authlog';
$wgLogNames  ['authlog']           = 'authlogpage';
$wgLogHeaders['authlog']           = 'authlogpagetext';
$wgLogActions['authlog/success']   = 'authlog-success';
$wgLogActions['authlog/error']     = 'authlog-error';
$wgLogActions['authlog/logout']    = 'userlogin-logout';

// Add hooks to the login/logout events
$wgHooks['UserLoginForm'][]      = 'wfUserLoginLogError';
$wgHooks['UserLoginComplete'][]  = 'wfUserLoginLogSuccess';
$wgHooks['UserLogout'][]         = 'wfUserLoginLogout';
$wgHooks['UserLogoutComplete'][] = 'wfUserLoginLogoutComplete';

function wfUserLoginLogSuccess(&$user)
{
    $log = new LogPage('userlogin', false);
    $log->addEntry('success', $user->getUserPage(), wfGetIP());
    return true;
}
function wfUserLoginLogError(&$tmpl)
{
    global $wgUser, $wgServerUser;
    if ($tmpl->data['message'] && $tmpl->data['messagetype'] == 'error') {
        $log = new LogPage('userlogin', false);
        $tmp = $wgUser->mId;
        if ($tmp == 0) {
            $wgUser->mId = $wgServerUser;
        }
        $log->addEntry('error', $wgUser->getUserPage(), $tmpl->data['message'], array( wfGetIP()));
        $wgUser->mId = $tmp;
    }
    return true;
}
/**
 * Create a copy of the current user for logging after logout
 */
function wfUserLoginLogout($user)
{
    global $wgUserBeforeLogout;
    $wgUserBeforeLogout = User::newFromId($user->getID());
    return true;
}
function wfUserLoginLogoutComplete($user)
{
    global $wgUser, $wgUserBeforeLogout;
    $tmp = $wgUser->mId;
    $wgUser->mId = $wgUserBeforeLogout->getId();
    $log = new LogPage('userlogin', false);
    $log->addEntry('logout', $wgUserBeforeLogout->getUserPage(), $user->getName());
    $wgUser->mId = $tmp;
    return true;
}
