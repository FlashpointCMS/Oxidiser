<?php

namespace Flashpoint\Oxidiser\Helpers;

use Flashpoint\Fuel\Observer;

class DefaultObserver extends Observer
{
    public function whenSaved()
    {
        if ($this->entity->type()->isPlural()) {
            $this->store->newInstance($this->state->all())->save();
        } elseif ($this->entity->type()->isSingular()) {
            $this->store->fill($this->state->all())->save();
        }
    }
}
