<?php

namespace App\Http\Controllers;

use App\Models\Ban;
use Illuminate\Support\Facades\Auth;

class BannedController extends Controller
{
    public function __invoke()
    {
        $ipBan = Ban::where('value', '=', request()->ip())
            ->where('expire', '>', time())
			->where('bantype', '=', 'ip')
            ->orderByDesc('id')
            ->first();

        return view('banned', [
            'ban' => $ipBan ?? Auth::user()->ban
        ]);
    }
}
