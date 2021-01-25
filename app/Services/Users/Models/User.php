<?php

namespace App\Services\Users\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Collection;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

use App\Services\Users\Models\Role; // relation roles and permissions
use App\Services\Users\Models\Permission; // relation roles and permissions

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable;

    protected $table = 'users';

    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
     public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_photo',
        'totalActiveTime',
        'api_token',
        'remember_token',
        'secret',
        'created_at',
        'updated_at',

    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'api_token',
        'secret',
    ];

    public function roles()
    {
        return $this->belongsToMany('app\Services\Users\Models\Role');
    }

    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        return $this->roles()->attach($role);
    }

    public function revokeRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        return $this->roles()->detach($role);
    }

    public function hasRole($name)
    {
        foreach($this->roles as $role)
        {
            if ($role->name === $name) return true;
        }

        return false;
    }

    public function myRole()
    {
        $role_user = new Collection();
        foreach($this->roles as $role)
        {
            $role_user->push(
                $role->name
            );
        }
        return $role_user;
    }
    // ...

    public function permissions()
    {
        return $this->belongsToMany('app\Services\Users\Models\Permission');
    }

    public function assignPermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        return $this->permissions()->attach($permission);
    }

    public function revokePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        return $this->permissions()->detach($permission);
    }

    public function hasPermission($name)
    {
        foreach($this->permissions as $permission)
        {
            if ($permission->name === $name) return true;
        }

        return false;
    }

    public function myPermission()
    {
        $permission_user = new Collection();
        foreach($this->permissions as $permission)
        {
            $permission_user->push(
                $permission->name
            );
        }
        return $permission_user;
    }
    // ...

}
