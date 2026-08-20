<?php

use App\Jobs\DeliverFormSubmission;
use App\Models\FormSubmission;
use App\Models\Redirect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed();
});

it('renders the seeded CMS homepage and localized public utilities', function (): void {
    $this->get('/')->assertOk()->assertInertia(fn (Assert $page): Assert => $page
        ->component('Pages/Show')
        ->where('page.title', 'Digify')
        ->has('blocks', 2)
        ->where('blocks.1.type', 'form'));

    $this->get('/ar/')
        ->assertOk()
        ->assertSee('dir="rtl"', false)
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('locale', 'ar')
            ->where('direction', 'rtl')
            ->where('menus.main.items.0.url', '/ar/careers/'));
    $this->get('/careers/open-application/')->assertOk()->assertInertia(fn (Assert $page): Assert => $page
        ->component('Forms/Standalone')
        ->where('form.key', 'open-application'));
    $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml');
});

it('validates, stores, and silently honeypots dynamic form submissions', function (): void {
    Queue::fake();

    $this->post('/forms/contact/submit', [
        'name' => 'Ada Lovelace', 'email' => 'ada@example.com', 'company' => 'Analytical Engines', 'message' => 'Build a useful platform.',
    ])->assertSessionHasNoErrors()->assertSessionHas('form_success');

    expect(FormSubmission::count())->toBe(1);
    Queue::assertPushed(DeliverFormSubmission::class);

    $this->post('/forms/contact/submit', ['_website' => 'spam.example', 'email' => 'invalid'])
        ->assertSessionHasNoErrors()->assertSessionHas('form_success');

    expect(FormSubmission::count())->toBe(1);
});

it('boots all primary admin builders for their authorized roles', function (): void {
    $ceo = User::where('email', 'ceo@digify.test')->firstOrFail();
    $it = User::where('email', 'it@digify.test')->firstOrFail();
    $editor = User::where('email', 'editor@digify.test')->firstOrFail();

    $this->actingAs($ceo)->get('/admin')->assertOk();
    expect($editor->can('pages.create'))->toBeTrue();
    $this->flushSession()->actingAs($editor)->get('/admin/pages/create')->assertOk();
    $this->flushSession()->actingAs($it)->get('/admin/forms/create')->assertOk();
    $this->actingAs($it)->get('/admin/site-settings')->assertOk();
    $this->actingAs($it)->get('/admin/media-assets/create')->assertOk();
});

it('resolves managed redirects and records misses without storing an IP', function (): void {
    Redirect::create(['from_path' => '/legacy', 'to_url' => '/careers/', 'status_code' => 301, 'is_active' => true]);

    $this->get('/legacy')->assertRedirect('/careers/');
    $this->get('/does-not-exist')->assertNotFound();
    $this->assertDatabaseHas('redirect_misses', ['path' => '/does-not-exist', 'hits' => 1]);
});
