<?php

namespace Tests\Feature;

// 1. Import Trait RefreshDatabase agar dikenali
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    // 2. Gunakan Trait di dalam class
    // Ini akan mereset database :memory: setiap kali tes jalan
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Tes ini mencoba akses halaman utama (root /)
        $response = $this->get('/');

        // Memastikan statusnya 200 (OK)
        $response->assertStatus(200);
    }
}
