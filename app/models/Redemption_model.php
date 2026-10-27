<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Redemption_model extends Model
{
    protected $table = 'redemptions';

    private function joined()
    {
        return $this->db->table($this->table)
                        ->select('redemptions.redemption_id, redemptions.resident_id, redemptions.reward_id, '
                               . 'redemptions.points_used, redemptions.status, redemptions.requested_at, '
                               . 'users.full_name AS resident_name, rewards.name AS reward_name')
                        ->join('users', 'users.user_id = redemptions.resident_id')
                        ->join('rewards', 'rewards.reward_id = redemptions.reward_id');
    }

    public function pending()
    {
        return $this->joined()->where('redemptions.status', 'pending')
                    ->order_by('redemptions.requested_at', 'ASC')->get_all();
    }

    public function history($limit = 100)
    {
        return $this->joined()->order_by('redemptions.redemption_id', 'DESC')->limit($limit)->get_all();
    }

    public function mine($resident_id)
    {
        return $this->joined()->where('redemptions.resident_id', (int) $resident_id)
                    ->order_by('redemptions.redemption_id', 'DESC')->get_all();
    }

    public function find($id)
    {
        return $this->db->table($this->table)->where('redemption_id', (int) $id)->row_array();
    }

    public function create_request(array $data)
    {
        $this->db->table($this->table)->insert($data);
        return $this->db->last_id();
    }

    /**
     * Approve a pending redemption: check balance and stock, take the points,
     * reduce stock and mark it approved. All-or-nothing.
     * Returns TRUE on success or an error message string.
     */
    public function approve($redemption_id, $admin_id)
    {
        $req = $this->find($redemption_id);
        if (!$req || $req['status'] !== 'pending') {
            return 'This request is no longer pending.';
        }
        $reward = $this->db->table('rewards')->where('reward_id', (int) $req['reward_id'])->row_array();
        $user   = $this->db->table('users')->where('user_id', (int) $req['resident_id'])->row_array();
        if (!$reward || (int) $reward['stock'] < 1) {
            return 'This reward is out of stock.';
        }
        if (!$user || (int) $user['points_balance'] < (int) $req['points_used']) {
            return 'The resident no longer has enough points.';
        }

        $this->db->transaction();
        $ok = $this->db->table('users')->where('user_id', (int) $req['resident_id'])
                       ->decrement('points_balance', (int) $req['points_used']);
        $ok = $ok !== false && $this->db->table('rewards')->where('reward_id', (int) $req['reward_id'])->decrement('stock', 1) !== false;
        $ok = $ok && $this->db->table($this->table)->where('redemption_id', (int) $redemption_id)
                              ->update(['status' => 'approved', 'verified_by' => (int) $admin_id]) !== false;
        if (!$ok) {
            $this->db->roll_back();
            return 'The approval could not be saved. Nothing was changed.';
        }
        $this->db->commit();
        return TRUE;
    }

    public function reject($redemption_id, $admin_id)
    {
        return $this->db->table($this->table)->where('redemption_id', (int) $redemption_id)
                        ->where('status', 'pending')
                        ->update(['status' => 'rejected', 'verified_by' => (int) $admin_id]);
    }
}
