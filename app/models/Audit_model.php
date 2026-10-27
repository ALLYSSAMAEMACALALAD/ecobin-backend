<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Audit_model extends Model
{
    protected $table = 'audit_logs';

    public function log($user_id, $action, $table_name = null, $record_id = null)
    {
        $this->db->table($this->table)->insert([
            'user_id'    => $user_id,
            'action'     => substr($action, 0, 100),
            'table_name' => $table_name,
            'record_id'  => $record_id,
        ]);
    }

    /** Latest audit entries with the actor's name. */
    public function recent($limit = 200)
    {
        return $this->db->table($this->table)
                        ->select('audit_logs.log_id, audit_logs.action, audit_logs.table_name, '
                               . 'audit_logs.record_id, audit_logs.created_at, users.full_name AS actor')
                        ->left_join('users', 'users.user_id = audit_logs.user_id')
                        ->order_by('audit_logs.log_id', 'DESC')
                        ->limit($limit)
                        ->get_all();
    }
}
