<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Http\Requests\HospitalProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * A hospital edits its own profile. There is no hospital id in the URL:
 * it is always the signed-in user's hospital, and the policy double-checks.
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $hospital = $request->user()->hospital;

        Gate::authorize('update', $hospital);

        return view('hospital.profile', ['hospital' => $hospital]);
    }

    public function update(HospitalProfileRequest $request): RedirectResponse
    {
        $hospital = $request->user()->hospital;

        Gate::authorize('update', $hospital);

        $hospital->update($request->hospitalAttributes());

        return redirect()->route('hospital.profile.edit')->with('success', 'Hospital profile saved.');
    }
}
