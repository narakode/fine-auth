<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\Fluent\AssertableJson;

test('login returns 422 error when credentials empty', function () {
    $response = $this->withHeaders(['accept' => 'application/json'])
        ->post('/login');

    $response->assertStatus(422)
        ->assertJson(function (AssertableJson $json) {
            $json->hasAll('message', 'errors.email', 'errors.password');
        });
});

test('login returns 401 error when email not found', function () {
    $credentials = [
        'email' => 'random@email.com',
        'password' => '3r}!<-F71Gy|'
    ];

    Auth::expects('attempt')
        ->with($credentials)
        ->andReturn(false);

    $response = $this->withHeaders(['accept' => 'application/json'])
        ->post('/login', $credentials);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'The provided credentials do not match our records.'
        ]);
});