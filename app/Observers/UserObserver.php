<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UserSetting;

class UserObserver
{
    public function created(User $user)
    {
		// not deleting this but it doesnt exist in plus by default
		/*
        $user->settings()->create([
            'last_hc_payday' => setting('give_hc_on_register') == '1' ? now()->addYears(10)->unix() : 0,
        ]);

        if (setting('give_hc_on_register') == '1') {
            $user->hcSubscription()->insert([
                'user_id' => $user->id,
                'subscription_type' => 'HABBO_CLUB',
                'timestamp_start' => now()->unix(),
                'duration' => (int)setting('hc_on_register_duration'),
                'active' => 1,
            ]);
        }
		*/
    }
}