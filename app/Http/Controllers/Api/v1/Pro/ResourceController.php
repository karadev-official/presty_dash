<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{

    public function resourceTypes(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasRole('pro'), 403);
        return response()->json([
            'types' => Resource::types(),
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasRole('pro'), 403);

        $resources = Resource::where('pro_user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return response()->json([
            'resources' => $resources,
        ]);
    }

    public function show(Request $request, Resource $resource)
    {
        $user = $request->user();
        abort_unless($user->hasRole('pro'), 403);
        abort_unless($resource->pro_user_id === $user->id, 404);

        return response()->json([
            'resource' => $resource,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasRole('pro'), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'type' => 'required|string|in:' . implode(',', Resource::types()),
        ]);

        $resource = Resource::create([
            'pro_user_id' => $user->id,
            'name' => $data['name'],
            'specialty' => $data['specialty'] ?? null,
            'type' => $data['type'],
            'is_default' => false,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Ressource créée avec succès.',
            'resource' => $resource,
        ], 201);
    }

    public function update(Request $request, Resource $resource)
    {
        $user = $request->user();
        abort_unless($user->hasRole('pro'), 403);
        abort_unless($resource->pro_user_id === $user->id, 404);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'specialty' => 'sometimes|nullable|string|max:255',
            'type' => 'sometimes|required|string|in:' . implode(',', Resource::types()),
            'is_active' => 'sometimes|boolean',
        ]);

        if ($resource->is_default) {
            if (isset($data['type']) && $data['type'] !== Resource::TYPE_SELF) {
                abort(422, "Impossible de changer le type de la ressource par défaut.");
            }
            if (isset($data['is_active']) && $data['is_active'] === false) {
                abort(422, "Impossible de désactiver la ressource par défaut.");
            }
        }
        unset($data['is_default']);

        $resource->update($data);

        return response()->json([
            'message' => 'Ressource mise à jour avec succès.',
            'resource' => $resource,
        ]);
    }

    public function destroy(Request $request, Resource $resource)
    {
        $user = $request->user();
        abort_unless($user->hasRole('pro'), 403);
        abort_unless($resource->pro_user_id === $user->id, 403);

        if ($resource->is_default) {
            abort(422, "Impossible de supprimer la ressource par défaut.");
        }

        $resource->delete();

        return response()->json([
            'message' => 'Ressource supprimée avec succès.',
        ]);
    }
}
