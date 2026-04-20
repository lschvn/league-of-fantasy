<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(function () {
    File::ensureDirectoryExists(resource_path('swagger'));
});

it('serves the generated scramble openapi document', function () {
    $response = $this->getJson('/docs/api.json');

    $response
        ->assertOk()
        ->assertJsonPath('openapi', '3.1.0');
});

it('serves the swagger json endpoint from the exported specification', function () {
    $this->artisan('scramble:export', ['--path' => resource_path('swagger/openapi.json')])->assertSuccessful();

    $response = $this->getJson('/swagger/v1');

    $response
        ->assertOk()
        ->assertJsonPath('openapi', '3.1.0');
});
