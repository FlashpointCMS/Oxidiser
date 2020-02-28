<?php

namespace Flashpoint\Oxidiser\Http\Controllers;

use Flashpoint\Fuel\Routing;
use Illuminate\Support\Collection;

class EntitiesController extends Controller
{
    /**
     * Show all the entities available for management.
     *
     * @param int $id
     */
    public function __invoke()
    {
        /** @var Collection $routings */
        $routings = app('routings');
        return $routings;
    }
}