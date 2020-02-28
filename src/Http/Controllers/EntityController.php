<?php

namespace Flashpoint\Oxidiser\Http\Controllers;

use Flashpoint\Fuel\Entities\Definitions\Collection;
use Flashpoint\Fuel\Entities\Definitions\Entity;
use Flashpoint\Fuel\Routing;
use Flashpoint\Oxidiser\Helpers\ObserverHelper;
use Flashpoint\Oxidiser\Models\Revision;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EntityController extends Controller
{
    public function index(Routing $routing)
    {
        if ($routing->entity()::plural()) {
            $collection = new Collection();
            $routing->entity()::collection($collection);

            $entries = Revision::query()
                ->fromRouting($routing)
                ->newest()
                ->get();

            return $collection->populate(function ($builder) use ($entries) {
                foreach ($entries as $revision) {
                    /** @var Revision $revision */
                    yield $builder(
                        $revision->toState(),
                        $revision->entry()->firstOrNew([])
                    )->put('_meta', [
                        'id' => $revision->id,
                        'published' => $revision->published,
                        'real' => $revision->real,
                    ]);
                }
            });
        } else {
            $revision = Revision::query()
                ->fromRouting($routing)
                ->newest()
                ->firstOrFail();

            return response(['id' => $revision->id], 303);
        }
    }

    public function create(Request $request, Routing $routing)
    {
        $revision = new Revision([
            'routing' => $routing->name(),
            'authenticator_id' => $request->user()->id
        ]);

        $revision->save();
        $revision->refresh();

        return response(['id' => $revision->id], 303);
    }

    public function show(Routing $routing, $id, $sequence = null)
    {
        $entity = $routing->entity();
        $revision = Revision::query()
            ->fromRouting($routing)
            ->when($sequence, function ($builder, $sequence) {
                /** @var Revision $builder */
                return $builder->withTrashed()->find($sequence);
            }, function ($builder) use ($id) {
                /** @var Revision $builder */
                return $builder->newest($id)->firstOrFail();
            });

        /** @var Entity $entity */
        $entity = new $entity ($revision->toState(), $revision->entry()->firstOrNew([]));

        $meta = [
            'published' => $revision->published,
            'real' => $revision->real,
            'published_at' => $revision->published_at,
            'revisions' => $revision->revisions->sortByDesc('deleted_at')->map(function (Revision $revision) {
                return [
                    'sequence_id' => (string)$revision->sequence_id,
                    'name' => $revision->deleted_at->format('d M y \a\t\ H:i:s')
                ];
            })->values()
        ];
        if (!empty($revision->deleted_at)) {
            $meta['deleted_at'] = $revision->deleted_at;
        }

        $entity->meta($meta);

        return $entity;
    }

    /**
     * @param Request $request
     * @param Routing $routing
     * @param $id
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function handle(Request $request, Routing $routing, $id)
    {
        $revision = Revision::fromRouting($routing)->newest($id)->firstOrFail();

        if ($revision->published && $request->get('event') != 'deleted') {
            $revision = $revision->revise();
        }

        if ($request->has('state')) {
            $revision->toState()->update($request->get('state'));
        } elseif ($request->has('value')) {
            $revision->toState()->put($request->get('field'), $request->get('value'));
        }

        $model = ObserverHelper::observe($routing, $revision, $request);

        switch (true) {
            case $request->get('event') == 'published' && $model->exists:
                if (!empty($revision->previousRevision)) {
                    $revision->previousRevision->delete();
                }
                $revision->published_at = Carbon::now();
                break;
            case $request->get('event') == 'deleted' && !empty($revision->entry):
                $revision->entry->delete();
            case $request->get('event') == 'discarded':
                $revision->deleted_at = Carbon::now();
                break;

        }

        $revision->save();

        return response(['id' => $revision->id], 303);
    }
}
