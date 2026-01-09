<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function store(Request $request)
    {
        // Logic to store image
        $request->validate([
            'file' => ['required', 'file', 'image', 'max:4096'], // 4MB
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
            'url' => env('FTP_BASE_URL') . '/' . $image->path,
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
