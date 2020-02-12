<?php

namespace Flashpoint\Oxidiser\Http\Controllers;

use Flashpoint\Fuel\Entities\Definitions\Collection;
use Flashpoint\Fuel\Routing;
use Flashpoint\Oxidiser\Helpers\ObserverHelper;
use Flashpoint\Oxidiser\Models\Revision;
use Illuminate\Http\Request;

class EntityController extends Controller
{
    public function index(Request $request, Routing $routing)
    {
        if (!$routing->entity()::type()->isPlural()) {
            $collection = new Collection();
            $routing->entity()::collection($collection);

            return dd($collection);
        } else {
            $entity = $routing->entity();

            $unpublished = Revision::query()
                ->ownedBy($request->user())
                ->notPublished()
                ->fromRouting($routing)
                ->first();
            $published = Revision::query()
                ->published()
                ->fromRouting($routing)
                ->first();
            $revision = $unpublished ?? $published;

            if (is_null($revision)) {
                abort(404, 'Singular instance not found');
            }

            return dd($unpublished, $published, new $entity (
                $routing->model()::query()->firstOrNew([]),
                $revision->toState()
            ));
        }
    }

    public function create(Request $request, Routing $routing, $id)
    {
        $revision = Revision::query()
            ->fromRouting($routing);

        if ($id) {
            $revision = $revision->findOrFail($id);
        } else {
            $revision = $revision->published()->firstOrCreate();
        }

        if ($revision->exists() || $revision->creator->id !== $request->user()->id) {
            $revision = $revision->revise();
        }

        $revision->save();
        return dd($revision);
    }

    public function show(Routing $routing, $id)
    {
        $entity = $routing->entity();
        $model = $routing->model();
        $revision = Revision::fromRouting($routing)->findOrFail($id);
        return dd($revision, new $entity ($model::query()->firstOrNew([]), $revision->toState()));
    }

    public function handle(Request $request, Routing $routing, $id)
    {
        $revision = Revision::fromRouting($routing)->find($id);

        if ($revision->creator->id != $request->user()->id) {
            abort(403, 'Revision is not owned by logged in user');
        }

        if($request->has('state')) {
            $revision->toState()->set($request->get('state'));
        }

        $entity = ObserverHelper::observe($routing, $revision, $request->get('action'), $revision->get('name'));
        $revision->save();

        dd($entity, $revision);
    }
}
