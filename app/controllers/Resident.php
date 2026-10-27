<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Resident extends Controller
{
    public function dashboard()
    {
        $user = eco_require_role(['resident']);
        $this->call->model('User_model');
        $this->call->model('Schedule_model');

        $this->call->view('resident/dashboard', [
            'title'    => 'Dashboard',
            'me'       => $this->User_model->find_by_id($user['user_id']),
            'upcoming' => $this->Schedule_model->upcoming(5),
        ]);
    }

    public function schedule()
    {
        eco_require_role(['resident']);
        $this->call->model('Schedule_model');

        $this->call->view('resident/schedule', [
            'title'     => 'Collection schedule',
            'schedules' => $this->Schedule_model->upcoming(50),
        ]);
    }

    /** The resident's QR eco-ID. The QR encodes ECOID-#### (same format as the drop-off logs). */
    public function ecoId()
    {
        $user = eco_require_role(['resident']);
        $this->call->model('User_model');
        $me = $this->User_model->find_by_id($user['user_id']);

        $this->call->view('resident/eco_id', [
            'title'  => 'My Eco-ID',
            'me'     => $me,
            'eco_id' => sprintf('ECOID-%04d', (int) $user['user_id']),
        ]);
    }

    /** Rewards catalog and this resident's redemption requests. */
    public function rewards()
    {
        $user = eco_require_role(['resident']);
        $this->call->model('User_model');
        $this->call->model('Reward_model');
        $this->call->model('Redemption_model');

        $this->call->view('resident/rewards', [
            'title'   => 'Rewards',
            'me'      => $this->User_model->find_by_id($user['user_id']),
            'rewards' => $this->Reward_model->active(),
            'mine'    => $this->Redemption_model->mine($user['user_id']),
        ]);
    }

    /** Request a reward. Points are only taken when an admin approves it. */
    public function redeem($id)
    {
        $user = eco_require_role(['resident']);
        $this->call->model('User_model');
        $this->call->model('Reward_model');
        $this->call->model('Redemption_model');

        $reward = $this->Reward_model->find($id);
        $me     = $this->User_model->find_by_id($user['user_id']);
        if (!$reward || (int) $reward['is_active'] !== 1) {
            eco_flash('error', 'That reward is not available.');
        } elseif ((int) $reward['stock'] < 1) {
            eco_flash('error', 'Sorry, ' . $reward['name'] . ' is out of stock.');
        } elseif ((int) $me['points_balance'] < (int) $reward['points_cost']) {
            eco_flash('error', 'You need ' . (int) $reward['points_cost'] . ' pts for ' . $reward['name'] . '.');
        } elseif (array_filter($this->Redemption_model->mine($user['user_id']),
                  function ($r) use ($reward) { return $r['status'] === 'pending' && (int) $r['reward_id'] === (int) $reward['reward_id']; })) {
            eco_flash('info', 'You already have a pending request for ' . $reward['name'] . '.');
        } else {
            $rid = $this->Redemption_model->create_request([
                'resident_id' => $user['user_id'],
                'reward_id'   => $reward['reward_id'],
                'points_used' => (int) $reward['points_cost'],
                'status'      => 'pending',
            ]);
            eco_audit('Requested redemption: ' . $reward['name'], 'redemptions', $rid);
            eco_flash('success', 'Request sent for ' . $reward['name'] . '. Show this at the hub to collect it once approved.');
        }
        eco_go('resident/rewards');
    }
}
