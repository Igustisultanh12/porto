<?php

namespace Tests\Feature;

use App\Jobs\NotifyContact;
use App\Models\ContactMessage;
use App\Models\Content;
use App\Models\Media;
use App\Models\Taxonomy;
use App\Models\User;
use App\Policies\ContentPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['inertia.ssr.enabled' => false]);
        $this->withoutVite();
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_malformed_login_and_slug_inputs_return_validation_errors(): void
    {
        $this->post('/admin/login', ['email' => ['invalid'], 'password' => 'test'])->assertSessionHasErrors('email');
        $this->actingAs($this->admin())->post('/admin/contents', $this->payload(['slug' => ['invalid']]))->assertSessionHasErrors('slug');
        $this->assertDatabaseCount('contents', 0);
    }

    public function test_public_cards_do_not_send_full_bodies_or_private_attachment_metadata(): void
    {
        $content = Content::factory()->published()->create(['body' => 'FULL_BODY_SENTINEL', 'featured' => true]);
        Media::factory()->create(['content_id' => $content->id]);

        $this->get('/karya/project')->assertOk()->assertInertia(fn (Assert $page) => $page->missing('items.data.0.body')->has('items.data.0.media', 0));
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->missing('featured.0.body')->has('featured.0.media', 0));
    }

    private function payload(array $overrides = []): array
    {
        return array_replace(['type' => 'project', 'title' => 'Proyek baru', 'slug' => 'proyek-baru', 'summary' => 'Ringkasan', 'body' => '## Isi', 'status' => 'draft', 'featured' => false, 'sort_order' => 0, 'metadata' => [], 'taxonomy_ids' => []], $overrides);
    }

    private function contact(array $overrides = []): array
    {
        return array_replace(['submission_id' => (string) Str::uuid(), 'name' => 'Pengirim', 'email' => 'sender@example.test', 'subject' => 'Diskusi riset', 'message' => 'Saya ingin mendiskusikan penelitian.', 'website' => ''], $overrides);
    }

    public function test_guest_cannot_view_or_mutate_admin_content(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->post('/admin/contents', $this->payload())->assertRedirect('/admin/login');
        $this->assertDatabaseCount('contents', 0);
    }

    public function test_non_admin_is_forbidden_and_cannot_escalate_through_payload(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put('/admin/settings', ['name' => 'Changed', 'is_admin' => true])->assertForbidden();
        $this->assertFalse($user->fresh()->is_admin);
        $this->assertDatabaseCount('settings', 0);
    }

    public function test_policy_distinguishes_public_draft_and_admin_permissions(): void
    {
        $policy = new ContentPolicy;
        $draft = Content::factory()->create();
        $public = Content::factory()->published()->create();
        $admin = $this->admin();
        $user = User::factory()->create();
        $this->assertFalse($policy->view(null, $draft));
        $this->assertTrue($policy->view(null, $public));
        $this->assertTrue($policy->view($admin, $draft));
        $this->assertFalse($policy->update($user, $draft));
        $this->assertFalse($policy->delete($user, $draft));
        $this->assertTrue($policy->create($admin));
    }

    public function test_admin_can_login_and_logout(): void
    {
        $admin = $this->admin();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
        $this->post('/admin/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_wrong_password_and_public_registration_are_rejected(): void
    {
        $admin = $this->admin();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'incorrect'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/register', [])->assertNotFound();
    }

    #[TestWith(['project'])] #[TestWith(['research'])] #[TestWith(['article'])]
    public function test_content_can_be_created_updated_published_and_soft_deleted(string $type): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/contents', $this->payload(['type' => $type]))->assertSessionHasNoErrors()->assertRedirect();
        $content = Content::where('slug', 'proyek-baru')->firstOrFail();
        $this->put('/admin/contents/'.$content->id, $this->payload(['type' => $type, 'title' => 'Versi baru', 'status' => 'published']))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('contents', ['id' => $content->id, 'title' => 'Versi baru', 'status' => 'published']);
        $this->delete('/admin/contents/'.$content->id)->assertRedirect('/admin/contents');
        $this->assertSoftDeleted($content);
        $this->post('/admin/contents/bulk', ['ids' => [$content->id], 'action' => 'restore'])->assertRedirect();
        $this->assertDatabaseHas('contents', ['id' => $content->id, 'deleted_at' => null, 'status' => 'draft', 'published_at' => null]);
    }

    public function test_draft_cannot_leak_through_detail_search_sitemap_or_media(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('media/secret.pdf', 'private');
        $content = Content::factory()->create(['type' => 'research', 'slug' => 'secret-research', 'title' => 'Rahasia']);
        $media = Media::factory()->create(['content_id' => $content->id, 'path' => 'media/secret.pdf']);
        $this->get($content->url)->assertNotFound();
        $this->get('/media/'.$media->id)->assertNotFound();
        $this->get('/riset/secret-research/bibtex')->assertNotFound();
        $this->get('/karya/research?q=Rahasia')->assertInertia(fn (Assert $page) => $page->has('items.data', 0));
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('secret-research');
    }

    public function test_unpublish_immediately_revokes_public_downloads_and_sitemap(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('media/public.pdf', 'pdf');
        $content = Content::factory()->published()->create();
        $media = Media::factory()->create(['content_id' => $content->id, 'path' => 'media/public.pdf']);
        $this->get('/media/'.$media->id)->assertOk();
        $this->get('/sitemap.xml')->assertSee($content->slug);
        $this->actingAs($this->admin())->post('/admin/contents/bulk', ['ids' => [$content->id], 'action' => 'unpublish'])->assertRedirect();
        auth()->logout();
        $this->get('/media/'.$media->id)->assertNotFound();
        $this->get('/sitemap.xml')->assertDontSee($content->slug);
    }

    public function test_duplicate_slug_and_invalid_metadata_are_rejected(): void
    {
        Content::factory()->create(['slug' => 'proyek-baru']);
        $this->actingAs($this->admin())->post('/admin/contents', $this->payload(['metadata' => ['demo_url' => 'javascript:alert(1)', 'doi' => 'invalid']]))->assertSessionHasErrors(['slug', 'metadata.demo_url', 'metadata.doi']);
        $this->assertDatabaseCount('contents', 1);
    }

    public function test_invalid_required_fields_and_unknown_metadata_are_rejected(): void
    {
        $this->actingAs($this->admin())->post('/admin/contents', $this->payload(['title' => '', 'status' => 'secret', 'metadata' => ['admin' => true], 'taxonomy_ids' => [9999]]))->assertSessionHasErrors(['title', 'status', 'metadata', 'taxonomy_ids.0']);
        $this->assertDatabaseCount('contents', 0);
    }

    public function test_markdown_strips_scripts_and_unsafe_links(): void
    {
        $content = Content::factory()->published()->create(['body' => '<script>alert(1)</script>'."\n\n".'[bad](javascript:alert(1))'."\n\n".'**Safe text**']);
        $this->get($content->url)->assertInertia(fn (Assert $page) => $page->where('content.body_html', fn ($html) => ! str_contains($html, '<script') && ! str_contains($html, 'javascript:') && str_contains($html, '<strong>Safe text</strong>')));
    }

    public function test_filters_and_pagination_only_return_matching_public_research(): void
    {
        $tag = Taxonomy::factory()->create(['kind' => 'topic', 'name' => 'Komputasi']);
        Content::factory()->published()->count(10)->create(['type' => 'research', 'year' => 2026, 'publication_type' => 'journal', 'title' => 'Komputasi terbuka'])->each(fn ($c) => $c->taxonomies()->attach($tag));
        Content::factory()->published()->create(['type' => 'research', 'year' => 2025]);
        $this->get('/karya/research?q=Komputasi&year=2026&publication_type=journal&taxonomy='.$tag->id)->assertInertia(fn (Assert $page) => $page->where('items.total', 10)->has('items.data', 9)->where('items.last_page', 2));
    }

    public function test_research_citation_and_bibtex_preserve_author_order(): void
    {
        $content = Content::factory()->published()->create(['type' => 'research', 'title' => 'Riset A & B', 'year' => 2026, 'metadata' => ['authors' => [['name' => 'Budi', 'affiliation' => ''], ['name' => 'Ani', 'affiliation' => '']], 'venue' => 'Jurnal Uji', 'doi' => '10.1234/example']]);
        $this->get($content->url)->assertInertia(fn (Assert $page) => $page->where('citation', 'Budi, Ani. (2026). Riset A & B. Jurnal Uji. https://doi.org/10.1234/example'));
        $this->get('/riset/'.$content->slug.'/bibtex')->assertOk()->assertSee('author = {Budi and Ani}', false)->assertSee('Riset A \\& B', false);
    }

    public function test_settings_entries_and_taxonomies_are_editable(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings', ['name' => 'Nama pemilik', 'bio' => 'Profil baru', 'is_admin' => true])->assertSessionHasNoErrors();
        $this->get('/tentang')->assertInertia(fn (Assert $page) => $page->where('site.name', 'Nama pemilik')->missing('site.is_admin'));
        $this->post('/admin/entries', ['kind' => 'skill', 'title' => 'Laravel', 'sort_order' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('profile_entries', ['title' => 'Laravel']);
        $this->post('/admin/taxonomies', ['kind' => 'tag', 'name' => 'Web'])->assertSessionHasNoErrors();
        $taxonomy = Taxonomy::firstOrFail();
        $this->delete('/admin/taxonomies/'.$taxonomy->id)->assertRedirect();
        $this->assertModelMissing($taxonomy);
    }

    public function test_dangerous_uploads_and_oversized_files_are_rejected(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin());
        $this->post('/admin/media', ['slot' => 'avatar', 'file' => UploadedFile::fake()->createWithContent('attack.svg', '<svg onload="alert(1)"></svg>')])->assertSessionHasErrors('file');
        $this->post('/admin/media', ['slot' => 'cv', 'file' => UploadedFile::fake()->create('large.pdf', 10241, 'application/pdf')])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('media', 0);
    }

    public function test_image_is_optimized_and_draft_image_is_private(): void
    {
        Storage::fake('local');
        $content = Content::factory()->create();
        $this->actingAs($this->admin())->post('/admin/media', ['content_id' => $content->id, 'slot' => 'cover', 'file' => UploadedFile::fake()->image('photo.png', 2200, 1000)])->assertSessionHasNoErrors();
        $media = Media::firstOrFail();
        $this->assertSame('image/webp', $media->mime);
        Storage::disk('local')->assertExists($media->path);
        $size = getimagesize(Storage::disk('local')->path($media->path));
        $this->assertSame(1920, $size[0]);
        auth()->logout();
        $this->get('/media/'.$media->id)->assertNotFound();
    }

    public function test_contact_is_idempotent_and_queues_only_one_notification(): void
    {
        Queue::fake([NotifyContact::class]);
        config(['portfolio.notification_email' => 'owner@example.test']);
        $data = $this->contact();
        $this->post('/kontak', $data)->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->post('/kontak', $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('contact_messages', 1);
        Queue::assertPushed(NotifyContact::class, 1);
    }

    public function test_contact_survives_email_failure_and_remains_retryable(): void
    {
        config(['portfolio.notification_email' => 'owner@example.test', 'queue.default' => 'sync']);
        Mail::shouldReceive('raw')->once()->andThrow(new \RuntimeException('SMTP unavailable'));
        $this->post('/kontak', $this->contact())->assertSessionHasNoErrors()->assertSessionHas('success', 'Pesanmu sudah tersimpan. Terima kasih telah menghubungi.');
        $this->assertDatabaseHas('contact_messages', ['notified_at' => null, 'subject' => 'Diskusi riset']);
    }

    public function test_contact_honeypot_and_rate_limit(): void
    {
        $this->post('/kontak', $this->contact(['website' => 'https://spam.test']))->assertSessionHasErrors('website');
        $this->assertDatabaseCount('contact_messages', 0);
        for ($i = 0; $i < 4; $i++) {
            $this->post('/kontak', $this->contact())->assertRedirect();
        }
        $this->post('/kontak', $this->contact())->assertTooManyRequests();
        $this->assertDatabaseCount('contact_messages', 4);
    }

    public function test_admin_can_read_archive_and_delete_messages(): void
    {
        $message = ContactMessage::factory()->create();
        $this->actingAs($this->admin());
        $this->patch('/admin/messages/'.$message->id, ['action' => 'read'])->assertRedirect();
        $this->assertNotNull($message->fresh()->read_at);
        $this->patch('/admin/messages/'.$message->id, ['action' => 'archive'])->assertRedirect();
        $this->assertNotNull($message->fresh()->archived_at);
        $this->patch('/admin/messages/'.$message->id, ['action' => 'delete'])->assertRedirect();
        $this->assertModelMissing($message);
    }

    public function test_password_change_requires_current_password(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put('/admin/password', ['current_password' => 'wrong', 'password' => 'NewSecurePass123', 'password_confirmation' => 'NewSecurePass123'])->assertSessionHasErrors('current_password');
        $this->put('/admin/password', ['current_password' => 'password', 'password' => 'NewSecurePass123', 'password_confirmation' => 'NewSecurePass123'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('NewSecurePass123', $admin->fresh()->password));
    }

    public function test_password_reset_is_disabled_by_default_and_works_when_enabled(): void
    {
        $this->get('/admin/forgot-password')->assertNotFound();
        config(['portfolio.password_reset_enabled' => true]);
        Notification::fake();
        $admin = $this->admin();
        $this->post('/admin/forgot-password', ['email' => $admin->email])->assertSessionHas('success');
        Notification::assertSentTo($admin, ResetPassword::class);
        $token = Password::createToken($admin);
        $this->post('/admin/reset-password', ['email' => $admin->email, 'token' => $token, 'password' => 'ResetSecurePass123', 'password_confirmation' => 'ResetSecurePass123'])->assertRedirect('/admin/login');
        $this->assertTrue(Hash::check('ResetSecurePass123', $admin->fresh()->password));
    }
}
