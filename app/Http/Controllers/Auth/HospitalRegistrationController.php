<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\HospitalRegistrationRequest;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class HospitalRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register-hospital');
    }

    /**
     * Create the hospital's login and a pending hospital profile.
     * The hospital is not logged in: it can sign in only after an admin approves it.
     */
    public function store(HospitalRegistrationRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = new User([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
            ]);
            $user->role = Role::Hospital;
            $user->save();

            $user->hospital()->save(new Hospital($request->hospitalAttributes()));

            return $user;
        });

        event(new Registered($user));

        return redirect()->route('register.hospital.pending');
    }

    public function pending(): View
    {
        return view('auth.hospital-pending');
    }
}
