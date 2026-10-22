<?php

use App\Models\Ingrediente;
use App\Models\Receta;
use App\Models\RecetaIngrediente;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Estas pruebas usan la base de datos, así que necesitan arrancar Laravel
uses(TestCase::class, RefreshDatabase::class);

test('se instancia una receta con título, porciones e instrucciones', function () {
    $receta = Receta::create([
        'titulo' => 'Panqueques',
        'porciones' => 4,
        'instrucciones' => 'Mezclar todo y cocinar en sartén caliente.',
    ]);

    expect($receta->exists)->toBeTrue()
        ->and($receta->titulo)->toBe('Panqueques')
        ->and($receta->porciones)->toBe(4)
        ->and($receta->ingredientes)->toHaveCount(0);

    $this->assertDatabaseHas('recetas', ['titulo' => 'Panqueques', 'porciones' => 4]);
});

test('una receta contiene varios ingredientes, cada uno con su cantidad y unidad', function () {
    $receta = Receta::create([
        'titulo' => 'Panqueques',
        'porciones' => 4,
        'instrucciones' => 'Mezclar todo y cocinar en sartén caliente.',
    ]);
    $harina = Ingrediente::create(['nombre' => 'Harina']);
    $leche = Ingrediente::create(['nombre' => 'Leche']);

    $receta->ingredientes()->attach($harina, ['cantidad' => 200, 'unidad' => 'g']);
    $receta->ingredientes()->attach($leche, ['cantidad' => 250, 'unidad' => 'ml']);

    $ingredientes = $receta->ingredientes;
    $enHarina = $ingredientes->firstWhere('nombre', 'Harina');
    $enLeche = $ingredientes->firstWhere('nombre', 'Leche');

    expect($ingredientes)->toHaveCount(2)
        ->and($enHarina->pivot->cantidad)->toBe(200.0)
        ->and($enHarina->pivot->unidad)->toBe('g')
        ->and($enLeche->pivot->cantidad)->toBe(250.0)
        ->and($enLeche->pivot->unidad)->toBe('ml');

    $this->assertDatabaseHas('receta_ingrediente', [
        'receta_id' => $receta->id,
        'ingrediente_id' => $harina->id,
        'unidad' => 'g',
    ]);
});

test('la clase asociación conoce la receta y el ingrediente que une', function () {
    $receta = Receta::create([
        'titulo' => 'Panqueques',
        'porciones' => 4,
        'instrucciones' => 'Mezclar todo y cocinar en sartén caliente.',
    ]);
    $harina = Ingrediente::create(['nombre' => 'Harina']);
    $receta->ingredientes()->attach($harina, ['cantidad' => 200, 'unidad' => 'g']);

    $linea = $receta->ingredientes()->first()->pivot;

    expect($linea)->toBeInstanceOf(RecetaIngrediente::class)
        ->and($linea->receta->is($receta))->toBeTrue()
        ->and($linea->ingrediente->is($harina))->toBeTrue();
});

test('un mismo ingrediente puede usarse en otra receta con otra cantidad y unidad', function () {
    $harina = Ingrediente::create(['nombre' => 'Harina']);
    $panqueques = Receta::create(['titulo' => 'Panqueques', 'porciones' => 4, 'instrucciones' => 'Mezclar y cocinar.']);
    $pan = Receta::create(['titulo' => 'Pan casero', 'porciones' => 8, 'instrucciones' => 'Amasar y hornear.']);

    $panqueques->ingredientes()->attach($harina, ['cantidad' => 200, 'unidad' => 'g']);
    $pan->ingredientes()->attach($harina, ['cantidad' => 1, 'unidad' => 'kg']);

    $enPanqueques = $panqueques->ingredientes()->first();
    $enPan = $pan->ingredientes()->first();

    expect($enPanqueques->is($enPan))->toBeTrue()
        ->and($enPanqueques->pivot->cantidad)->toBe(200.0)
        ->and($enPanqueques->pivot->unidad)->toBe('g')
        ->and($enPan->pivot->cantidad)->toBe(1.0)
        ->and($enPan->pivot->unidad)->toBe('kg');
});

test('un ingrediente no puede repetirse dentro de la misma receta', function () {
    $receta = Receta::create([
        'titulo' => 'Panqueques',
        'porciones' => 4,
        'instrucciones' => 'Mezclar todo y cocinar en sartén caliente.',
    ]);
    $harina = Ingrediente::create(['nombre' => 'Harina']);

    $receta->ingredientes()->attach($harina, ['cantidad' => 200, 'unidad' => 'g']);

    // El segundo intento viola la llave primaria compuesta (receta_id, ingrediente_id)
    expect(fn () => $receta->ingredientes()->attach($harina, ['cantidad' => 50, 'unidad' => 'g']))
        ->toThrow(QueryException::class);

    $this->assertDatabaseCount('receta_ingrediente', 1);
});