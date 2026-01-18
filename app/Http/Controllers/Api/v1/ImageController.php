<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class ImageController extends Controller
{

    #[OA\Post(
        path: "/api/v1/images",
        summary: "Upload an image",
        tags: ["Images"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "file",
                            type: "string",
                            format: "binary",
                            description: "The image file to upload"
                        ),
                        new OA\Property(
                            property: "service_id",
                            type: "integer",
                            description: "Optional service ID to associate the image with"
                        ),
                    ],
                    required: ["file"]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Image uploaded successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(property: "image", type: "object"),
                        new OA\Property(property: "url", type: "string"),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Bad Request"
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized"
            ),
        ]
    )]
    public function store(Request $request)
    {
        // Logic to store image
        $request->validate([
            'file' => ['required', 'file', 'mimes:jpeg,png,jpg,webp,gif,heic,svg', 'max:4096'], // 4MB
            'service_id' => ['nullable', 'exists:services,id'], // Service IDs to associate with
        ]);

        // Stockage de l'image
        $file = $request->file('file');
        $path = $file->store('images', ['disk' => 'ftp']);

        Log::info(['path' => $path]);

        $name = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $userId = $request->user()->id;
        $url = env('FTP_BASE_URL') . '/' . $path;
        $image = Image::create([
            'path' => $path,
            'name' => $name,
            'mime_type' => $mimeType,
            'user_id' => $userId,
        ]);
        if ($request->has('service_id')) {
            $image->services()->attach($request->input('service_id'));
        }
        return response()->json([
            'message' => 'Image enregistrée avec succès',
            'image' => $image,
            'url' => $url,
        ], 201);
    }

    public function show(Image $image)
    {
        return response()->json([
            'image' => $this->imagePayload($image),
        ]);
    }

    public function findImageByUrl(Request $request)
    {
        $request->validate([
            'url' => ['required', 'string', 'max:2048'],
        ]);

        $baseUrl = env('FTP_BASE_URL') . '/';
        $path = $request->input('url');
        if (str_starts_with($path, $baseUrl)) {
            $path = substr($path, strlen($baseUrl));
        }

        $image = Image::where('path', $path)->first();
        if (!$image) {
            return response()->json(['image' => null,]);
        }

        return response()->json([
            'image' => $this->imagePayload($image),
        ]);
    }

    public function firstOrCreate(Request $request)
    {
        $request->validate([
            'path' => ['required', 'string', 'max:2048'],
            'name' => ['required', 'string', 'max:255'],
            'mime_type' => ['required', 'string', 'max:100'],
            'file' => ['required', 'file', 'image', 'max:4096'], // 4MB
            'service_id' => ['nullable', 'exists:services,id'], // Service IDs to associate with
        ]);

        // retirer l'url de base si présent
        $baseUrl = env('FTP_BASE_URL') . '/';
        if (str_starts_with($request->input('path'), $baseUrl)) {
            $request->merge([
                'path' => substr($request->input('path'), strlen($baseUrl)),
            ]);
        }
        $image = Image::where('path', $request->input('path'))->first();
        if (!$image) {
            // Stockage de l'image
            $file = $request->file('file');
            $path = $file->store('images', ['disk' => 'ftp']);
            $image = Image::create([
                'path' => $path,
                'name' => $request->input('name'),
                'mime_type' => $request->input('mime_type'),
                'user_id' => $request->user()->id,
            ]);
            if ($request->has('service_id')) {
                $image->services()->attach($request->input('service_id'));
            }
        }

        return response()->json([
            'image' => $this->imagePayload($image),
        ]);
    }

    public function showMultiple(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:images,id'],
        ]);

        $images = Image::whereIn('id', $request->input('ids'))->get();

        return response()->json([
            'images' => $images->map(function ($image) {
                return $this->imagePayload($image);
            }),
        ]);
    }

    public function destroy(Image $image)
    {
        Storage::disk('ftp')->delete($image->path);
        $image->services()->detach();
        $image->delete();

        return response()->json([
            'message' => 'Image supprimée avec succès',
        ]);
    }

    private function imagePayload(Image $image)
    {
        return [
            'id' => $image->id,
            'name' => $image->name,
            'mime_type' => $image->mime_type,
            'url' => $image->url,
            'created_at' => $image->created_at,
            'updated_at' => $image->updated_at,
            'services' => $image->services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                ];
            }),
        ];
    }
}
