<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOptionGroupRequest;
use App\Http\Requests\StoreOptionRequest;
use App\Models\ServiceOption;
use App\Models\ServiceOptionGroup;
use Illuminate\Http\Request;

class OptionGroupController extends Controller
{
    public function index(Request $request)
    {
        $groups = ServiceOptionGroup::where('user_id', $request->user()->id)
            ->with('options')
            ->orderBy('position')
            ->get();

        return response()->json(['groups' => $groups]);
    }

    public function store(StoreOptionGroupRequest $request)
    {
        $data = $request->validated();

        // si obligatoire et selection single → min_select=1
        if (($data['is_required'] ?? false) && ($data['selection_type'] ?? 'single') === 'single') {
            $data['min_select'] = 1;
            $data['max_select'] = 1;
        }

        $group = ServiceOptionGroup::create([
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        return response()->json(['group' => $group->load('options')], 201);
    }

    public function show(Request $request, ServiceOptionGroup $group)
    {
        abort_unless($group->user_id === $request->user()->id, 404);
        return response()->json(['group' => $group->load('options')]);
    }

    public function update(StoreOptionGroupRequest $request, ServiceOptionGroup $group)
    {
        abort_unless($group->user_id === $request->user()->id, 404);

        $data = $request->validated();

        // cohérence
        if (($data['selection_type'] ?? $group->selection_type) === 'single') {
            // single => max=1 si required
            if (($data['is_required'] ?? $group->is_required) === true) {
                $data['min_select'] = 1;
                $data['max_select'] = 1;
            } else {
                $data['max_select'] = 1;
            }
        }

        $group->update($data);

        return response()->json(['group' => $group->load('options')]);
    }

    public function destroy(Request $request, ServiceOptionGroup $group)
    {
        abort_unless($group->user_id === $request->user()->id, 404);
        $group->delete();

        return response()->json(['ok' => true]);
    }

    // ----- OPTIONS -----

    public function storeOption(StoreOptionRequest $request, ServiceOptionGroup $group)
    {
        abort_unless($group->user_id === $request->user()->id, 404);

        $option = $group->options()->create($request->validated());

        return response()->json(['option' => $option], 201);
    }

    public function updateOption(StoreOptionRequest $request, ServiceOptionGroup $group, ServiceOption $option)
    {
        abort_unless($group->user_id === $request->user()->id, 404);
        abort_unless($option->service_option_group_id === $group->id, 404);

        $option->update($request->validated());

        return response()->json(['option' => $option]);
    }

    public function destroyOption(Request $request, ServiceOptionGroup $group, ServiceOption $option)
    {
        abort_unless($group->user_id === $request->user()->id, 404);
        abort_unless($option->service_option_group_id === $group->id, 404);

        $option->delete();

        return response()->json(['ok' => true]);
    }
}
