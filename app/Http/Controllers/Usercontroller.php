<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // LIST USERS
    public function index()
    {
        return response()->json(User::all());
    }

    // CREATE USER
    public function store(Request $request)
    {
        $request->validate([
            'NIK' => 'required|unique:users,NIK',
            'Name' => 'required',
            'Division' => 'required',
            'Team' => 'required',
            'Job_Function_KPI' => 'required',
            'status' => 'required',
            'region' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required'
        ]);

        $user = User::create([
            'NIK' => $request->NIK,
            'Name' => $request->Name,
            'Division' => $request->Division,
            'Team' => $request->Team,
            'Job_Function_KPI' => $request->Job_Function_KPI,
            'status' => $request->status,
            'region' => $request->region,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json($user, 201);
    }

    // SHOW USER
    public function show(User $user)
    {
        return response()->json($user);
    }

    // UPDATE USER
    public function update(Request $request, User $user)
    {
        $request->validate([
            'email' => 'email|unique:users,email,' . $user->id,
        ]);

        $data = $request->all();

        if ($request->has('password') && $request->password) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json($user);
    }

    // DELETE USER
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
}
