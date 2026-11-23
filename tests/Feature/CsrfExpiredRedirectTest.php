<?php

namespace Tests\Feature;

use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CsrfExpiredRedirectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // В тестах VerifyCsrfToken отключён, поэтому эмулируем истёкший токен
        Route::middleware('web')->post('/_test/csrf-expired', function () {
            throw new TokenMismatchException('CSRF token mismatch.');
        });
    }

    public function test_guest_form_post_redirects_to_login_and_remembers_page(): void
    {
        $page = url('/cabinet/tickets/inbox');

        $this->post('/_test/csrf-expired', [], ['Referer' => $page])
            ->assertRedirect(route('login'))
            ->assertSessionHas('url.intended', $page);
    }

    public function test_foreign_referer_is_not_used_as_intended_url(): void
    {
        $this->post('/_test/csrf-expired', [], ['Referer' => rtrim(url('/'), '/') . '.evil.com/x'])
            ->assertRedirect(route('login'))
            ->assertSessionMissing('url.intended');
    }

    public function test_ajax_request_keeps_json_419(): void
    {
        $this->postJson('/_test/csrf-expired')->assertStatus(419);
    }
}
