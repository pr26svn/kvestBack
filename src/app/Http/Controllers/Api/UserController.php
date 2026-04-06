<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Только администраторы могут просматривать пользователей
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $users = User::with('roles')->get();

        return response()->json(['data' => $users]);
    }

    public function store(Request $request): JsonResponse
    {
        // Только администраторы могут создавать пользователей
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:user,admin,moderator,expert',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return response()->json(['data' => $user], 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        // Только администраторы могут просматривать детали пользователей
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return response()->json(['data' => $user->load('roles')]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        // Только администраторы могут обновлять пользователей
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $user->id,
            'role' => 'string|in:user,admin,moderator,expert',
        ]);

        $user->update($validated);

        return response()->json(['data' => $user->load('roles')]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        // Только администраторы могут удалять пользователей
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        // Нельзя удалить самого себя
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot delete yourself'], 400);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
