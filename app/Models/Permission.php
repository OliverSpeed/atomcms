<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
	protected $table = 'permissions_groups';
    public $timestamps = false;

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'rank', 'id');
    }
}