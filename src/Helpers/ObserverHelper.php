<?php

namespace Flashpoint\Oxidiser\Helpers;

use Flashpoint\Fuel\Models\Model;
use Flashpoint\Fuel\Observer;
use Flashpoint\Fuel\Routing;
use Flashpoint\Oxidiser\Models\Revision;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Trait ObserverHelper
 * @package Flashpoint\Oxidiser\Helpers
 */
class ObserverHelper
{
    /**
     * @param Observer[] $observers
     * @param $action
     * @param $value
     * @param $name
     */
    public static function prepareRelevantObservers($observers, $action, $name = null)
    {
        return collect($observers)->push(DefaultObserver::class)
            ->reduce(function (Collection $handlers, $observer) use ($action, $name) {
                /** @var Observer $observer */
                return $handlers->merge($observer::canHandleBy($action, $name) ?? []);
            }, collect());
    }

    /**
     * @param Routing $routing
     * @param Revision $revision
     * @param $action
     * @param null $name
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function observe(Routing $routing, Revision $revision, Request $request)
    {
        $container = collect();
        $state = $revision->toState();
        $model = $revision->entry()->firstOrNew([]);
        $entity = $routing->entity();
        $entity = new $entity ($state, $model);

        static::prepareRelevantObservers(
            $routing->observers(),
            $request->get('event'),
            $request->get('field', null)
        )->each(function ($handler) use (&$entity, &$state, &$container, $routing, $model, $request) {
            if (!$container->has($handler[0])) {
                $instance = new $handler[0] ($model, $state, $entity, $request);
                $container->put($handler[0], $instance);
            }

            return $container->get($handler[0])->{$handler[1]}();
        }, collect());

        return $model;
    }
}
