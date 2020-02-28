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
 * @method Builder|self newest($id = null)
 * @property Authenticator creator
 * @property State state
 */
class Revision extends Model
{
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

    /** @var State */
    private $_state;

    public static function boot()
    {
        parent::boot();

        static::saving(function ($instance) {
            if (!empty($instance->_state)) {
                /** @var static $instance */
                $instance->state = $instance->_state->all();
            }
            return true;
        });
    }

    public function revisions()
    {
        $query = $this->hasMany(self::class, 'id', 'id');
        /** @var self $query */
        return $query->published()->onlyTrashed();
    }

    public function latestRevision()
    {
        return $this->hasOne(self::class, 'id', 'id')->orderByDesc('created_at');
    }

    public function previousRevision()
    {
        return $this
            ->belongsTo(self::class, 'previous_sequence_id', 'sequence_id')
            ->orderByDesc('created_at');
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
     * @param Builder $builder
     * @param Routing $routing
     * @return mixed
     */
    public function scopeFromRouting($builder, Routing $routing)
    {
        return $builder->where('routing', $routing->name());
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
    public function scopeNewest($builder, $id = null)
    {
        return $builder->distinct('id')
            ->when($id, function ($builder, $id) {
                /** @var Builder $builder */
                return $builder->where('id', $id);
            })
            ->orderBy('id')->orderByDesc('created_at');
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
            'previous_sequence_id' => $this->sequence_id,
            'authenticator_id' => ($authenticator ?? app(Request::class)->user())->getAuthIdentifier(),
            'state' => $this->toState()->all()
        ]);
    }

    public function toState()
    {
        if (empty($this->_state)) {
            $this->_state = new State($this->state);
            $this->state = State::class;
        }

        return $this->_state;
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

    public function getPublishedAttribute()
    {
        return !is_null($this->published_at);
    }

    public function getRealAttribute()
    {
        return !is_null($this->entry);
    }
}
