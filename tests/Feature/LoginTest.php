<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Workbench\App\Models\User;

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

test('login returns access token when attempt success', function () {
    $credentials = [
        'email' => 'test@example.com',
        'password' => 'dcG&494hj.6k'
    ];

    $user = Mockery::mock(new User)->makePartial();

    Auth::expects('attempt')
        ->with($credentials)
        ->andReturn(true);
    Auth::expects('user')
        ->andReturn($user);

    $fakeToken = Str::random();

    $user->shouldReceive('createToken')
        ->with('api')
        ->andReturn(new NewAccessToken(new PersonalAccessToken(), $fakeToken));

    $response = $this->withHeaders(['accept' => 'application/json'])
        ->post('/login', $credentials);

    $response->assertStatus(200)
        ->assertJson([
            'access_token' => $fakeToken
        ]);
});