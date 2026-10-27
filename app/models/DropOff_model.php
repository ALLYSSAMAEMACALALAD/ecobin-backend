<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class DropOff_model extends Model
{
    protected $table = 'drop_off_logs';

    /** Points per kilogram for each accepted material. */
    public static $rates = [
        'PET bottles'   => 10,
        'Plastic'       => 6,
        'Glass'         => 8,
        'Metal / cans'  => 15,
        'Paper'         => 5,
        'Cardboard'     => 5,
    ];

    /**
     * Record a verified drop-off and credit the resident in one transaction.
     * Returns the points awarded, or FALSE if the save failed.
     */
    public function record(array $data, $points)
    {
        $this->db->transaction();
        $ok = $this->db->table($this->table)->insert($data + ['points_awarded' => $points]);
        if ($ok === false) {
            $this->db->roll_back();
            return false;
        }
        $ok = $this->db->table('users')->where('user_id', (int) $data['resident_id'])->increment('points_balance', $points);
        if ($ok === false) {
            $this->db->roll_back();
            return false;
        }
        $this->db->commit();
        return $points;
    }

    public function recent($limit = 100)
    {
        return $this->db->table($this->table)
                        ->select('drop_off_logs.log_id, drop_off_logs.material_type, drop_off_logs.weight_kg, '
                               . 'drop_off_logs.points_awarded, drop_off_logs.qr_value, drop_off_logs.logged_at, '
                               . 'users.full_name AS resident_name, collection_points.name AS point_name')
                        ->join('users', 'users.user_id = drop_off_logs.resident_id')
                        ->join('collection_points', 'collection_points.point_id = drop_off_logs.point_id')
                        ->order_by('drop_off_logs.log_id', 'DESC')
                        ->limit($limit)
                        ->get_all();
    }

    public function all_points_total()
    {
        $rows = $this->db->table($this->table)->select('points_awarded')->get_all();
        return array_sum(array_column($rows, 'points_awarded'));
    }
}
