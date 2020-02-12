<?php

namespace Flashpoint\Oxidiser\Models;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Laravel\Passport\HasApiTokens;

class Authenticator extends Model implements AuthenticatableContract
{
    use Authenticatable, HasApiTokens, HybridRelations;

    protected $table = 'flashpoint_authenticators';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'locked_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_user' => 'boolean',
        'permissions' => 'array'
    ];

    public function getLockedAttribute()
    {
        return !is_null($this->locked_at);
    }

    public function setLockedAttribute($val)
    {
        $this->attributes['locked_at'] = $val ? Carbon::now() : null;
    }

    public function setPasswordAttribute($val)
    {
        /** @var \Illuminate\Hashing\HashManager $hasher */
        $hasher = app('hash');
        $this->attributes['password'] = $val ? $hasher->make($val) : null;
    }

    public function findForPassport($username)
    {
        return $this->query()->where('username', $username)->first();
    }

    public function validateForPassportPasswordGrant($password)
    {
        /** @var \Illuminate\Hashing\HashManager $hasher */
        $hasher = app('hash');
        return $this->is_user && $hasher->check($password, $this->password);
    }

    public function revisions() {
        return $this->hasMany(Revision::class, 'authenticator_id');
    }
}
