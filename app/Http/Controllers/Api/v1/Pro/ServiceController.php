<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Models\Service;
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
            'description' => ['nullable', 'string', 'max:600'],
            'duration' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_online' => ['sometimes', 'boolean'],
            'image_ids' => ['nullable', 'array'],
            'option_groups' => ['sometimes', 'array'],

            'option_groups.*.id' => ['nullable', 'integer'], // pas de exists ici si tu veux être souple
            'option_groups.*.client_id' => ['sometimes', 'string', 'max:255'], // interface only (ignored)
            'option_groups.*.name' => ['required_with:option_groups', 'string', 'max:255'],
            'option_groups.*.slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'option_groups.*.selection_type' => ['required_with:option_groups', Rule::in(['single', 'multiple'])],
            'option_groups.*.is_required' => ['required_with:option_groups', 'boolean'],
            'option_groups.*.min_select' => ['sometimes', 'integer', 'min:0'],
            'option_groups.*.max_select' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'option_groups.*.position' => ['sometimes', 'integer', 'min:0'],

            'option_groups.*.options' => ['sometimes', 'array'],
            'option_groups.*.options.*.id' => ['nullable', 'integer'],
            'option_groups.*.options.*.client_id' => ['sometimes', 'string', 'max:255'], // ignored
            'option_groups.*.options.*.name' => ['required_with:option_groups.*.options', 'string', 'max:255'],
            'option_groups.*.options.*.slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'option_groups.*.options.*.duration' => ['sometimes', 'integer', 'min:0'],
            'option_groups.*.options.*.price' => ['required_with:option_groups.*.options', 'integer', 'min:0'], // centimes
            'option_groups.*.options.*.position' => ['sometimes', 'integer', 'min:0'],
            'option_groups.*.options.*.image_id' => ['sometimes', 'nullable', 'integer', 'exists:images,id'],
        ]);


        $service = new Service();

        DB::transaction(function () use (&$service, $request, $data) {
            $service = Service::create([
                'user_id' => $request->user()->id,
                ...collect($data)->except(['option_groups'])->toArray(),
            ]);

            if (!array_key_exists('option_groups', $data)) {
                return;
            }

            $uid = $request->user()->id;
            $groupsPayload = $data['option_groups'];
            $sync = [];

            foreach ($groupsPayload as $gi => $g) {
                // 2) Create group (store = toujours create)
                $group = ServiceOptionGroup::create([
                    'user_id'        => $uid,
                    'client_id'      => $g['client_id'] ?? null,
                    'name'           => $g['name'],
                    'slug'           => $g['slug'] ?? $g['name'],
                    'selection_type' => $g['selection_type'],
                    'is_required'    => (bool) $g['is_required'],
                    'min_select'     => $g['min_select'] ?? 0,
                    'max_select'     => $g['max_select'] ?? null,
                    'position'       => $g['position'] ?? $gi,
                    'is_active'      => true,
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
            'service' => $this->servicePayload($service->load('optionGroups.options')),
        ]);
    }

    public function update(Request $request, Service $service)
    {
        abort_unless($service->user_id === $request->user()->id, 404);

        Log::info($request->input("option_groups"));
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255'],
            'service_category_id' => ['sometimes', 'exists:service_categories,id'],
            'description' => ['nullable', 'string', 'max:600'],
            'duration' => ['required', 'integer', 'min:0'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_online' => ['sometimes', 'boolean'],
            'image_ids' => ['nullable', 'array'],
            'option_groups' => ['sometimes', 'array'],

            'option_groups.*.id' => ['nullable', 'integer'], // pas de exists ici si tu veux être souple
            'option_groups.*.client_id' => ['sometimes', 'string', 'max:255'], // interface only (ignored)
            'option_groups.*.name' => ['required_with:option_groups', 'string', 'max:255'],
            'option_groups.*.slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'option_groups.*.selection_type' => ['required_with:option_groups', Rule::in(['single', 'multiple'])],
            'option_groups.*.is_required' => ['required_with:option_groups', 'boolean'],
            'option_groups.*.min_select' => ['sometimes', 'integer', 'min:0'],
            'option_groups.*.max_select' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'option_groups.*.position' => ['sometimes', 'integer', 'min:0'],

            'option_groups.*.options' => ['sometimes', 'array'],
            'option_groups.*.options.*.id' => ['nullable', 'integer'],
            'option_groups.*.options.*.client_id' => ['sometimes', 'string', 'max:255'], // ignored
            'option_groups.*.options.*.name' => ['required_with:option_groups.*.options', 'string', 'max:255'],
            'option_groups.*.options.*.slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'option_groups.*.options.*.duration' => ['sometimes', 'integer', 'min:0'],
            'option_groups.*.options.*.price' => ['required_with:option_groups.*.options', 'integer', 'min:0'], // centimes
            'option_groups.*.options.*.position' => ['sometimes', 'integer', 'min:0'],
            'option_groups.*.options.*.image_id' => ['sometimes', 'nullable', 'integer', 'exists:images,id'],
        ]);

        DB::transaction(function () use ($service, $data, $request) {

            // 1) update service fields
            $serviceFields = collect($data)->except(['option_groups'])->toArray();
            if (!empty($serviceFields)) {
                $service->update($serviceFields);
            }

            if (!array_key_exists('option_groups', $data)) {
                return;
            }

            $uid = $request->user()->id;
            $groupsPayload = $data['option_groups'] ?? [];
            $sync = [];

            foreach ($groupsPayload as $gi => $g) {

                // ✅ GROUP: id != null => update ; id == null => create
                if (!empty($g['id'])) {
                    $group = ServiceOptionGroup::where('id', $g['id'])
                        ->where('user_id', $uid)
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
                        'user_id'        => $uid,
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
                            'id' => $o['id'] ?? null,
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
                            'duration' => $option->duration,
                            'name' => $option->name,
                            'price' => $option->price,
                            'position' => $option->position,
                            'is_active' => $option->is_active,
                            'is_online' => $option->is_online,
                            'image_id' => $option->image->id ?? null,
                            'image_url' => $option->image->url ?? null,
                        ];
                    }),
                ];
            }),
            'images' => $service->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                ];
            })
        ];
    }
}
