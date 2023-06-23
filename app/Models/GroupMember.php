<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupMember extends Model
{
    protected $table = 'group_memberships';

    protected $guarded = ['id'];

    public $timestamps = false;

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }
}