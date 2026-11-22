<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * The only place, besides the seeder, where admin accounts are created.
 */
class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manageAdmins', User::class);

        $search = $request->validate(['search' => ['nullable', 'string', 'max:100']])['search'] ?? null;

        $admins = User::query()
            ->where('role', Role::Admin)
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.admins.index', ['admins' => $admins, 'search' => $search]);
    }

    public function create(): View
    {
        Gate::authorize('manageAdmins', User::class);

        return view('admin.admins.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manageAdmins', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $admin = new User([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $admin->role = Role::Admin;
        $admin->email_verified_at = now();
        $admin->save();

        return redirect()->route('admin.admins.index')
            ->with('success', "Administrator account created for {$admin->name}.");
    }
}
