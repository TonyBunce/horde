<?php
/**
 * Nag base inclusion file.
 *
 * This file brings in all of the dependencies that every Nag
 * script will need and sets up objects that all scripts use.
 */

// Determine BASE directories.
require_once dirname(__FILE__) . '/base.load.php';

// Load the Horde Framework core, and set up inclusion paths.
require_once HORDE_BASE . '/lib/core.php';

// Registry.
$s_ctrl = 0;
switch (Horde_Util::nonInputVar('nag_session_control')) {
case 'none':
    $s_ctrl = Horde_Registry::SESSION_NONE;
    break;

case 'readonly':
    $s_ctrl = Horde_Registry::SESSION_READONLY;
    break;
}
$registry = Horde_Registry::singleton($s_ctrl);

try {
    $registry->pushApp('nag', array('check_perms' => (Horde_Util::nonInputVar('nag_authentication') != 'none'), 'logintasks' => true));
} catch (Horde_Exception $e) {
    Horde_Auth::authenticationFailureRedirect('nag', $e);
}
$conf = &$GLOBALS['conf'];
@define('NAG_TEMPLATES', $registry->get('templates'));

// Notification system.
$notification = Horde_Notification::singleton();
$notification->attach('status', null, 'Nag_Notification_Listener_Status');

// Start compression.
Horde::compressOutput();

// Set the timezone variable.
Horde_Nls::setTimeZone();

// Create a share instance.
$GLOBALS['nag_shares'] = Horde_Share::singleton($registry->getApp());

Nag::initialize();
