<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

uses(RefreshDatabase::class);

test('registration screen can be rendered in local environment', function (): void {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register in local environment', function (): void {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('chats.index', absolute: false));
});

test('registration returns 404 in production environment', function (): void {
    App::shouldReceive('isProduction')->andReturn(true);

    $response = $this->get('/register');

    $response->assertStatus(404);
});
