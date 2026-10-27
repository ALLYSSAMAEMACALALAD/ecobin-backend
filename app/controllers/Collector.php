<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Collector extends Controller
{
    public function route()
    {
        eco_require_role(['collector']);
        $this->call->view('shared/stub', [
            'title' => "Today's route",
            'note'  => 'Route dispatch and the Google Maps view are part of a later checking.',
        ]);
    }

    public function stop($id)
    {
        eco_require_role(['collector']);
        $this->call->view('shared/stub', [
            'title' => 'Stop detail',
            'note'  => 'Stop details (stop #' . (int) $id . ') are part of a later checking.',
        ]);
    }

    public function completeStop($id)
    {
        eco_require_role(['collector']);
        eco_flash('info', 'Marking stops complete will be built in a later checking.');
        eco_go('collector/route');
    }
}
