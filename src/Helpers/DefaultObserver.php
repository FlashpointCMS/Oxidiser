<?php

namespace Flashpoint\Oxidiser\Helpers;

use Flashpoint\Fuel\Observer;
use Flashpoint\Oxidiser\Models\Revision;

class DefaultObserver extends Observer
{
    public function whenPublished()
    {
        $attributes = collect($this->state->all())->only($this->store->getFillable())->toArray();

        $this->store->fill($attributes)->save();
    }

    public function whenRestored()
    {
        $this->state->set(
            Revision::query()->withTrashed()->find(
                $this->request->get('sequence')
            )->toState()
        );
    }
}
