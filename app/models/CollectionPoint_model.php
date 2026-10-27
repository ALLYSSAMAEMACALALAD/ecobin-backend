<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class CollectionPoint_model extends Model
{
    protected $table = 'collection_points';

    public static $types = ['hub', 'bin'];

    public function all()
    {
        return $this->db->table($this->table)->order_by('name', 'ASC')->get_all();
    }

    public function find($id)
    {
        return $this->db->table($this->table)->where('point_id', (int) $id)->row_array();
    }

    public function create_point(array $data)
    {
        $this->db->table($this->table)->insert($data);
        return $this->db->last_id();
    }

    public function update_point($id, array $data)
    {
        return $this->db->table($this->table)->where('point_id', (int) $id)->update($data);
    }

    public function delete_point($id)
    {
        return $this->db->table($this->table)->where('point_id', (int) $id)->delete();
    }

    /** How many schedules and drop-off logs reference this point (used to block unsafe deletes). */
    public function reference_count($id)
    {
        $schedules = $this->db->table('schedules')->where('point_id', (int) $id)->get_all();
        $drops     = $this->db->table('drop_off_logs')->where('point_id', (int) $id)->get_all();
        return count($schedules) + count($drops);
    }
}
