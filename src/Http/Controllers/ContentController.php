<?php

namespace Flashpoint\Oxidiser\Http\Controllers;

use Flashpoint\Fuel\Models\Model;
use Flashpoint\Fuel\Routing;

class ContentController extends Controller
{
    public function get(Routing $routing)
    {
        /** @var Model $model */
        $model = $routing->model();

        return $model::queryForContent()->get();
    }
}
