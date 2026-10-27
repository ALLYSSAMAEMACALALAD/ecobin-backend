<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * JSON API used by the React frontend (frontend/).
 *
 * It uses the same PHP session and the same models and rules as the
 * classic pages. The Vite dev server forwards /api to this backend, so
 * the browser keeps one session cookie.
 *
 * Every POST must send the X-Requested-With header. The React app always
 * does. A cross-site page cannot add that header without a CORS preflight,
 * which this API never allows, so it cannot submit forms on the user's behalf.
 */
class Api extends Controller
{
    const MAX_PHOTO_BYTES = 5242880; // 5 MB
    const ALLOWED_PHOTO_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    /* ----------------------------- session ----------------------------- */

    /** Current user as a fresh database row, or NULL (and the session is ended) if gone or inactive. */
    private function current_user()
    {
        $session = eco_user();
        if (!$session) {
            return null;
        }
        $this->call->model('User_model');
        $fresh = $this->User_model->find_by_id($session['user_id']);
        if (!$fresh || $fresh['status'] !== 'active') {
            eco_session()->sess_destroy();
            return null;
        }
        return $fresh;
    }

    /** Stop with 401/403 unless a logged-in user has one of the roles. Returns the fresh user row. */
    private function require_user(array $roles = ['resident'])
    {
        $user = $this->current_user();
        if (!$user) {
            $this->send(['error' => 'Please log in to continue.'], 401);
        }
        if (!in_array($user['role'], $roles, true)) {
            $this->send(['error' => 'You do not have access to that.'], 403);
        }
        return $user;
    }

