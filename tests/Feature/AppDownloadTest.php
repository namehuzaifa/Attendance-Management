<?php

namespace Tests\Feature;

use App\Http\Controllers\AppDownloadController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The sign-in screen offers the Android build, so employees can install it
 * before they have anything on their phone.
 */
class AppDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_apk_is_published(): void
    {
        $this->assertTrue(
            AppDownloadController::isAvailable(),
            'public/' . AppDownloadController::PATH . ' is missing — publish a build there',
        );
    }

    /**
     * Deliberately public: a phone scanning the QR has no session yet.
     */
    public function test_a_guest_can_download_the_app(): void
    {
        $response = $this->get(route('app.download'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.android.package-archive');
        $this->assertStringContainsString('HRMS.apk', $response->headers->get('content-disposition'));
    }

    public function test_the_download_really_is_an_android_package(): void
    {
        $handle = fopen(AppDownloadController::file(), 'rb');
        $magic = fread($handle, 4);
        fclose($handle);

        // Every APK is a zip, so it starts with the zip local-file header.
        $this->assertSame("PK\x03\x04", $magic);
    }

    /**
     * A small link under the sign-in button, not a block on the page.
     */
    public function test_the_login_page_links_to_the_app_dialog(): void
    {
        $page = $this->get('/login')->assertOk();

        $page->assertSee('Get the Android app');
        $page->assertSee('appDownloadModal', false);
        $page->assertSee(route('app.download'));
        $page->assertSee('assets/js/qrcode.js', false);
    }

    /**
     * Reached from the navbar icon, so the dashboards stay uncluttered.
     */
    public function test_both_dashboards_reach_the_app_dialog_from_the_navbar(): void
    {
        foreach ([User::factory()->admin()->create(), User::factory()->create()] as $user) {
            $page = $this->actingAs($user)->get('/dashboard')->assertOk();

            $page->assertSee('appDownloadModal', false);
            $page->assertSee('Download APK');
            $page->assertSee('data-feather="smartphone"', false);
        }
    }

    /**
     * The theme script binds `.nav-link-style` as the dark-mode switch and
     * rewrites the icon inside every match. Wearing that class made the app
     * icon a second toggle: tapping it also flipped the theme, and a reload
     * redrew it as another moon.
     */
    public function test_the_app_icon_is_not_wired_up_as_a_theme_toggle(): void
    {
        $html = $this->actingAs(User::factory()->admin()->create())
            ->get('/dashboard')
            ->assertOk()
            ->getContent();

        preg_match('/<a[^>]*appDownloadModal[^>]*>/', $html, $match);

        $this->assertNotEmpty($match, 'the navbar should link to the app dialog');
        $this->assertStringNotContainsString('nav-link-style', $match[0]);

        // And the real toggle is still there, exactly once.
        $this->assertSame(1, substr_count($html, 'nav-link-style'));
    }
}
