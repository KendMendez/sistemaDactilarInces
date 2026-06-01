<?php

use App\Models\Cargo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cargo::truncate();
});

test('index returns all cargos', function () {
    Cargo::create(['cargo' => 'SuperAdmin1']);
    Cargo::create(['cargo' => 'SuperAdmin2']);

    $response = $this->getJson('/api/cargo');

    $response->assertStatus(200)
        ->assertJson(['error' => 0])
        ->assertJsonCount(2, 'results');
});

test('showById returns cargo by id', function () {
    $cargo = Cargo::create(['cargo' => 'SuperAdmin1']);
    $encryptedId = Crypt::encrypt($cargo->id);

    $response = $this->getJson("/api/cargo/{$encryptedId}");

    $response->assertStatus(200)
        ->assertJson(['error' => 0]);

    $response->assertJsonPath('results.cargo', 'SuperAdmin1');
});

test('store creates a new cargo', function () {
    $response = $this->postJson('/api/cargo', [
        'cargo' => 'SuperAdmin1',
    ]);

    $response->assertStatus(201)
        ->assertJson(['error' => 0]);

    $this->assertDatabaseHas('cargos', ['cargo' => 'SuperAdmin1']);
});

test('update modifies an existing cargo', function () {
    $cargo = Cargo::create(['cargo' => 'SuperAdmin1']);
    $encryptedId = Crypt::encrypt($cargo->id);

    $response = $this->putJson("/api/cargo/{$encryptedId}", [
        'cargo' => 'SuperAdmin3',
    ]);

    $response->assertStatus(200)
        ->assertJson(['error' => 0]);

    $this->assertDatabaseHas('cargos', ['cargo' => 'SuperAdmin3']);
});

test('delete removes a cargo', function () {
    $cargo = Cargo::create(['cargo' => 'SuperAdmin2']);
    $encryptedId = Crypt::encrypt($cargo->id);

    $response = $this->deleteJson("/api/cargo/{$encryptedId}");

    $response->assertStatus(200)
        ->assertJson(['error' => 0]);

    $this->assertDatabaseMissing('cargos', ['id' => $cargo->id]);
});
