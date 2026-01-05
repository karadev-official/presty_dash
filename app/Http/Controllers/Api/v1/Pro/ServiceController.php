<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceOption;
use App\Models\ServiceOptionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{

    public function index(Request $request)
    {
        $services = Service::with('category', 'optionGroups.options')
            ->where('user_id', $request->user()->id)
            ->get();

        $services = $services->map(function ($service) {
            return $this->servicePayload($service);
        });

        return response()->json([
            'services' => $services,
        ]);
    }

    public function show(Request $request, Service $service)
    {
        abort_unless($service->user_id === $request->user()->id, 404);
        $service->load('category', 'optionGroups.options');

        return response()->json([
            'service' => $this->servicePayload($service),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'description' => ['sometimes', 'string'],
            'duration' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_online' => ['sometimes', 'boolean'],
        ]);


        $service = Service::create([
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        // groupe de services
        if (isset($data['option_groups'])) {
            // foreach ($data['option_groups'] as $index => $groupToCreate) {

            // }
        }

        return response()->json([
            'service' => $this->servicePayload($service),
        ]);
    }

    public function update(Request $request, Service $service)
    {
        abort_unless($service->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255'],
            'service_category_id' => ['sometimes', 'exists:service_categories,id'],
            'description' => ['sometimes', 'string'],
            'duration' => ['sometimes', 'integer', 'min:1'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_online' => ['sometimes', 'boolean'],
            'option_groups' => ['sometimes', 'array'],
        ]);

        // DB::transaction(function () use ($request, $service, $data) {

        //     // 1) update service fields
        //     $serviceFields = collect($data)->except('option_groups')->toArray();
        //     if (!empty($serviceFields)) {
        //         $service->update($serviceFields);
        //     }

        //     // 2) option groups sync (create/update + attach + options sync)
        //     if (!array_key_exists('option_groups', $data)) {
        //         return;
        //     }

        //     $incomingGroups = $data['option_groups'] ?? [];
        //     $userId = $request->user()->id;

        //     // ids actuellement attachés à ce service
        //     $currentAttachedIds = $service->optionGroups()->pluck('service_option_groups.id')->all();

        //     $attachMap = [];           // group_id => ['position' => ...]
        //     $keptGroupIds = [];        // ids qui restent attachés
        //     $seenOptionIdsByGroup = []; // [groupId => [optionIds...]]

        //     foreach ($incomingGroups as $gIndex => $g) {

        //         $groupId = is_numeric($g['id'] ?? null) ? (int)$g['id'] : null;

        //         // soit update un groupe existant du user, soit create
        //         if ($groupId) {
        //             $group = ServiceOptionGroup::where('id', $groupId)
        //                 ->where('user_id', $userId)
        //                 ->firstOrFail();

        //             $group->update([
        //                 'title' => $g['title'],
        //                 'selection_type' => $g['selection_type'],
        //                 'is_required' => (bool)($g['is_required'] ?? false),
        //                 'min_select' => $g['min_select'] ?? null,
        //                 'max_select' => $g['max_select'] ?? null,
        //                 'is_active' => (bool)($g['is_active'] ?? true),
        //                 'position' => $g['position'] ?? $gIndex,
        //             ]);
        //         } else {
        //             $group = ServiceOptionGroup::create([
        //                 'user_id' => $userId,
        //                 'title' => $g['title'],
        //                 'selection_type' => $g['selection_type'],
        //                 'is_required' => (bool)($g['is_required'] ?? false),
        //                 'min_select' => $g['min_select'] ?? null,
        //                 'max_select' => $g['max_select'] ?? null,
        //                 'is_active' => (bool)($g['is_active'] ?? true),
        //                 'position' => $g['position'] ?? $gIndex,
        //             ]);
        //         }

        //         $keptGroupIds[] = $group->id;

        //         // pivot attach + position
        //         $attachMap[$group->id] = [
        //             'position' => $g['position'] ?? $gIndex,
        //         ];

        //         // 3) options du groupe (create/update + delete missing)
        //         $incomingOptions = $g['options'] ?? [];
        //         $keptOptionIds = [];

        //         foreach ($incomingOptions as $oIndex => $o) {
        //             $optId = is_numeric($o['id'] ?? null) ? (int)$o['id'] : null;

        //             if ($optId) {
        //                 $opt = ServiceOption::where('id', $optId)
        //                     ->where('service_option_group_id', $group->id)
        //                     ->firstOrFail();

        //                 $opt->update([
        //                     'label' => $o['label'],
        //                     'duration_min' => $o['duration_min'] ?? 0,
        //                     'price' => $o['price'] ?? 0,
        //                     'position' => $o['position'] ?? $oIndex,
        //                     'is_active' => (bool)($o['is_active'] ?? true),
        //                 ]);
        //             } else {
        //                 $opt = ServiceOption::create([
        //                     'service_option_group_id' => $group->id,
        //                     'label' => $o['label'],
        //                     'duration_min' => $o['duration_min'] ?? 0,
        //                     'price' => $o['price'] ?? 0,
        //                     'position' => $o['position'] ?? $oIndex,
        //                     'is_active' => (bool)($o['is_active'] ?? true),
        //                 ]);
        //             }

        //             $keptOptionIds[] = $opt->id;
        //         }

        //         // supprimer options absentes du payload (au choix: delete ou is_active=false)
        //         // version delete :
        //         ServiceOption::where('service_option_group_id', $group->id)
        //             ->when(count($keptOptionIds) > 0, fn($q) => $q->whereNotIn('id', $keptOptionIds))
        //             ->when(count($keptOptionIds) === 0, fn($q) => $q) // delete all if empty list
        //             ->delete();

        //         $seenOptionIdsByGroup[$group->id] = $keptOptionIds;
        //     }

        //     // 4) detach les groupes supprimés dans le payload
        //     $toDetach = array_values(array_diff($currentAttachedIds, $keptGroupIds));
        //     if (!empty($toDetach)) {
        //         $service->optionGroups()->detach($toDetach);
        //     }

        //     // 5) attach/update pivot positions (syncWithoutDetaching + updateExistingPivot)
        //     // le plus simple: sync() complet du pivot avec positions :
        //     $service->optionGroups()->sync($attachMap);
        // });

        $service->refresh();

        return response()->json([
            'service' => $this->servicePayload($service->load('optionGroups.options')),
        ]);
    }

    public function destroy(Request $request, Service $service)
    {
        abort_unless($service->user_id === $request->user()->id, 404);

        $service->delete();

        return response()->json(['message' => 'Service supprimé avec succès.']);
    }
    public function servicePayload(Service $service)
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->slug,
            'description' => $service->description,
            'duration' => $service->duration,
            'price' => $service->price,
            'is_active' => $service->is_active,
            'is_online' => $service->is_online,
            'category' => $service->category ? [
                'id' => $service->category->id,
                'name' => $service->category->name,
                'slug' => $service->category->slug,
            ] : null,
            'option_groups' => $service->optionGroups->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'client_id' => $group->client_id,
                    'selection_type' => $group->selection_type,
                    'is_required' => $group->is_required,
                    'min_select' => $group->min_select,
                    'max_select' => $group->max_select,
                    'position' => $group->pivot->position,
                    'options' => $group->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'client_id' => $option->client_id,
                            'name' => $option->name,
                            'price' => $option->price,
                            'position' => $option->position,
                        ];
                    }),
                ];
            }),
        ];
    }
}
