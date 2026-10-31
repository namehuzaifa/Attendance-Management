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

    public function test_the_login_page_offers_the_app_with_a_scannable_code(): void
    {
        $page = $this->get('/login')->assertOk();

        $page->assertSee('Android app');
        $page->assertSee('Download APK');
        $page->assertSee(route('app.download'));
        $page->assertSee('assets/js/qrcode.js', false);
    }

    public function test_both_dashboards_offer_the_app(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Download APK');

        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Download APK');
    }
}
