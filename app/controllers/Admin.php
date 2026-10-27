<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Admin extends Controller
{
    /* =========================================================
     * Dashboard
     * ========================================================= */
    public function dashboard()
    {
        eco_require_role(['admin']);
        $this->call->model('Report_model');
        $this->call->model('Redemption_model');
        $this->call->model('User_model');
        $this->call->model('DropOff_model');
        $this->call->model('Schedule_model');
        $this->call->model('CollectionPoint_model');

        $reports = $this->Report_model->everything();
        $counts  = ['Open' => 0, 'In Progress' => 0, 'Resolved' => 0];
        foreach ($reports as $r) {
            if (isset($counts[$r['status']])) $counts[$r['status']]++;
        }

        $week_end = date('Y-m-d', strtotime('+7 days'));
        $upcoming = array_values(array_filter($this->Schedule_model->upcoming(50),
            function ($s) use ($week_end) { return $s['collection_date'] <= $week_end; }));

        $this->call->view('admin/dashboard', [
            'title'         => 'Admin dashboard',
            'open_reports'  => $counts['Open'],
            'in_progress'   => $counts['In Progress'],
            'resolved'      => $counts['Resolved'],
            'pending_redeem'=> count($this->Redemption_model->pending()),
            'residents'     => count($this->User_model->residents()),
            'points_total'  => $this->DropOff_model->all_points_total(),
            'recent_reports'=> array_slice($reports, 0, 5),
            'upcoming'      => $upcoming,
            'points'        => $this->CollectionPoint_model->all(),
        ]);
    }

    /* =========================================================
     * Collection schedules (CRUD)
     * ========================================================= */
    public function schedules()
    {
        eco_require_role(['admin']);
        $this->call->model('Schedule_model');
        $this->call->view('admin/schedules/index', [
            'title'     => 'Collection schedules',
            'schedules' => $this->Schedule_model->all_with_points(),
        ]);
    }

    public function createSchedule()
    {
        eco_require_role(['admin']);
        $this->render_schedule_form('create', null, [], []);
    }

    public function storeSchedule()
    {
        $user = eco_require_role(['admin']);
        $input = $this->read_schedule_input();
        [$errors, $clean] = $this->validate_schedule($input);
        if ($errors) {
            $this->render_schedule_form('create', null, $errors, $input);
            return;
        }
        $this->call->model('Schedule_model');
        $id = $this->Schedule_model->create_schedule($clean + ['created_by' => $user['user_id']]);
        eco_audit('Created schedule', 'schedules', $id);
        eco_flash('success', 'Schedule added.');
        eco_go('admin/schedules');
    }

    public function showSchedule($id)
    {
        eco_require_role(['admin']);
        $this->call->model('Schedule_model');
        $schedule = $this->Schedule_model->find($id);
        if (!$schedule) show_404();
        $this->call->view('admin/schedules/show', ['title' => 'Schedule details', 'schedule' => $schedule]);
    }

    public function editSchedule($id)
    {
        eco_require_role(['admin']);
        $this->call->model('Schedule_model');
        $schedule = $this->Schedule_model->find($id);
        if (!$schedule) show_404();
        $this->render_schedule_form('edit', $schedule, [], $schedule);
    }

    public function updateSchedule($id)
    {
        eco_require_role(['admin']);
        $this->call->model('Schedule_model');
        $schedule = $this->Schedule_model->find($id);
        if (!$schedule) show_404();
        $input = $this->read_schedule_input();
        [$errors, $clean] = $this->validate_schedule($input);
        if ($errors) {
            $this->render_schedule_form('edit', $schedule, $errors, $input);
            return;
        }
        $this->Schedule_model->update_schedule($schedule['schedule_id'], $clean);
        eco_audit('Updated schedule', 'schedules', $schedule['schedule_id']);
        eco_flash('success', 'Schedule updated.');
        eco_go('admin/schedules/' . (int) $schedule['schedule_id']);
    }

    public function deleteSchedule($id)
    {
        eco_require_role(['admin']);
        $this->call->model('Schedule_model');
        $schedule = $this->Schedule_model->find($id);
        if (!$schedule) {
            eco_flash('error', 'That schedule no longer exists.');
            eco_go('admin/schedules');
        }
        $this->Schedule_model->delete_schedule($schedule['schedule_id']);
        eco_audit('Deleted schedule', 'schedules', $schedule['schedule_id']);
        eco_flash('success', 'Schedule deleted.');
        eco_go('admin/schedules');
    }

    /* =========================================================
     * Collection points (CRUD)
     * ========================================================= */
    public function points()
    {
        eco_require_role(['admin']);
        $this->call->model('CollectionPoint_model');
        $points = $this->CollectionPoint_model->all();
        foreach ($points as &$p) {
            $p['in_use'] = $this->CollectionPoint_model->reference_count($p['point_id']);
        }
        unset($p);
        $this->call->view('admin/points/index', ['title' => 'Collection points', 'points' => $points]);
    }

    public function createPoint()
    {
        eco_require_role(['admin']);
        $this->render_point_form('create', null, [], []);
    }

    public function storePoint()
    {
        eco_require_role(['admin']);
        $input = $this->read_point_input();
        [$errors, $clean] = $this->validate_point($input);
        if ($errors) {
            $this->render_point_form('create', null, $errors, $input);
            return;
        }
        $this->call->model('CollectionPoint_model');
        $id = $this->CollectionPoint_model->create_point($clean);
        eco_audit('Created collection point', 'collection_points', $id);
        eco_flash('success', 'Collection point added.');
        eco_go('admin/points');
    }

    public function editPoint($id)
    {
        eco_require_role(['admin']);
        $this->call->model('CollectionPoint_model');
        $point = $this->CollectionPoint_model->find($id);
        if (!$point) show_404();
        $this->render_point_form('edit', $point, [], $point);
    }

    public function updatePoint($id)
    {
        eco_require_role(['admin']);
        $this->call->model('CollectionPoint_model');
        $point = $this->CollectionPoint_model->find($id);
        if (!$point) show_404();
        $input = $this->read_point_input();
        [$errors, $clean] = $this->validate_point($input);
        if ($errors) {
            $this->render_point_form('edit', $point, $errors, $input);
            return;
        }
        $this->CollectionPoint_model->update_point($point['point_id'], $clean);
        eco_audit('Updated collection point', 'collection_points', $point['point_id']);
        eco_flash('success', 'Collection point updated.');
        eco_go('admin/points');
    }

    public function deletePoint($id)
    {
        eco_require_role(['admin']);
        $this->call->model('CollectionPoint_model');
        $point = $this->CollectionPoint_model->find($id);
        if (!$point) {
            eco_flash('error', 'That collection point no longer exists.');
            eco_go('admin/points');
        }
        if ($this->CollectionPoint_model->reference_count($point['point_id']) > 0) {
            eco_flash('error', 'Cannot delete "' . $point['name'] . '" because it has schedules or drop-off records.');
            eco_go('admin/points');
        }
        $this->CollectionPoint_model->delete_point($point['point_id']);
        eco_audit('Deleted collection point', 'collection_points', $point['point_id']);
        eco_flash('success', 'Collection point deleted.');
        eco_go('admin/points');
    }

    /* =========================================================
     * Drop-offs (QR verification and points credit)
     * ========================================================= */
    public function dropoffs()
    {
        eco_require_role(['admin']);
        $this->render_dropoff_page([], []);
    }

    public function recordDropoff()
    {
        $admin = eco_require_role(['admin']);
        $this->call->model('User_model');
        $this->call->model('DropOff_model');
        $this->call->model('CollectionPoint_model');

        $input  = [
            'eco_id'      => strtoupper(trim($_POST['eco_id'] ?? '')),
            'point_id'    => trim($_POST['point_id'] ?? ''),
            'material'    => trim($_POST['material'] ?? ''),
            'weight'      => trim($_POST['weight'] ?? ''),
        ];
        $errors = [];
        $resident = null;

        if (!preg_match('/^ECOID-(\d{1,6})$/', $input['eco_id'], $m)) {
            $errors[] = 'Enter the Eco-ID as shown on the resident\'s QR (for example, ECOID-0003).';
        } else {
            $resident = $this->User_model->find_by_id((int) $m[1]);
            if (!$resident || $resident['role'] !== 'resident' || $resident['status'] !== 'active') {
                $errors[] = 'No active resident matches that Eco-ID.';
                $resident = null;
            }
        }
        if ($input['point_id'] === '' || !ctype_digit($input['point_id']) || !$this->CollectionPoint_model->find($input['point_id'])) {
            $errors[] = 'Choose a valid collection point.';
        }
        $rates = DropOff_model::$rates;
        if (!isset($rates[$input['material']])) {
            $errors[] = 'Choose a valid material.';
        }
        $weight = $input['weight'];
        if ($weight === '' || !is_numeric($weight) || (float) $weight <= 0 || (float) $weight > 500) {
            $errors[] = 'Weight must be greater than 0 and at most 500 kg.';
        }

        if ($errors) {
            $this->render_dropoff_page($errors, $input);
            return;
        }

        $kg     = round((float) $weight, 2);
        $points = (int) round($kg * $rates[$input['material']]);
        $saved = $this->DropOff_model->record([
            'resident_id' => $resident['user_id'],
            'point_id'    => (int) $input['point_id'],
            'recorded_by' => $admin['user_id'],
            'material_type' => $input['material'],
            'weight_kg'   => $kg,
            'qr_value'    => $input['eco_id'],
        ], $points);

        if ($saved === false) {
            $this->render_dropoff_page(['The drop-off could not be saved. Nothing was credited.'], $input);
            return;
        }
        eco_audit('Recorded drop-off (+' . $points . ' pts)', 'drop_off_logs', null);
        eco_flash('success', 'Credited ' . $points . ' pts to ' . $resident['full_name'] . '.');
        eco_go('admin/dropoffs');
    }

    /* =========================================================
     * Redemptions (verify and approve)
     * ========================================================= */
    public function redemptions()
    {
        eco_require_role(['admin']);
        $this->call->model('Redemption_model');
        $this->call->view('admin/redemptions/index', [
            'title'   => 'Redemptions',
            'pending' => $this->Redemption_model->pending(),
            'history' => $this->Redemption_model->history(100),
        ]);
    }

    public function approveRedemption($id)
    {
        $admin = eco_require_role(['admin']);
        $this->call->model('Redemption_model');
        $result = $this->Redemption_model->approve($id, $admin['user_id']);
        if ($result === TRUE) {
            eco_audit('Approved redemption', 'redemptions', (int) $id);
            eco_flash('success', 'Redemption approved. Points and stock were updated.');
        } else {
            eco_flash('error', $result);
        }
        eco_go('admin/redemptions');
    }

    public function rejectRedemption($id)
    {
        $admin = eco_require_role(['admin']);
        $this->call->model('Redemption_model');
        $this->Redemption_model->reject($id, $admin['user_id']);
        eco_audit('Rejected redemption', 'redemptions', (int) $id);
        eco_flash('info', 'Redemption rejected. No points were taken.');
        eco_go('admin/redemptions');
    }

    /* =========================================================
     * Reports overview (exports live in Report controller)
     * ========================================================= */
    public function reports()
    {
        eco_require_role(['admin']);
        $this->call->model('Report_model');
        $all = $this->Report_model->everything();
        $counts = ['Open' => 0, 'In Progress' => 0, 'Resolved' => 0];
        foreach ($all as $r) {
            if (isset($counts[$r['status']])) $counts[$r['status']]++;
        }
        $total = count($all);
        $this->call->view('admin/reports/index', [
            'title'    => 'Reports',
            'total'    => $total,
            'counts'   => $counts,
            'resolved_pct' => $total ? round($counts['Resolved'] * 100 / $total) : 0,
        ]);
    }

    /* =========================================================
     * Users
     * ========================================================= */
    public function users()
    {
        $admin = eco_require_role(['admin']);
        $this->call->model('User_model');
        $this->call->view('admin/users/index', [
            'title' => 'Users',
            'users' => $this->User_model->all_users(),
            'me'    => $admin['user_id'],
        ]);
    }

    /** Create a collector account. Residents register themselves. */
    public function storeUser()
    {
        eco_require_role(['admin']);
        $this->call->model('User_model');
        $name  = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $errors = [];
        if ($name === '') $errors[] = 'Full name is required.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
        elseif ($this->User_model->find_by_email($email)) $errors[] = 'That email is already registered.';
        if ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) $errors[] = 'Enter a valid phone number.';
        if (strlen($pass) < 8) $errors[] = 'Password must be at least 8 characters.';

        if ($errors) {
            eco_flash('error', implode(' ', $errors));
            eco_go('admin/users');
        }
        $id = $this->User_model->create_user([
            'full_name' => $name, 'email' => $email, 'phone' => $phone !== '' ? $phone : null,
            'password_hash' => password_hash($pass, PASSWORD_DEFAULT), 'role' => 'collector',
            'points_balance' => 0, 'status' => 'active',
        ]);
        eco_audit('Created collector account', 'users', $id);
        eco_flash('success', 'Collector account created for ' . $name . '.');
        eco_go('admin/users');
    }

    public function changeRole($id)
    {
        $admin = eco_require_role(['admin']);
        $this->call->model('User_model');
        $target = $this->User_model->find_by_id($id);
        $role = $_POST['role'] ?? '';
        if (!$target) show_404();
        if ((int) $target['user_id'] === (int) $admin['user_id']) {
            eco_flash('error', 'You cannot change your own role.');
        } elseif (!in_array($role, User_model::$roles, true)) {
            eco_flash('error', 'Choose a valid role.');
        } else {
            $this->User_model->set_role($target['user_id'], $role);
            eco_audit('Changed role to ' . $role, 'users', $target['user_id']);
            eco_flash('success', $target['full_name'] . ' is now ' . $role . '.');
        }
        eco_go('admin/users');
    }

    public function changeStatus($id)
    {
        $admin = eco_require_role(['admin']);
        $this->call->model('User_model');
        $target = $this->User_model->find_by_id($id);
        if (!$target) show_404();
        $status = ($target['status'] === 'active') ? 'inactive' : 'active';
        if ((int) $target['user_id'] === (int) $admin['user_id']) {
            eco_flash('error', 'You cannot deactivate your own account.');
        } else {
            $this->User_model->set_status($target['user_id'], $status);
            eco_audit('Set account ' . $status, 'users', $target['user_id']);
            eco_flash('success', $target['full_name'] . ' is now ' . $status . '.');
        }
        eco_go('admin/users');
    }

    /* =========================================================
     * Audit logs and notification queue
     * ========================================================= */
    public function audit()
    {
        eco_require_role(['admin']);
        $this->call->model('Audit_model');
        $this->call->model('Notification_model');
        $this->call->view('admin/audit/index', [
            'title'         => 'Audit logs',
            'logs'          => $this->Audit_model->recent(200),
            'notifications' => $this->Notification_model->recent(50),
        ]);
    }

    /* =========================================================
     * Helpers
     * ========================================================= */
    private function read_schedule_input()
    {
        return [
            'point_id'        => trim($_POST['point_id'] ?? ''),
            'waste_type'      => trim($_POST['waste_type'] ?? ''),
            'collection_date' => trim($_POST['collection_date'] ?? ''),
            'time_window'     => trim($_POST['time_window'] ?? ''),
        ];
    }

    private function validate_schedule(array $in)
    {
        $this->call->model('Schedule_model');
        $this->call->model('CollectionPoint_model');
        $errors = [];
        if ($in['point_id'] === '' || !ctype_digit($in['point_id']) || !$this->CollectionPoint_model->find($in['point_id'])) {
            $errors[] = 'Choose a valid collection point.';
        }
        if (!in_array($in['waste_type'], Schedule_model::$waste_types, true)) {
            $errors[] = 'Choose a valid waste type.';
        }
        $date = DateTime::createFromFormat('Y-m-d', $in['collection_date']);
        if ($in['collection_date'] === '') {
            $errors[] = 'Collection date is required.';
        } elseif (!$date || $date->format('Y-m-d') !== $in['collection_date']) {
            $errors[] = 'Enter a valid collection date.';
        }
        if ($in['time_window'] === '') {
            $errors[] = 'Time window is required (for example, 7-9 AM).';
        } elseif (mb_strlen($in['time_window']) > 50) {
            $errors[] = 'Time window must be 50 characters or fewer.';
        }
        $clean = [
            'point_id'        => (int) $in['point_id'],
            'waste_type'      => $in['waste_type'],
            'collection_date' => $in['collection_date'],
            'time_window'     => $in['time_window'],
        ];
        return [$errors, $clean];
    }

    private function render_schedule_form($mode, $schedule, array $errors, array $values)
    {
        $this->call->model('Schedule_model');
        $this->call->model('CollectionPoint_model');
        $this->call->view('admin/schedules/form', [
            'title'       => $mode === 'create' ? 'Add schedule' : 'Edit schedule',
            'mode'        => $mode,
            'schedule'    => $schedule,
            'errors'      => $errors,
            'values'      => $values,
            'points'      => $this->CollectionPoint_model->all(),
            'waste_types' => Schedule_model::$waste_types,
        ]);
    }

    private function read_point_input()
    {
        return [
            'name'       => trim($_POST['name'] ?? ''),
            'address'    => trim($_POST['address'] ?? ''),
            'point_type' => trim($_POST['point_type'] ?? ''),
            'latitude'   => trim($_POST['latitude'] ?? ''),
            'longitude'  => trim($_POST['longitude'] ?? ''),
        ];
    }

    private function validate_point(array $in)
    {
        $this->call->model('CollectionPoint_model');
        $errors = [];
        if ($in['name'] === '' || mb_strlen($in['name']) > 120) $errors[] = 'Name is required (120 characters or fewer).';
        if ($in['address'] === '' || mb_strlen($in['address']) > 255) $errors[] = 'Address is required (255 characters or fewer).';
        if (!in_array($in['point_type'], CollectionPoint_model::$types, true)) $errors[] = 'Choose a valid point type.';
        if ($in['latitude'] !== '' && (!is_numeric($in['latitude']) || abs((float) $in['latitude']) > 90)) $errors[] = 'Latitude is invalid.';
        if ($in['longitude'] !== '' && (!is_numeric($in['longitude']) || abs((float) $in['longitude']) > 180)) $errors[] = 'Longitude is invalid.';
        $clean = [
            'name'       => $in['name'],
            'address'    => $in['address'],
            'point_type' => $in['point_type'],
            'latitude'   => $in['latitude'] !== '' ? $in['latitude'] : null,
            'longitude'  => $in['longitude'] !== '' ? $in['longitude'] : null,
        ];
        return [$errors, $clean];
    }

    private function render_point_form($mode, $point, array $errors, array $values)
    {
        $this->call->view('admin/points/form', [
            'title'  => $mode === 'create' ? 'Add collection point' : 'Edit collection point',
            'mode'   => $mode,
            'point'  => $point,
            'errors' => $errors,
            'values' => $values,
        ]);
    }

    private function render_dropoff_page(array $errors, array $values)
    {
        $this->call->model('DropOff_model');
        $this->call->model('CollectionPoint_model');
        $this->call->view('admin/dropoffs/index', [
            'title'  => 'Drop-offs',
            'errors' => $errors,
            'values' => $values,
            'points' => $this->CollectionPoint_model->all(),
            'rates'  => DropOff_model::$rates,
            'logs'   => $this->DropOff_model->recent(100),
        ]);
    }
}
