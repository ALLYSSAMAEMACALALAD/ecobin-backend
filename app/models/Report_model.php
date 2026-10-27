<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Report_model extends Model
{
    protected $table = 'dumping_reports';

    public static $statuses = ['Open', 'In Progress', 'Resolved'];

    private function joined()
    {
        return $this->db->table($this->table)
                        ->select('dumping_reports.report_id, dumping_reports.resident_id, dumping_reports.description, '
                               . 'dumping_reports.location_text, dumping_reports.latitude, dumping_reports.longitude, '
                               . 'dumping_reports.photo_url, dumping_reports.status, dumping_reports.assigned_to, '
                               . 'dumping_reports.created_at, users.full_name AS resident_name, '
                               . 'users.email AS resident_email, assignee.full_name AS assignee_name')
                        ->join('users', 'users.user_id = dumping_reports.resident_id')
                        ->left_join('users AS assignee', 'assignee.user_id = dumping_reports.assigned_to');
    }

    /** All reports for the admin ticket queue, optionally filtered by status. */
    public function all_filtered($status = null, $limit = 300)
    {
        $q = $this->joined();
        if ($status !== null && $status !== '') {
            $q = $q->where('dumping_reports.status', $status);
        }
        return $q->order_by('dumping_reports.report_id', 'DESC')->limit($limit)->get_all();
    }

    /** Every report (no limit), for statistics and exports. */
    public function everything()
    {
        return $this->joined()->order_by('dumping_reports.report_id', 'DESC')->get_all();
    }

    public function find($id)
    {
        return $this->joined()->where('dumping_reports.report_id', (int) $id)->row_array();
    }

    /** One resident's own reports, newest first. */
    public function mine($resident_id)
    {
        return $this->db->table($this->table)
                        ->where('resident_id', (int) $resident_id)
                        ->order_by('created_at', 'DESC')
                        ->get_all();
    }

    public function create_report(array $data)
    {
        $this->db->table($this->table)->insert($data);
        return $this->db->last_id();
    }

    public function update_status($id, $status, $assigned_to)
    {
        return $this->db->table($this->table)->where('report_id', (int) $id)
                        ->update(['status' => $status, 'assigned_to' => (int) $assigned_to]);
    }
}
