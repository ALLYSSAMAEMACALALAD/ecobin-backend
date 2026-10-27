<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Schedule_model extends Model
{
    protected $table = 'schedules';

    /** Waste types accepted by the schedule form (validated server-side). */
    public static $waste_types = ['Biodegradable', 'Recyclables', 'Residual'];

    private function base_query()
    {
        return $this->db->table($this->table)
                        ->select('schedules.schedule_id, schedules.point_id, schedules.waste_type, '
                               . 'schedules.collection_date, schedules.time_window, '
                               . 'collection_points.name AS point_name, collection_points.address AS point_address')
                        ->join('collection_points', 'collection_points.point_id = schedules.point_id');
    }

    /** Every schedule, soonest first. */
    public function all_with_points()
    {
        return $this->base_query()
                    ->order_by('schedules.collection_date', 'ASC')
                    ->get_all();
    }

    /** Schedules from today onward (used by the resident pages). */
    public function upcoming($limit = 10)
    {
        return $this->base_query()
                    ->where('schedules.collection_date', '>=', date('Y-m-d'))
                    ->order_by('schedules.collection_date', 'ASC')
                    ->limit($limit)
                    ->get_all();
    }

    public function find($id)
    {
        return $this->base_query()
                    ->where('schedules.schedule_id', (int) $id)
                    ->row_array();
    }

    /** Insert a schedule and return the new schedule_id. */
    public function create_schedule(array $data)
    {
        $this->db->table($this->table)->insert($data);
        return $this->db->last_id();
    }

    public function update_schedule($id, array $data)
    {
        return $this->db->table($this->table)
                        ->where('schedule_id', (int) $id)
                        ->update($data);
    }

    public function delete_schedule($id)
    {
        return $this->db->table($this->table)
                        ->where('schedule_id', (int) $id)
                        ->delete();
    }
}
