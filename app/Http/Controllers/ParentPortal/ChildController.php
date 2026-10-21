<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChildRequest;
use App\Models\Child;
use App\Services\ScheduleGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ChildController extends Controller
{
    public function __construct(private readonly ScheduleGenerator $generator) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Child::class);

        $children = $request->user()->children()
            ->with('doses.scheduleDose.vaccine', 'doses.hospitalStock.hospital')
            ->orderBy('date_of_birth', 'desc')
            ->get();

        return view('parent.children.index', ['children' => $children]);
    }

    public function create(): View
    {
        Gate::authorize('create', Child::class);

        return view('parent.children.create', ['child' => new Child]);
    }

    public function store(ChildRequest $request): RedirectResponse
    {
        Gate::authorize('create', Child::class);

        $child = DB::transaction(function () use ($request) {
            $child = new Child($request->validated());
            $child->parent_id = $request->user()->id;
            $child->save();

            $this->generator->generate($child);

            return $child;
        });

        return redirect()->route('parent.children.show', $child)
            ->with('success', "{$child->first_name} has been added. Here is their vaccination schedule.");
    }

    public function show(Child $child): View
    {
        Gate::authorize('view', $child);

        return view('parent.children.show', [
            'child' => $child,
            'schedule' => $child->schedule(),
        ]);
    }

    public function edit(Child $child): View
    {
        Gate::authorize('update', $child);

        return view('parent.children.edit', ['child' => $child]);
    }

    public function update(ChildRequest $request, Child $child): RedirectResponse
    {
        Gate::authorize('update', $child);

        DB::transaction(function () use ($request, $child) {
            $child->fill($request->validated());
            $dobChanged = $child->isDirty('date_of_birth');
            $child->save();

            if ($dobChanged) {
                $this->generator->recalculateDueDates($child);
            }
        });

        return redirect()->route('parent.children.show', $child)
            ->with('success', "{$child->first_name}'s details have been saved.");
    }

    public function destroy(Child $child): RedirectResponse
    {
        Gate::authorize('delete', $child);

        $child->delete();

        return redirect()->route('parent.children.index')
            ->with('success', "{$child->first_name} has been removed from your account.");
    }
}
