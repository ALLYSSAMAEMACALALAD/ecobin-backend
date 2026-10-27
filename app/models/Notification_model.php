<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Notification_model extends Model
{
    protected $table = 'notifications';

    /**
     * Queue a notification. Rows stay 'queued' until an SMS/email provider
     * (Semaphore or Twilio) is connected and a sender picks them up.
     */
    public function queue($user_id, $channel, $message)
    {
        $this->db->table($this->table)->insert([
            'user_id' => $user_id,
            'channel' => $channel,
            'message' => $message,
            'status'  => 'queued',
        ]);
    }

    public function recent($limit = 50)
    {
        return $this->db->table($this->table)
                        ->select('notifications.notif_id, notifications.channel, notifications.message, notifications.status, users.full_name AS recipient')
                        ->join('users', 'users.user_id = notifications.user_id')
                        ->order_by('notifications.notif_id', 'DESC')
                        ->limit($limit)
                        ->get_all();
    }
}
