<?php

namespace App\Services\Users\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // ...
    protected $table = 'roles';
    public function users()
    {
        return $this->belongsToMany('app\Services\Users\Models\User');
    }

    // ...
}
