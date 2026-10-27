<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Reward_model extends Model
{
    protected $table = 'rewards';

    public function active()
    {
        return $this->db->table($this->table)->where('is_active', 1)->order_by('points_cost', 'ASC')->get_all();
    }

    public function find($id)
    {
        return $this->db->table($this->table)->where('reward_id', (int) $id)->row_array();
    }

    public function decrement_stock($id)
    {
        return $this->db->table($this->table)->where('reward_id', (int) $id)->decrement('stock', 1);
    }
}
