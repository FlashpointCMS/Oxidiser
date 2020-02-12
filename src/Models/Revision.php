<?php

namespace Flashpoint\Oxidiser\Models;

use Flashpoint\Fuel\Routing;
use Flashpoint\Fuel\State;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

/**
 * Class Revision
 * @package Flashpoint\Oxidiser\Models
 * @method static Builder|self query()
 * @method Builder|self fromRouting(Routing $routing)
 * @method Builder|self ownedBy(Authenticator $authenticator)
 * @method Builder|self notPublished()
 * @method Builder|self published()
 * @property Authenticator creator
 */
class Revision extends Model
{
    private static $stateCache;

    use HybridRelations, SoftDeletes;

    protected $table = 'flashpoint_revisions';

    protected $primaryKey = 'sequence_id';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'state' => 'array'
    ];

    protected $fillable = [
        'id',
        'routing',
        'previous_sequence_id',
        'authenticator_id',
        'state'
    ];

    public function revisions()
    {
        return $this->hasMany(self::class, 'id', 'id');
    }

    public function latestRevision()
    {
        return $this->hasOne(self::class, 'id', 'id')->orderByDesc('created_at');
    }

    public function entry(Routing $routing = null)
    {
        return $this->hasOne(($routing ?? app(Routing::class))->model(), '_entry_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo($this->getDefaultUserProvider(), 'authenticator_id');
    }

    /**
     * @return State
     */
    public function toState()
    {
        if(!static::$stateCache) {
            static::$stateCache = new State($this->state);
        }
        return self::$stateCache;
    }

    /**
     * @param Builder $builder
     * @param Routing $routing
     * @return mixed
     */
    public function scopeFromRouting($builder, Routing $routing)
    {
        return $builder->where('routing', $routing->name())->orderByDesc('created_at');
    }

    /**
     * @param Builder $builder
     * @param Authenticator $user
     * @return mixed
     */
    public function scopeOwnedBy($builder, Authenticatable $user)
    {
        return $builder->where('authenticator_id', $user->getAuthIdentifier());
    }

    /**
     * @param Builder $builder
     * @return mixed
     */
    public function scopeNotPublished($builder)
    {
        return $builder->whereNull('published_at');
    }

    /**
     * @param Builder $builder
     * @return mixed
     */
    public function scopePublished($builder)
    {
        return $builder->whereNotNull('published_at');
    }

    public function revise(Authenticatable $authenticator = null)
    {
        return new static([
            'id' => $this->id,
            'routing' => $this->routing,
            'previous_sequence_id' => $this->id,
            'authenticator_id' => ($authenticator ?? app(Request::class)->user())->getAuthIdentifier(),
            'state' => $this->state
        ]);
    }

    public function firstOrCreate()
    {

    }

    private function getDefaultUserProvider()
    {
        /** @var AuthManager $auth */
        $auth = app('auth');
        return config(
            'auth.providers.' . config(
                "auth.guards.{$auth->getDefaultDriver()}.provider"
            ) . '.model'
        );
    }
}
