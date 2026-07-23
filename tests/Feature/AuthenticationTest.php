<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('keeps the user authenticated with the remember me cookie', function (): void {
    $user = User::factory()->create([
        'password' => 'password',
        'remember_token' => null,
    ]);

    $recallerCookie = Auth::guard()->getRecallerName();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'remember' => true,
    ]);

    $response->assertRedirect('/dashboard')->assertCookie($recallerCookie);
    $this->assertNotNull($user->refresh()->remember_token);

    $this->flushSession();
    Auth::forgetGuards();

    $this->withCookie($recallerCookie, $response->getCookie($recallerCookie)->getValue())
        ->get('/dashboard')
        ->assertOk();
});
