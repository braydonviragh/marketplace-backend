<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\CreateSuperAdminRequest;
use App\Http\Requests\Auth\UpdateSuperAdminRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class SuperAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:super_admin']);
    }

    public function index()
    {
        $superAdmins = User::where('role', 'super_admin')
            ->latest()
            ->paginate(15);

        return $this->successResponse([
            'super_admins' => $superAdmins,
            'pagination' => [
                'total' => $superAdmins->total(),
                'per_page' => $superAdmins->perPage(),
                'current_page' => $superAdmins->currentPage(),
                'last_page' => $superAdmins->lastPage(),
            ]
        ], 'Super admins retrieved successfully');
    }

    public function store(CreateSuperAdminRequest $request)
    {
        $user = User::create([
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(10),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        event(new Registered($user));

        return $this->successResponse([
            'user' => $user,
        ], 'Super admin created successfully');
    }

    public function update(UpdateSuperAdminRequest $request, User $superAdmin)
    {
        if ($superAdmin->role !== 'super_admin') {
            return $this->errorResponse('User is not a super admin', 'INVALID_ROLE', 422);
        }

        $superAdmin->update($request->validated());

        return $this->successResponse([
            'user' => $superAdmin->fresh()
        ], 'Super admin updated successfully');
    }

    public function deactivate(User $superAdmin)
    {
        if ($superAdmin->role !== 'super_admin') {
            return $this->errorResponse('User is not a super admin', 'INVALID_ROLE', 422);
        }

        // Prevent self-deactivation
        if ($superAdmin->id === Auth::id()) {
            return $this->errorResponse('Cannot deactivate yourself', 'SELF_DEACTIVATION', 422);
        }

        $superAdmin->update([
            'is_active' => false,
            'deactivated_at' => now()
        ]);

        // Revoke all tokens
        $superAdmin->tokens()->delete();

        return $this->successResponse([
            'user' => $superAdmin->fresh()
        ], 'Super admin deactivated successfully');
    }

    public function reactivate(User $superAdmin)
    {
        if ($superAdmin->role !== 'super_admin') {
            return $this->errorResponse('User is not a super admin', 'INVALID_ROLE', 422);
        }

        $superAdmin->update([
            'is_active' => true,
            'deactivated_at' => null
        ]);

        return $this->successResponse([
            'user' => $superAdmin->fresh()
        ], 'Super admin reactivated successfully');
    }
} 