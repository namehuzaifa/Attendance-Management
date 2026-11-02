<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The portal is installable, so iPhone and desktop staff get an app icon
 * too — the APK only helps Android.
 */
class PwaTest extends TestCase
{
    public function test_the_manifest_is_served_and_well_formed(): void
    {
        $path = public_path('manifest.webmanifest');

        $this->assertFileExists($path);

        $manifest = json_decode(file_get_contents($path), true);

        $this->assertIsArray($manifest, 'the manifest must be valid JSON');
        $this->assertSame('HRMS', $manifest['short_name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('/dashboard', $manifest['start_url']);
    }

    /**
     * Android needs a 192 and a 512, and a maskable one or the launcher
     * draws the icon inside a white blob.
     */
    public function test_the_manifest_icons_exist_at_the_sizes_android_wants(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true);

        $sizes = array_column($manifest['icons'], 'sizes');
        $this->assertContains('192x192', $sizes);
        $this->assertContains('512x512', $sizes);

        $this->assertContains('maskable', array_column($manifest['icons'], 'purpose'));

        foreach ($manifest['icons'] as $icon) {
            $file = public_path(ltrim($icon['src'], '/'));

            $this->assertFileExists($file, $icon['src'] . ' is listed but missing');

            [$width, $height] = getimagesize($file);
            [$declaredW, $declaredH] = array_map('intval', explode('x', $icon['sizes']));

            $this->assertSame($declaredW, $width, $icon['src'] . ' is not the size it claims');
            $this->assertSame($declaredH, $height);
        }
    }

    public function test_pages_advertise_the_manifest_and_register_the_worker(): void
    {
        $page = $this->get('/login')->assertOk();

        $page->assertSee('manifest.webmanifest', false);
        $page->assertSee('serviceWorker', false);
        $page->assertSee('apple-touch-icon', false);
    }

    /**
     * Pages are rendered for a signed-in person, so caching them could show
     * one employee's data to the next and would serve stale CSRF tokens.
     */
    public function test_the_worker_never_caches_pages(): void
    {
        $worker = file_get_contents(public_path('sw.js'));

        $this->assertStringContainsString('offline.html', $worker);
        $this->assertStringNotContainsString("cache.put(request, response.clone())\n        }\n\n    // navigate", $worker);

        // The navigation branch must fall back to the offline page, never to
        // a cached copy of a real page.
        $this->assertMatchesRegularExpression(
            "/request\.mode === 'navigate'[\s\S]*caches\.match\('\/offline\.html'\)/",
            $worker,
        );
    }

    public function test_the_offline_page_stands_on_its_own(): void
    {
        $html = file_get_contents(public_path('offline.html'));

        // It is shown without a network, so it cannot pull in remote assets.
        $this->assertStringNotContainsString('http://', $html);
        $this->assertStringNotContainsString('https://', $html);
        $this->assertStringContainsString('offline', strtolower($html));
    }

    public function test_the_app_dialog_offers_a_web_install(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Install the web app')
            ->assertSee('Add to Home Screen', false);
    }

    /**
     * Safari implements no install API — beforeinstallprompt is Chromium
     * only — so an iPhone can never be given a working Install button. It
     * gets the manual Share steps instead, and the script has to know the
     * difference.
     */
    public function test_iphones_are_given_the_manual_steps_instead_of_a_button(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertStringContainsString('hrmsIosInstall', $html);
        $this->assertStringContainsString('iPad|iPhone|iPod', $html);

        // iPad reports itself as a Mac, so touch points are the giveaway.
        $this->assertStringContainsString('maxTouchPoints', $html);

        $this->assertStringContainsString('Tap the <strong>Share</strong> button', $html);
        $this->assertStringContainsString('only works in Safari', $html);
    }
}
