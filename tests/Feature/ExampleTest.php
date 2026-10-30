<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The portal has no public landing page — the root sends visitors to the
     * sign-in screen.
     */
    public function test_the_root_url_sends_guests_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_the_login_screen_renders(): void
    {
        $this->get('/login')->assertOk();
    }
}
