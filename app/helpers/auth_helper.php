<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| Eco-Bin auth and shared helpers
| -------------------------------------------------------------------
| Loaded automatically (see app/config/autoload.php).
| Session user layout: ['user_id' => int, 'full_name' => string, 'role' => string]
*/

/** Return the Session library instance. */
function eco_session()
{
    return lava_instance()->session;
}

/** Return the logged-in user array, or NULL when nobody is logged in. */
function eco_user()
{
    return eco_session()->userdata('eco_user');
}

/** Store a one-time message for the next page (success, error, info). */
function eco_flash($key, $message)
{
    eco_session()->set_flashdata($key, $message);
}

/** Read a one-time message (returns NULL when there is none). */
function eco_flash_get($key)
{
    return eco_session()->flashdata($key);
}

/** Escape output for HTML. Always use this when printing user data. */
function eco_e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Build a full URL for a path such as 'admin/schedules'. */
function eco_url($path = '')
{
    return base_url(ltrim($path, '/'));
}

/** Send the browser to a path and stop the script. */
function eco_go($path)
{
    header('Location: ' . eco_url($path), true, 302);
    exit;
}

/** The page each role lands on after login. */
function eco_home_for($role)
{
    switch ($role) {
        case 'resident':  return 'resident/dashboard';
        case 'collector': return 'collector/route';
        case 'admin':     return 'admin/dashboard';
        default:          return '';
    }
}

/**
 * Block the page unless the user is logged in and has one of the roles.
 * Returns the user array when access is allowed.
 */
function eco_require_role(array $roles)
{
    $user = eco_user();
    if ($user) {
        // The session can outlive the account (for example, after the database is re-imported).
        // Check the account on every protected request and end the session if it is gone or inactive.
        $LAVA = lava_instance();
        $LAVA->call->model('User_model');
        $fresh = $LAVA->User_model->find_by_id($user['user_id']);
        if (!$fresh || $fresh['status'] !== 'active') {
            eco_session()->sess_destroy();
            eco_session()->set_flashdata('error', 'Your session has ended. Please log in again.');
            eco_go('login');
        }
        if ($fresh['role'] !== $user['role'] || $fresh['full_name'] !== $user['full_name']) {
            $user = ['user_id' => (int) $fresh['user_id'], 'full_name' => $fresh['full_name'], 'role' => $fresh['role']];
            eco_session()->set_userdata('eco_user', $user);
        }
    }
    if (!$user) {
        eco_flash('error', 'Please log in to continue.');
        eco_go('login');
    }
    if (!in_array($user['role'], $roles, true)) {
        eco_flash('error', 'You do not have access to that page.');
        eco_go(eco_home_for($user['role']));
    }
    return $user;
}

/** Navigation menu for a role, as [label => path] pairs (matches the site map). */
function eco_nav($role)
{
    $menus = [
        'resident' => [
            'Dashboard'        => 'resident/dashboard',
            'Schedule'         => 'resident/schedule',
            'My Eco-ID'        => 'resident/eco-id',
            'Rewards'          => 'resident/rewards',
            'Report Dumping'   => 'resident/reports/new',
            'My Reports'       => 'resident/reports',
        ],
        'collector' => [
            "Today's Route"    => 'collector/route',
        ],
        'admin' => [
            'Dashboard'        => 'admin/dashboard',
            'Schedules'        => 'admin/schedules',
            'Collection Points'=> 'admin/points',
            'Drop-offs'        => 'admin/dropoffs',
            'Redemptions'      => 'admin/redemptions',
            'Dumping Tickets'  => 'admin/tickets',
            'Reports'          => 'admin/reports',
            'Users'            => 'admin/users',
            'Audit Logs'       => 'admin/audit',
        ],
    ];
    return $menus[$role] ?? [];
}

/** Record an action in audit_logs, attributed to the logged-in user. */
function eco_audit($action, $table = null, $record_id = null)
{
    $user = eco_user();
    $LAVA = lava_instance();
    $LAVA->call->model('Audit_model');
    $LAVA->Audit_model->log($user ? $user['user_id'] : null, $action, $table, $record_id);
}
