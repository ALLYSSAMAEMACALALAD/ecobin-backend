<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class User_model extends Model
{
    protected $table = 'users';

    public static $roles = ['resident', 'collector', 'admin'];

    public function find_by_id($id)
    {
        return $this->db->table($this->table)->where('user_id', (int) $id)->row_array();
    }

    public function find_by_email($email)
    {
        return $this->db->table($this->table)->where('email', $email)->row_array();
    }

    /** Insert a user and return the new user_id. */
    public function create_user(array $data)
    {
        $this->db->table($this->table)->insert($data);
        return $this->db->last_id();
    }

    public function all_users()
    {
        return $this->db->table($this->table)
                        ->select('user_id, full_name, email, phone, role, points_balance, status, created_at')
                        ->order_by('role', 'ASC')->order_by('full_name', 'ASC')->get_all();
    }

    public function set_role($id, $role)
    {
        return $this->db->table($this->table)->where('user_id', (int) $id)->update(['role' => $role]);
    }

    public function set_status($id, $status)
    {
        return $this->db->table($this->table)->where('user_id', (int) $id)->update(['status' => $status]);
    }

    public function residents()
    {
        return $this->db->table($this->table)->where('role', 'resident')->get_all();
    }
}
