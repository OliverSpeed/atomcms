<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserStat;

class LeaderboardController extends Controller
{
    public function __invoke()
    {
        $staffUsers = User::select('id')->where('rank', '>=', setting('min_staff_rank'))->get()->pluck('id');

        $mostOnline = UserStat::whereNotIn('id', $staffUsers)
            ->select('id', 'OnlineTime')
            ->orderByDesc('OnlineTime')
            ->take(9)
            ->with('user:id,username,look')
            ->get(['id', 'OnlineTime']);

        $respectsReceived = UserStat::whereNotIn('id', $staffUsers)
            ->select('id', 'Respect')
            ->orderByDesc('Respect')
            ->take(9)
            ->with('user:id,username,look')
			->get(['id', 'Respect']);

        $achievementScores = UserStat::whereNotIn('id', $staffUsers)
            ->select('id', 'AchievementScore')
            ->orderByDesc('AchievementScore')
            ->take(9)
            ->with('user:id,username,look')
            ->get(['id', 'AchievementScore']);

        return view('leaderboard', [
            'credits' => User::whereNotIn('id', $staffUsers)->orderByDesc('credits')->take(9)->get(),
            'duckets' => User::whereNotIn('id', $staffUsers)->orderByDesc('activity_points')->take(9)->get(),
            'diamonds' => User::whereNotIn('id', $staffUsers)->orderByDesc('vip_points')->take(9)->get(),
            'mostOnline' => $mostOnline,
            'respectsReceived' => $respectsReceived,
            'achievementScores' => $achievementScores,
        ]);
    }
}
