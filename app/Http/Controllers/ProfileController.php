<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\MessengerFriendship;
use App\Models\Room;

class ProfileController extends Controller
{
    public function __invoke(User $user)
    {
        $user = $user->load(['badges' => function ($badges) {
            $badges->where('badge_slot', '>', '0')
                ->orderBy('badge_slot')
                ->take(5)
                ->get();
        },
        'rooms' => function ($rooms) {
            $rooms->select('id', 'owner', 'caption', 'users_now')
                ->orderByDesc('users_now')
                ->orderBy('id')
                ->take(4)
                ->get();
        }]);

        $friends = MessengerFriendship::select('user_two_id')
            ->where('user_one_id', '=', $user->id)
            ->whereHas('user')
            ->take(12)
            ->inRandomOrder()
            ->with('user:id,username,look')
            ->get();

        $groups = GroupMember::select(['group_memberships.id', 'group_memberships.group_id', 'group_memberships.user_id', 'groups.name', 'groups.badge'])
            ->where('group_memberships.user_id', '=', $user->id)
            ->join('groups', 'group_memberships.group_id', '=', 'groups.id')
            ->take(6)
            ->inRandomOrder()
            ->get();

        return view('user.profile', [
            'user' => $user,
            'friends' => $friends,
            'groups' => $groups,
        ]);
    }
}
