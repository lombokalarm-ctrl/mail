<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_displays_subscription_packages_and_travel_positioning(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('APLI Mail Untuk Travel Umrah dan Haji', false)
            ->assertSee('Paket Free')
            ->assertSee('Paket Silver')
            ->assertSee('Paket Gold')
            ->assertSee('Email Personal Jamaah')
            ->assertSee('application/ld+json', false);
    }
}
