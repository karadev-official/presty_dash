<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class ServiceOptionGroupAttachController extends Controller
{
    private function assertServiceOwner(Request $request, Service $service): void
    {
        abort_unless($service->user_id === $request->user()->id, 404);
    }

    public function index(Request $request, Service $service)
    {
        $this->assertServiceOwner($request, $service);

        return response()->json([
            'groups' => $service->optionGroups()->with('options')->get(),
        ]);
    }

    public function attach(Request $request, Service $service)
    {
        $this->assertServiceOwner($request, $service);

        $data = $request->validate([
            'group_id' => [
                'required',
                Rule::exists('service_option_groups', 'id')->where(fn($q) => $q->where('user_id', $request->user()->id)),
            ],
            'position' => ['sometimes', 'integer', 'min:0'],
        ]);

        $service->optionGroups()->syncWithoutDetaching([
            $data['group_id'] => ['position' => $data['position'] ?? 0],
        ]);

        return response()->json(['ok' => true]);
    }

    public function detach(Request $request, Service $service)
    {
        $this->assertServiceOwner($request, $service);

        $data = $request->validate([
            'group_id' => [
                'required',
                Rule::exists('service_option_groups', 'id')->where(fn($q) => $q->where('user_id', $request->user()->id)),
            ],
        ]);

        $service->optionGroups()->detach($data['group_id']);

        return response()->json(['ok' => true]);
    }

    public function reorder(Request $request, Service $service)
    {
        $this->assertServiceOwner($request, $service);

        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.group_id' => [
                'required',
                Rule::exists('service_option_groups', 'id')->where(fn($q) => $q->where('user_id', $request->user()->id)),
            ],
            'items.*.position' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($data['items'] as $it) {
            $service->optionGroups()->updateExistingPivot($it['group_id'], [
                'position' => $it['position'],
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