    /** Reject POST requests that do not carry the X-Requested-With header. */
    private function guard_post()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->send(['error' => 'Missing X-Requested-With header.'], 400);
        }
    }

    /* ----------------------------- helpers ----------------------------- */

    /** Send a JSON response and stop. */
    private function send($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Decode a JSON request body into an array (empty array if none). */
    private function json_body()
    {
        $data = json_decode(file_get_contents('php://input') ?: '', true);
        return is_array($data) ? $data : [];
    }

    /** Only the fields the frontend may see. Never send password_hash. */
    private function public_user(array $row)
    {
        return [
            'user_id'        => (int) $row['user_id'],
            'full_name'      => $row['full_name'],
            'role'           => $row['role'],
            'points_balance' => (int) $row['points_balance'],
        ];
    }

    /* ------------------------------ auth ------------------------------- */

    /** GET api/me: who is logged in (user is null when nobody is). */
    public function me()
    {
        $user = $this->current_user();
        $this->send(['user' => $user ? $this->public_user($user) : null]);
    }

    /** POST api/login  body: {email, password} */
    public function login()
    {
        $this->guard_post();
        $in       = $this->json_body();
        $email    = trim((string) ($in['email'] ?? ''));
        $password = (string) ($in['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->send(['errors' => ['Email and password are required.']], 422);
        }

        $this->call->model('User_model');
        $user = $this->User_model->find_by_email($email);
        if (!$user || !password_verify($password, $user['password_hash']) || $user['status'] !== 'active') {
            $this->send(['errors' => ['Invalid email or password.']], 401);
        }

        eco_session()->set_userdata('eco_user', [
            'user_id'   => (int) $user['user_id'],
            'full_name' => $user['full_name'],
            'role'      => $user['role'],
        ]);
        $this->send(['user' => $this->public_user($user)]);
    }

    /** POST api/register  body: {full_name, email, phone, password, password_confirm} (residents only) */
    public function register()
    {
        $this->guard_post();
        $in      = $this->json_body();
        $name    = trim((string) ($in['full_name'] ?? ''));
        $email   = trim((string) ($in['email'] ?? ''));
        $phone   = trim((string) ($in['phone'] ?? ''));
        $pass    = (string) ($in['password'] ?? '');
        $confirm = (string) ($in['password_confirm'] ?? '');
        $errors  = [];

        if ($name === '') {
            $errors[] = 'Full name is required.';
        } elseif (mb_strlen($name) > 120) {
            $errors[] = 'Full name must be 120 characters or fewer.';
        }
        if ($email === '') {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }
        if ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) {
            $errors[] = 'Enter a valid phone number.';
        }
        if (strlen($pass) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($pass !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        $this->call->model('User_model');
        if (!$errors && $this->User_model->find_by_email($email)) {
            $errors[] = 'That email is already registered.';
        }
        if ($errors) {
            $this->send(['errors' => $errors], 422);
        }

        $user_id = $this->User_model->create_user([
            'full_name'      => $name,
            'email'          => $email,
            'phone'          => $phone !== '' ? $phone : null,
            'password_hash'  => password_hash($pass, PASSWORD_DEFAULT),
            'role'           => 'resident',
            'points_balance' => 0,
            'status'         => 'active',
        ]);

        eco_session()->set_userdata('eco_user', [
            'user_id'   => (int) $user_id,
            'full_name' => $name,
            'role'      => 'resident',
        ]);
        $this->send(['user' => ['user_id' => (int) $user_id, 'full_name' => $name, 'role' => 'resident', 'points_balance' => 0]], 201);
    }

    /** POST api/logout */
    public function logout()
    {
        $this->guard_post();
        eco_session()->sess_destroy();
        $this->send(['ok' => true]);
    }

    /* ---------------------------- resident ----------------------------- */

    /** GET api/dashboard: the resident's points and the next 5 collections. */
    public function dashboard()
    {
        $user = $this->require_user(['resident']);
        $this->call->model('Schedule_model');
        $this->send([
            'user'     => $this->public_user($user),
            'upcoming' => $this->Schedule_model->upcoming(5),
        ]);
    }

    /** GET api/schedules: upcoming collections (up to 50). */
    public function schedules()
    {
        $this->require_user(['resident']);
        $this->call->model('Schedule_model');
        $this->send(['schedules' => $this->Schedule_model->upcoming(50)]);
    }

    /** GET api/rewards: catalog, points balance, and this resident's requests. */
    public function rewards()
    {
        $user = $this->require_user(['resident']);
        $this->call->model('Reward_model');
        $this->call->model('Redemption_model');
        $this->send([
            'points_balance' => (int) $user['points_balance'],
            'rewards'        => $this->Reward_model->active(),
            'redemptions'    => $this->Redemption_model->mine($user['user_id']),
        ]);
    }

    /** POST api/rewards/{id}/redeem: request a reward. Points are taken only when an admin approves. */
    public function redeem($id)
    {
        $this->guard_post();
        $user = $this->require_user(['resident']);
        $this->call->model('Reward_model');
        $this->call->model('Redemption_model');

        $reward = $this->Reward_model->find($id);
        if (!$reward || (int) $reward['is_active'] !== 1) {
            $this->send(['error' => 'That reward is not available.'], 404);
        }
        if ((int) $reward['stock'] < 1) {
            $this->send(['error' => 'Sorry, ' . $reward['name'] . ' is out of stock.'], 422);
        }
        if ((int) $user['points_balance'] < (int) $reward['points_cost']) {
            $this->send(['error' => 'You need ' . (int) $reward['points_cost'] . ' pts for ' . $reward['name'] . '.'], 422);
        }
        $pending = array_filter($this->Redemption_model->mine($user['user_id']), function ($r) use ($reward) {
            return $r['status'] === 'pending' && (int) $r['reward_id'] === (int) $reward['reward_id'];
        });
        if ($pending) {
            $this->send(['error' => 'You already have a pending request for ' . $reward['name'] . '.'], 409);
        }

        $rid = $this->Redemption_model->create_request([
            'resident_id' => $user['user_id'],
            'reward_id'   => $reward['reward_id'],
            'points_used' => (int) $reward['points_cost'],
            'status'      => 'pending',
        ]);
        eco_audit('Requested redemption: ' . $reward['name'], 'redemptions', $rid);
        $this->send(['message' => 'Request sent for ' . $reward['name'] . '. Show your Eco-ID at the hub to collect it once approved.'], 201);
    }

    /** GET api/eco-id: the QR value (ECOID-####) and the resident's details. */
    public function ecoId()
    {
        $user = $this->require_user(['resident']);
        $this->send([
            'eco_id'         => sprintf('ECOID-%04d', (int) $user['user_id']),
            'full_name'      => $user['full_name'],
            'points_balance' => (int) $user['points_balance'],
        ]);
    }

    /** GET api/reports: this resident's dumping reports, newest first. */
    public function reports()
    {
        $user = $this->require_user(['resident']);
        $this->call->model('Report_model');
        $this->send(['reports' => $this->Report_model->mine($user['user_id'])]);
    }

    /** POST api/reports: multipart form with description, location, latitude, longitude, photo. */
    public function createReport()
    {
        $this->guard_post();
        $user   = $this->require_user(['resident']);
        $desc   = trim($_POST['description'] ?? '');
        $loc    = trim($_POST['location'] ?? '');
        $lat    = trim($_POST['latitude'] ?? '');
        $lng    = trim($_POST['longitude'] ?? '');
        $errors = [];

        if ($desc === '') {
            $errors[] = 'Description is required.';
        } elseif (mb_strlen($desc) > 1000) {
            $errors[] = 'Description must be 1000 characters or fewer.';
        }
        if ($loc === '') {
            $errors[] = 'Location is required (for example, the street or a landmark).';
        } elseif (mb_strlen($loc) > 255) {
            $errors[] = 'Location must be 255 characters or fewer.';
        }
        if ($lat !== '' && (!is_numeric($lat) || abs((float) $lat) > 90)) {
            $errors[] = 'Latitude is invalid.';
        }
        if ($lng !== '' && (!is_numeric($lng) || abs((float) $lng) > 180)) {
            $errors[] = 'Longitude is invalid.';
        }

        $photo_url = null;
        $file = $_FILES['photo'] ?? null;
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'A photo is required as evidence.';
        } else {
            $photo_url = $this->save_photo($file, $errors);
        }

        if ($errors) {
            $this->send(['errors' => $errors], 422);
        }

        $this->call->model('Report_model');
        $id = $this->Report_model->create_report([
            'resident_id'   => $user['user_id'],
            'description'   => $desc,
            'location_text' => $loc,
            'latitude'      => $lat !== '' ? $lat : null,
            'longitude'     => $lng !== '' ? $lng : null,
            'photo_url'     => $photo_url,
            'status'        => 'Open',
        ]);
        $this->send(['message' => 'Report submitted. Thank you for helping keep the community clean.', 'report_id' => (int) $id], 201);
    }

    /** Validate and store an uploaded photo. Returns the public path, or NULL and adds to $errors. */
    private function save_photo(array $file, array &$errors)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'The photo could not be uploaded. Try a smaller image.';
            return null;
        }
        if ($file['size'] > self::MAX_PHOTO_BYTES) {
            $errors[] = 'The photo must be 5 MB or smaller.';
            return null;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset(self::ALLOWED_PHOTO_TYPES[$mime]) || !is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'The photo must be a JPG, PNG, or WEBP image.';
            return null;
        }

        $dir = ROOT_DIR . 'public/uploads/reports/';
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            $errors[] = 'The upload folder could not be created. Check folder permissions.';
            return null;
        }

        $name = bin2hex(random_bytes(16)) . '.' . self::ALLOWED_PHOTO_TYPES[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
            $errors[] = 'The photo could not be saved. Check folder permissions.';
            return null;
        }
        return 'uploads/reports/' . $name;
    }
}
