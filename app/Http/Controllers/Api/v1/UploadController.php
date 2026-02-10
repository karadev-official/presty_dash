<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'image', 'max:4096'], // 4MB
        ]);

        $userId = $request->user()->id;

        $ext = $request->file('file')->getClientOriginalExtension() ?: 'jpg';
        $name = Str::uuid()->toString() . '.' . $ext;

        $path = $request->file('file')->storeAs("public/users/{$userId}/options", $name);

        $publicPath = Str::replaceFirst('public/', '', $path);
        $url = Storage::url($publicPath); // /storage/users/{id}/options/xxx.jpg

        return response()->json([
            'url' => $url,
        ]);
    }
}
