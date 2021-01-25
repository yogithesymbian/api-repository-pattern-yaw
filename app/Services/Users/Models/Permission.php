<?php

namespace App\Services\Users\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    public function users()
    {
        return $this->belongsToMany('app\Services\Users\Models\User');
    }
}
