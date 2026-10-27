<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Auth extends Controller
{
    public function login()
    {
        if (eco_user()) {
            eco_go(eco_home_for(eco_user()['role']));
        }
        $this->call->view('auth/login', ['title' => 'Log in', 'errors' => [], 'old' => []]);
    }

    public function authenticate()
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors   = [];

        if ($email === '')    $errors[] = 'Email is required.';
        if ($password === '') $errors[] = 'Password is required.';

        if (!$errors) {
            $this->call->model('User_model');
            $user = $this->User_model->find_by_email($email);
            if (!$user || !password_verify($password, $user['password_hash']) || $user['status'] !== 'active') {
                $errors[] = 'Invalid email or password.';
            }
        }

        if ($errors) {
            $this->call->view('auth/login', ['title' => 'Log in', 'errors' => $errors, 'old' => ['email' => $email]]);
            return;
        }

        eco_session()->set_userdata('eco_user', [
            'user_id'   => (int) $user['user_id'],
            'full_name' => $user['full_name'],
            'role'      => $user['role'],
        ]);
        eco_flash('success', 'Welcome back, ' . $user['full_name'] . '!');
        eco_go(eco_home_for($user['role']));
    }

    public function register()
    {
        if (eco_user()) {
            eco_go(eco_home_for(eco_user()['role']));
        }
        $this->call->view('auth/register', ['title' => 'Register', 'errors' => [], 'old' => []]);
    }

    /** Create a resident account (only residents self-register). */
    public function store()
    {
        $name     = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';
        $errors   = [];

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
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        $this->call->model('User_model');
        if (!$errors && $this->User_model->find_by_email($email)) {
            $errors[] = 'That email is already registered.';
        }

        if ($errors) {
            $old = ['full_name' => $name, 'email' => $email, 'phone' => $phone];
            $this->call->view('auth/register', ['title' => 'Register', 'errors' => $errors, 'old' => $old]);
            return;
        }

        $user_id = $this->User_model->create_user([
            'full_name'     => $name,
            'email'         => $email,
            'phone'         => $phone !== '' ? $phone : null,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role'          => 'resident',
            'points_balance'=> 0,
            'status'        => 'active',
        ]);

        eco_session()->set_userdata('eco_user', [
            'user_id'   => (int) $user_id,
            'full_name' => $name,
            'role'      => 'resident',
        ]);
        eco_flash('success', 'Account created. Welcome to Eco-Bin!');
        eco_go('resident/dashboard');
    }

    public function logout()
    {
        eco_session()->sess_destroy();
        eco_session()->set_flashdata('success', 'You have been logged out.');
        eco_go('');
    }

    public function profile()
    {
        eco_require_role(['resident', 'collector', 'admin']);
        $this->call->view('shared/stub', [
            'title' => 'Profile',
            'note'  => 'Profile editing is part of Checking 2 / Checking 3 scope.',
        ]);
    }
}
