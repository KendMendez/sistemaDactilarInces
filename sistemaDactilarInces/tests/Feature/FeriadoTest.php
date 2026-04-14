<?php

use App\Models\Feriado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;

uses(RefreshDatabase::class);

beforeEach(function () {
    Feriado::truncate();
});

test('index returns all feriados', function () {
    Feriado::create(['fecha' => '2026-01-01', 'descripcion' => 'Año Nuevo']);
    Feriado::create(['fecha' => '2026-07-05', 'descripcion' => 'Día de la Independencia']);

    $response = $this->getJson('/api/feriado/index');

    $response->assertStatus(200)
        ->assertJson(['error' => 0])
        ->assertJsonCount(2, 'results');
});

test('showById returns feriado by id', function () {
    $feriado = Feriado::create(['fecha' => '2026-01-01', 'descripcion' => 'Año Nuevo']);
    $encryptedId = Crypt::encrypt($feriado->id);

    $response = $this->getJson("/api/feriado/showById{$encryptedId}");

    $response->assertStatus(200)
        ->assertJson(['error' => 0]);

    $response->assertJsonPath('results.fecha', '2026-01-01');
    $response->assertJsonPath('results.descripcion', 'Año Nuevo');
});

test('store creates a new feriado', function () {
    $response = $this->postJson('/api/feriado/store', [
        'fecha' => '2026-12-25',
        'descripcion' => 'Navidad',
    ]);

    $response->assertStatus(201)
        ->assertJson(['error' => 0]);

    $this->assertDatabaseHas('feriados', ['fecha' => '2026-12-25', 'descripcion' => 'Navidad']);
});

test('update modifies an existing feriado', function () {
    $feriado = Feriado::create(['fecha' => '2026-01-01', 'descripcion' => 'Año Nuevo']);
    $encryptedId = Crypt::encrypt($feriado->id);

    $response = $this->putJson("/api/feriado/update{$encryptedId}", [
        'fecha' => '2026-01-02',
        'descripcion' => 'Año Nuevo Actualizado',
    ]);

    $response->assertStatus(200)
        ->assertJson(['error' => 0]);

    $this->assertDatabaseHas('feriados', ['fecha' => '2026-01-02', 'descripcion' => 'Año Nuevo Actualizado']);
});

test('delete removes a feriado', function () {
    $feriado = Feriado::create(['fecha' => '2026-07-5', 'descripcion' => 'Día de la Independencia']);
    $encryptedId = Crypt::encrypt($feriado->id);

    $response = $this->deleteJson("/api/feriado/delete{$encryptedId}");

    $response->assertStatus(200)
        ->assertJson(['error' => 0]);

    $this->assertDatabaseMissing('feriados', ['id' => $feriado->id]);
});
