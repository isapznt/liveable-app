<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    // GET /api/admin/stats
    public function stats()
    {
        return response()->json([
            'total_users'      => User::count(),
            'total_properties' => Property::count(),
            'total_reviews'    => Review::count(),
            'total_admins'     => User::where('role', 'admin')->count(),
            'new_users_month'  => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'new_props_month'  => Property::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ]);
    }

    // GET /api/admin/users
    public function listUsers()
    {
        $users = User::select('id', 'name', 'last_name', 'email', 'role', 'created_at')
            ->latest()
            ->get();

        return response()->json($users);
    }

    // POST /api/admin/create-admin
    public function createAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string',
            'last_name' => 'required|string',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name'      => $request->name,
            'last_name' => $request->last_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'admin',
        ]);

        return response()->json([
            'message' => 'Admin criado com sucesso!',
            'user'    => $user->only('id', 'name', 'last_name', 'email', 'role'),
        ], 201);
    }

    // PATCH /api/admin/users/{user}/role
    public function changeRole(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:user,admin',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user->update(['role' => $request->role]);

        return response()->json(['message' => 'Role atualizado.', 'role' => $user->role]);
    }
}
