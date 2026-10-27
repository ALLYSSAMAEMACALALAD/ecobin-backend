<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| EcoBin ROUTES
| -------------------------------------------------------------------
| Routes marked "stub" render a placeholder page (collector features,
| for a later checking). Everything else is functional.
*/
/** @var object $router **/

// Public
$router->get('/', 'Home::index');
$router->get('/faq', 'Home::faq');
$router->get('/login', 'Auth::login');
$router->post('/login', 'Auth::authenticate');
$router->get('/register', 'Auth::register');
$router->post('/register', 'Auth::store');
$router->get('/logout', 'Auth::logout');
$router->get('/profile', 'Auth::profile');              // stub
$router->get('/404', 'Home::notFound');

// Resident
$router->get('/resident/dashboard', 'Resident::dashboard');
$router->get('/resident/schedule', 'Resident::schedule');
$router->get('/resident/eco-id', 'Resident::ecoId');
$router->get('/resident/rewards', 'Resident::rewards');
$router->post('/resident/rewards/{id}/redeem', 'Resident::redeem');
$router->get('/resident/reports', 'Report::mine');
$router->get('/resident/reports/new', 'Report::create');
$router->post('/resident/reports/new', 'Report::store');

// Collector (driver) - stubs for a later checking
$router->get('/collector/route', 'Collector::route');
$router->get('/collector/stops/{id}', 'Collector::stop');
$router->post('/collector/stops/{id}/complete', 'Collector::completeStop');

// Admin - dashboard
$router->get('/admin/dashboard', 'Admin::dashboard');

// Admin - collection schedules (CRUD)
$router->get('/admin/schedules', 'Admin::schedules');
$router->get('/admin/schedules/create', 'Admin::createSchedule');
$router->post('/admin/schedules', 'Admin::storeSchedule');
$router->get('/admin/schedules/{id}', 'Admin::showSchedule');
$router->get('/admin/schedules/{id}/edit', 'Admin::editSchedule');
$router->post('/admin/schedules/{id}', 'Admin::updateSchedule');
$router->post('/admin/schedules/{id}/delete', 'Admin::deleteSchedule');

// Admin - collection points (CRUD)
$router->get('/admin/points', 'Admin::points');
$router->get('/admin/points/create', 'Admin::createPoint');
$router->post('/admin/points', 'Admin::storePoint');
$router->get('/admin/points/{id}/edit', 'Admin::editPoint');
$router->post('/admin/points/{id}', 'Admin::updatePoint');
$router->post('/admin/points/{id}/delete', 'Admin::deletePoint');

// Admin - drop-offs and points credit
$router->get('/admin/dropoffs', 'Admin::dropoffs');
$router->post('/admin/dropoffs', 'Admin::recordDropoff');

// Admin - redemptions
$router->get('/admin/redemptions', 'Admin::redemptions');
$router->post('/admin/redemptions/{id}/approve', 'Admin::approveRedemption');
$router->post('/admin/redemptions/{id}/reject', 'Admin::rejectRedemption');

// Admin - dumping tickets
$router->get('/admin/tickets', 'Report::index');
$router->get('/admin/tickets/{id}', 'Report::showTicket');
$router->post('/admin/tickets/{id}/status', 'Report::updateTicket');

// Admin - reports and exports
$router->get('/admin/reports', 'Admin::reports');
$router->get('/admin/reports/export.csv', 'Report::exportCsv');
$router->get('/admin/reports/export.pdf', 'Report::exportPdf');

// Admin - users
$router->get('/admin/users', 'Admin::users');
$router->post('/admin/users', 'Admin::storeUser');
$router->post('/admin/users/{id}/role', 'Admin::changeRole');
$router->post('/admin/users/{id}/status', 'Admin::changeStatus');

// Admin - audit logs
$router->get('/admin/audit', 'Admin::audit');

// ---------------------------------------------------------------
// JSON API for the React frontend (see /frontend). Session-based.
// ---------------------------------------------------------------
$router->get('api/me', 'Api::me');
$router->post('api/register', 'Api::register');
$router->post('api/login', 'Api::login');
$router->post('api/logout', 'Api::logout');
$router->get('api/dashboard', 'Api::dashboard');
$router->get('api/schedules', 'Api::schedules');
$router->get('api/rewards', 'Api::rewards');
$router->post('api/rewards/{id}/redeem', 'Api::redeem');
$router->get('api/eco-id', 'Api::ecoId');
$router->get('api/reports', 'Api::reports');
$router->post('api/reports', 'Api::createReport');
