<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'            => 'required|string',
            'last_name'       => 'required|string',
            'email'           => 'required|email|unique:users',
            'password'        => 'required|string',
            'phone'           => 'string',
            'profile_picture' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = array_merge(
            $request->only('name', 'last_name', 'email', 'role', 'phone'),
            ['password' => Hash::make($request->password)]
        );

        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $name  = $request->name . '_' . $image->getClientOriginalName() . '.png';
            $data['profile_picture'] = $image->storeAs('assets/images/users', $name, 'public');
        }

        if (User::create($data)) {
            return response()->json(['message' => 'Usuario registrado'], 201);
        }

        return response()->json(['message' => 'Error ao registrar usuário'], 500);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Dados Incorretos'], 401);
        }

        $token = $user->createToken('access-token')->plainTextToken;

        return response()->json(['token' => $token], 200);
    }

    public function show(User $user)
    {
        $user = User::findOrFail($user->id);
        return response()->json($user);
    }

    public function listUsers()
    {
        return response()->json(User::all());
    }

    public function updateMe(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name'          => 'sometimes|string',
            'last_name'     => 'sometimes|string',
            'email'         => 'sometimes|email|unique:users,email,' . $user->id,
            'phone'         => 'sometimes|nullable|string',
            'bio'           => 'sometimes|nullable|string',
            'twitter'       => 'sometimes|nullable|string',
            'instagram'     => 'sometimes|nullable|string',
            'facebook'      => 'sometimes|nullable|string',
            'share_socials' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user->update($request->only([
            'name',
            'last_name',
            'email',
            'phone',
            'bio',
            'twitter',
            'instagram',
            'facebook',
            'share_socials',
        ]));

        return response()->json(['message' => 'Usuário atualizado'], 200);
    }

    public function updatePhoto(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $image = $request->file('profile_picture');
        $name  = $user->name . '_' . time() . '.' . $image->getClientOriginalExtension();
        $path  = $image->storeAs('assets/images/users', $name, 'public');

        $user->update(['profile_picture' => $path]);

        return response()->json([
            'message'         => 'Foto atualizada',
            'profile_picture' => Storage::url($path),
        ], 200);
    }

    public function updateBanner(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($user->banner && Storage::disk('public')->exists($user->banner)) {
            Storage::disk('public')->delete($user->banner);
        }

        $image = $request->file('banner');
        $path  = $image->storeAs(
            'assets/images/banners',
            $user->name . '_' . time() . '.' . $image->getClientOriginalExtension(),
            'public'
        );

        $user->update(['banner' => $path]);

        return response()->json([
            'message' => 'Banner atualizado',
            'banner'  => Storage::url($path),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout realizado'], 200);
    }

    public function myProperties(Request $request)
    {
        $properties = Property::where('user_id', $request->user()->id)->get();
        return response()->json($properties);
    }
}
