<?php

namespace Flashpoint\Oxidiser\Helpers;

use Flashpoint\Fuel\Observer;
use Flashpoint\Fuel\Routing;
use Flashpoint\Oxidiser\Models\Revision;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Trait ObserverHelper
 * @package Flashpoint\Oxidiser\Helpers
 */
class ObserverHelper
{
    /**
     * Returns the handling methods or null
     */
    public static function canHandleBy($action, $name = null)
    {
        $methods = [];

        $action = Str::studly($action);
        $name = is_null($name) ? null : Str::studly($name);

        if (!is_null($name) && method_exists(static::class, "when{$action}{$name}")) {
            $methods[] = [static::class, "when{$action}{$name}"];
        }

        if (method_exists(static::class, "when{$action}")) {
            $methods[] = [static::class, "when{$action}"];
        }

        return sizeof($methods) > 0 ? $methods : null;
    }

    /**
     * @param Observer[] $observers
     * @param $action
     * @param $value
     * @param $name
     */
    public static function prepareRelevantObservers($observers, $action, $name = null)
    {
        return collect($observers + [DefaultObserver::class])
            ->reduce(function (Collection $handlers, $observer) use ($action, $name) {
                /** @var static $observer */
                return $handlers->merge($observer::canHandleBy($action, $name) ?? []);
            }, collect());
    }

    /**
     * @param Routing $routing
     * @param Revision $revision
     * @param $action
     * @param null $name
     * @return \Flashpoint\Fuel\Entities\Definitions\Entity
     */
    public static function observe(Routing $routing, Revision $revision, $action, $name = null)
    {
        $container = collect();
        $state = $revision->toState();
        $entity = $routing->entity();

        static::prepareRelevantObservers($routing->observers(), $action, $name)
            ->each(function ($handler) use (&$entity, &$state, &$container, $routing) {
                if (!$container->has($handler[0])) {
                    $instance = new $handler[0] ($routing->model(), $state, $entity);
                    $container->put($handler[0], $instance);
                }

                return $container->get($handler[0])->{$handler[1]};
            }, collect());

        return $entity;
    }
}
