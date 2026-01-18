<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Resource;
use Illuminate\Http\Request;

use OpenApi\Attributes as OA;

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
            'resources' => $resources->map(function (Resource $resource) {
                return $this->resourcePayload($resource);
            }),
        ]);
    }

    public function show(Request $request, Resource $resource)
    {
        $user = $request->user();
        abort_unless($user->hasRole('pro'), 403);
        abort_unless($resource->pro_user_id === $user->id, 404);

        return response()->json([
            'resource' => $this->resourcePayload($resource),
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
            'resource' => $this->resourcePayload($resource),
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
            'resource' => $this->resourcePayload($resource),
        ]);
    }

    #[OA\Put(
        path: "/api/v1/resources/{resource}/image",
        summary: "Met à jour l'image associée à une ressource",
        tags: ["Pro - Ressources"],
        parameters: [
            new OA\Parameter(
                name: "resource",
                in: "path",
                description: "ID de la ressource",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                "application/json" => new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(
                                property: "image_id",
                                type: "integer",
                                description: "ID de l'image à associer à la ressource. Mettre à null pour dissocier l'image."
                            ),
                        ]
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Image de la ressource mise à jour avec succès",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(
                                property: "resource",
                                type: "object",
                                description: "La ressource mise à jour",
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "name", type: "string"),
                                    new OA\Property(property: "specialty", type: "string"),
                                    new OA\Property(property: "type", type: "string"),
                                    new OA\Property(property: "is_default", type: "boolean"),
                                    new OA\Property(property: "is_active", type: "boolean"),
                                    new OA\Property(property: "resource_image_id", type: "integer"),
                                    new OA\Property(property: "resource_image_url", type: "string"),
                                ]
                            ),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 403,
                description: "Forbidden - L'utilisateur n'a pas les droits nécessaires"
            ),
            new OA\Response(
                response: 404,
                description: "Not Found - La ressource ou l'image n'existe pas"
            ),
        ]
    )]
    public function updateImage(Request $request, Resource $resource)
    {
        $request->validate([
            'image_id' => ['nullable', 'exists:images,id'],
        ]);

        $user = $request->user();

        // ✅ sécurité: la ressource doit appartenir à ce pro
        abort_unless($resource->pro_user_id === $user->id, 403);

        if ($request->image_id === null) {
            $resource->update(['resource_image_id' => null]);
            return response()->json([
                'resource' => $this->resourcePayload($resource->fresh('resourceImage')),
            ]);
        }

        // ✅ sécurité: l'image doit appartenir au pro
        $image = Image::where('id', $request->image_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $resource->update([
            'resource_image_id' => $image->id,
        ]);

        return response()->json([
            'resource' => $this->resourcePayload($resource->fresh('resourceImage')),
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

    public function resourcePayload(Resource $resource)
    {
        return [
            'id' => $resource->id,
            'name' => $resource->name,
            'specialty' => $resource->specialty,
            'type' => $resource->type,
            'is_default' => $resource->is_default,
            'is_active' => $resource->is_active,
            'resource_image_id' => $resource->resource_image_id,
            'resource_image_url' => $resource->resource_image_url,
        ];
    }
}
