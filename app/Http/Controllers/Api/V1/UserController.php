<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(User::all());
    }

    /**
     * Display the resource by id in the URL.
     */
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    /**
     * Display the resource by id derived from the API token used.
     */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Return the resource's (identified by id in the URL) associated image.
     */
    public function image(User $user): StreamedResponse
    {
        foreach (User::IMAGE_EXTENSIONS as $ext) {
            $path = "user_images/{$user->id}.{$ext}";
            if (Storage::exists($path)) return Storage::response($path);
        }

        abort(404, 'Image not found');
    }

    /**
     * Return the resource's (by id derived from the API token used) associated image.
     */
    public function imageMy(Request $request): StreamedResponse
    {
        return $this->image($request->user());
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateMe(UpdateUserRequest $request): UserResource
    {
        $user = $request->user();

        // Update name if present
        if ($request->filled('name')) {
            $user->name = $request->input('name');
        }

        // Update image if present
        if ($request->hasFile('image')) {

            // Delete existing images if present
            foreach (User::IMAGE_EXTENSIONS as $ext) {
                $path = "user_images/{$user->id}.{$ext}";
                if (Storage::exists($path)) Storage::delete($path);
            }

            // Save new image
            $image = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $filename = $user->id . '.' . $extension;
            Storage::putFileAs('user_images', $image, $filename);
        }

        $user->save();

        return new UserResource($user);
    }
}
