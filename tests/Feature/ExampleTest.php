<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_pocetna_preusmerava_na_admin(): void
    {
        $this->get('/')->assertRedirect('/admin');
    }
}
