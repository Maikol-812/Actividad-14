<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Receta extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'porciones',
        'instrucciones',
    ];

    /**
     * Ingredientes de la receta (1..*).
     * La cantidad y la unidad viven en la clase asociación RecetaIngrediente.
     */
    public function ingredientes(): BelongsToMany
    {
        return $this->belongsToMany(Ingrediente::class, 'receta_ingrediente')
            ->using(RecetaIngrediente::class)
            ->withPivot('cantidad', 'unidad');
    }
}