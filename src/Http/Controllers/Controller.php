<?php

namespace Flashpoint\Oxidiser\Http\Controllers;

use Illuminate\Support\Collection;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /** @var Collection */
    protected $routings;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->routings = app('routings');
    }
}
