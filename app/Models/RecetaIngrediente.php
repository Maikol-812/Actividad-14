<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;


class RecetaIngrediente extends Pivot
{
    protected $table = 'receta_ingrediente';

    public $timestamps = false;

    protected $casts = [
        'cantidad' => 'float',
    ];

    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class);
    }

    public function ingrediente(): BelongsTo
    {
        return $this->belongsTo(Ingrediente::class);
    }
}