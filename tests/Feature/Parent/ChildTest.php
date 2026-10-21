<?php

use App\Models\Child;
use App\Models\Hospital;
use App\Models\User;
use App\Models\VaccineSchedule;
use Database\Seeders\EpiScheduleSeeder;

beforeEach(function () {
    $this->seed(EpiScheduleSeeder::class);
    $this->parent = User::factory()->parent()->create();
});

function childPayload(array $overrides = []): array
{
    return [
        'first_name' => 'Sara',
        'last_name' => 'Example',
        'date_of_birth' => now()->subDays(50)->toDateString(),
        'gender' => 'female',
        'blood_group' => 'O+',
        'medical_notes' => '',
        ...$overrides,
    ];
}

test('a parent adds a child and gets the full schedule', function () {
    $response = $this->actingAs($this->parent)->post(route('parent.children.store'), childPayload());

    $child = Child::sole();
    $response->assertRedirect(route('parent.children.show', $child));

    expect($child->parent_id)->toBe($this->parent->id)
        ->and($child->doses()->count())->toBe(VaccineSchedule::count());

    $this->get(route('parent.children.show', $child))
        ->assertOk()
        ->assertSee('Sara Example')
        ->assertSee('Due now')       // 6-week doses at 50 days
        ->assertSee('Upcoming');
});

test('a child is always added to the signed-in parent', function () {
    $other = User::factory()->parent()->create();

    $this->actingAs($this->parent)->post(route('parent.children.store'), childPayload(['parent_id' => $other->id]));

    expect(Child::sole()->parent_id)->toBe($this->parent->id);
});

// Overrides are closures so dates are computed inside the test, in the app's
// timezone, not when the file loads (which would use UTC).
test('child details are validated', function (Closure $overrides, string $field) {
    $this->actingAs($this->parent)
        ->post(route('parent.children.store'), childPayload($overrides()))
        ->assertSessionHasErrors($field);

    expect(Child::count())->toBe(0);
})->with([
    'future birth' => [fn () => ['date_of_birth' => now()->addDay()->toDateString()], 'date_of_birth'],
    'too old' => [fn () => ['date_of_birth' => now()->subYears(13)->toDateString()], 'date_of_birth'],
    'unknown gender' => [fn () => ['gender' => 'unknown'], 'gender'],
    'bad blood group' => [fn () => ['blood_group' => 'Z+'], 'blood_group'],
    'missing name' => [fn () => ['first_name' => ''], 'first_name'],
]);

test('correcting the date of birth moves the schedule', function () {
    $this->actingAs($this->parent)->post(route('parent.children.store'), childPayload(['date_of_birth' => '2026-01-01']));
    $child = Child::sole();

    $this->put(route('parent.children.update', $child), childPayload(['date_of_birth' => '2026-01-11']))
        ->assertRedirect(route('parent.children.show', $child));

    $bcg = $child->doses()->whereHas('scheduleDose', fn ($q) => $q->where('label', 'BCG'))->sole();
    expect($bcg->due_date->toDateString())->toBe('2026-01-11');
});

test('the date of birth cannot move after a dose that was given', function () {
    $this->actingAs($this->parent)->post(route('parent.children.store'), childPayload(['date_of_birth' => '2026-01-01']));
    $child = Child::sole();
    $dose = $child->doses()->first();
    $dose->administered_at = '2026-01-05';
    $dose->save();

    $this->put(route('parent.children.update', $child), childPayload(['date_of_birth' => '2026-01-10']))
        ->assertSessionHasErrors('date_of_birth');

    expect($child->fresh()->date_of_birth->toDateString())->toBe('2026-01-01');
});

test('a parent can remove their child (soft delete)', function () {
    $child = Child::factory()->for($this->parent, 'parent')->create();

    $this->actingAs($this->parent)->delete(route('parent.children.destroy', $child))
        ->assertRedirect(route('parent.children.index'));

    $this->assertSoftDeleted($child);
});

test("a parent cannot view, edit or delete another parent's child", function (string $method, string $route) {
    $theirs = Child::factory()->create(); // belongs to another parent

    $this->actingAs($this->parent)
        ->call($method, route($route, $theirs), childPayload())
        ->assertForbidden();

    expect($theirs->fresh())->not->toBeNull()
        ->and($theirs->fresh()->first_name)->not->toBe('Sara');
})->with([
    'view' => ['GET', 'parent.children.show'],
    'edit form' => ['GET', 'parent.children.edit'],
    'update' => ['PUT', 'parent.children.update'],
    'delete' => ['DELETE', 'parent.children.destroy'],
]);

test("another parent's children never appear in my list or dashboard", function () {
    Child::factory()->create(['first_name' => 'Stranger']);
    Child::factory()->for($this->parent, 'parent')->create(['first_name' => 'Mine']);

    $this->actingAs($this->parent)->get(route('parent.children.index'))->assertSee('Mine')->assertDontSee('Stranger');
    $this->actingAs($this->parent)->get(route('parent.dashboard'))->assertSee('Mine')->assertDontSee('Stranger');
});

test('hospitals and admins cannot use the parent children pages', function (string $role) {
    $user = $role === 'admin' ? User::factory()->admin()->create() : Hospital::factory()->active()->create()->user;

    $this->actingAs($user)->get(route('parent.children.index'))->assertForbidden();
    $this->actingAs($user)->post(route('parent.children.store'), childPayload())->assertForbidden();

    expect(Child::count())->toBe(0);
})->with(['admin', 'hospital']);

test('the parent dashboard counts due and overdue doses', function () {
    $this->actingAs($this->parent)->post(route('parent.children.store'), childPayload(['date_of_birth' => now()->subDays(84)->toDateString()]));

    // At 12 weeks with nothing given: birth doses missed/overdue and 6-week doses overdue.
    $response = $this->get(route('parent.dashboard'))->assertOk();

    expect($response->viewData('overdue'))->toBeGreaterThan(0);
    $response->assertSee('overdue');
});
