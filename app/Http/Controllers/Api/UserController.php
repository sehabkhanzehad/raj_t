<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Api\CurrentUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return CurrentUserResource::collection(User::paginate($request->get('per_page', 50)));
    }

    public function show(Request $request): CurrentUserResource
    {
        return CurrentUserResource::make($request->user());
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $user = $request->user();

        if ($request->has('avatar')) {
            $user->deleteAvatar();
            
            $user->avatar = $request->hasFile('avatar')
                ? $request->file('avatar')->store('avatars')
                : null;
        }

        // Update user fields
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->save();

        return $this->success("Profile updated successfully", 200, ['user' => new CurrentUserResource($user)]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            throw $this->validationException('current_password', 'The current password is incorrect.');
        }

        $user->update([
            "password" => $request->password,
        ]);

        return $this->success("Password changed successfully");
    }
}
