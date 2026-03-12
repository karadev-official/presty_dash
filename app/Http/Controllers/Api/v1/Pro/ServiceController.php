<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOption;
use App\Models\ServiceOptionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{

    public function index(Request $request)
    {
        $professionalProfile = $request->user()->professionalProfile;
        $categoriesIds = $professionalProfile->serviceCategories()->pluck('id')->toArray();
        $services = Service::with('category', 'optionGroups.options')
            ->whereIn('service_category_id', $categoriesIds)
            ->get();
        return response()->json([
            'services' => ServiceResource::collection($services),
        ]);
    }

    public function show(Request $request, Service $service)
    {
        $this->authorize('view', $service->category);
        $service->load('category', 'optionGroups.options');
        return response()->json([
            'service' => new ServiceResource($service),
        ]);
    }

    public function store(ServiceRequest $request)
    {
        $this->authorize('create', ServiceCategory::class);
        $data = $request->validated();
        $service = new Service();

        DB::transaction(function () use (&$service, $request, $data) {
            $professionalProfile = $request->user()->professionalProfile;
            $service = Service::create($data);

            if (!array_key_exists('option_groups', $data)) {
                return;
            }

            $uid = $professionalProfile->id;
            $groupsPayload = $data['option_groups'];
            $sync = [];

            foreach ($groupsPayload as $gi => $g) {
                // 2) Create group (store = toujours create)
                $group = ServiceOptionGroup::create([
                    'professional_profile_id'   => $uid,
                    'client_id'                 => $g['client_id'] ?? null,
                    'name'                      => $g['name'],
                    'slug'                      => $g['slug'] ?? $g['name'],
                    'selection_type'            => $g['selection_type'],
                    'is_required'               => (bool) $g['is_required'],
                    'min_select'                => $g['min_select'] ?? 0,
                    'max_select'                => $g['max_select'] ?? null,
                    'position'                  => $g['position'] ?? $gi,
                    'is_active'                 => true,
                ]);

                // pivot
                $sync[$group->id] = ['position' => $g['position'] ?? $gi];

                // 3) Options
                foreach (($g['options'] ?? []) as $oi => $o) {
                    ServiceOption::create([
                        'service_option_group_id' => $group->id,
                        'client_id'               => $o['client_id'] ?? null,
                        'name'                    => $o['name'],
                        'slug'                    => $o['slug'] ?? $o['name'],
                        'duration'                => $o['duration'] ?? 0,
                        'price'                   => $o['price'],
                        'position'                => $o['position'] ?? $oi,
                        'is_active'               => $o['is_active'] ?? true,
                        'is_online'               => $o['is_online'] ?? true,
                        'image_id' => $o['image_id'] ?? null,
                    ]);
                }
            }
            // 4) Attach groups to service
            $service->optionGroups()->sync($sync);
            if (array_key_exists('image_ids', $data)) {
                $service->images()->sync($data['image_ids']);
            }
        });

        return response()->json([
            'service' => new ServiceResource($service->load('optionGroups.options')),
        ]);
    }

    public function update(ServiceRequest $request, Service $service)
    {
        $this->authorize('update', $service->category);
        $data = $request->validated();

        DB::transaction(function () use ($service, $data, $request) {
            $professionalProfile = $request->user()->professionalProfile;
            // 1) update service fields
            $serviceFields = collect($data)->except(['option_groups'])->toArray();
            if (!empty($serviceFields)) {
                $service->update($serviceFields);
            }

            if (!array_key_exists('option_groups', $data)) {
                return;
            }

            $uid = $professionalProfile->id;
            $groupsPayload = $data['option_groups'] ?? [];
            $sync = [];

            foreach ($groupsPayload as $gi => $g) {

                // ✅ GROUP: id != null => update ; id == null => create
                if (!empty($g['id'])) {
                    $group = ServiceOptionGroup::where('id', $g['id'])
                        ->where('professional_profile_id', $uid)
                        ->firstOrFail();

                    $group->update([
                        'name'           => $g['name'],
                        'slug'           => $g['slug'] ?? $g['name'],
                        'selection_type' => $g['selection_type'],
                        'is_required'    => (bool) $g['is_required'],
                        'min_select'     => $g['min_select'] ?? 0,
                        'max_select'     => $g['max_select'] ?? null,
                        'position'       => $g['position'] ?? $gi,
                        'is_active' => $o['is_active'] ?? true,
                        'is_online' => $o['is_online'] ?? true,
                    ]);
                } else {
                    $group = ServiceOptionGroup::create([
                        'professional_profile_id'        => $uid,
                        'client_id'      => $g['client_id'],
                        'name'           => $g['name'],
                        'slug'           => $g['slug'] ?? $g['name'],
                        'selection_type' => $g['selection_type'],
                        'is_required'    => (bool) $g['is_required'],
                        'min_select'     => $g['min_select'] ?? 0,
                        'max_select'     => $g['max_select'] ?? null,
                        'position'       => $g['position'] ?? $gi,
                        'is_active' => $o['is_active'] ?? true,
                        'is_online' => $o['is_online'] ?? true,
                    ]);
                }

                // ✅ pivot
                $sync[$group->id] = ['position' => $g['position'] ?? $gi];

                // ✅ OPTIONS
                $optionsPayload = $g['options'] ?? [];
                $keptOptionIds = [];

                foreach ($optionsPayload as $oi => $o) {

                    $opt = ServiceOption::updateOrCreate(
                        [
//                            'id' => $o['id'] ?? null,
                            'service_option_group_id' => $group->id,
                            'client_id' => $o['client_id'],
                        ],
                        [
                            'name'     => $o['name'],
                            'slug'     => $o['slug'] ?? $o['name'],
                            'duration' => $o['duration'] ?? 0,
                            'price'    => $o['price'], // centimes
                            'position' => $o['position'] ?? $oi,
                            'is_active' => $o['is_active'] ?? true,
                            'is_online' => $o['is_online'] ?? true,
                            'image_id' => $o['image_id'] ?? null,
                        ]
                    );
                    $keptOptionIds[] = $opt->id;
                }

                // ✅ delete options removed in this group
                ServiceOption::where('service_option_group_id', $group->id)
                    ->when(count($keptOptionIds) > 0, fn($q) => $q->whereNotIn('id', $keptOptionIds))
                    ->when(count($keptOptionIds) === 0, fn($q) => $q) // delete all
                    ->delete();
            }

            // ✅ sync groups attached to service (detach missing)
            $service->optionGroups()->sync($sync);
            // Sync images if provided
            if (array_key_exists('image_ids', $data)) {
                $service->images()->sync($data['image_ids']);
            }
        });


        $service->refresh();

        return response()->json([
            'service' => new ServiceResource($service->load('optionGroups.options')),
        ]);
    }

    public function destroy(Request $request, Service $service)
    {
        $this->authorize('delete', $service->category);
        $service->delete();
        return response()->json(['message' => 'Service supprimé avec succès.']);
    }
}
