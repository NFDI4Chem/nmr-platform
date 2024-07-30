<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Solvent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'molecular_formula',
        'molecular_weight',
        'meta_data',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'molecular_weight' => 'decimal:2',
    ];

    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class);
    }

    public function structures(): HasMany
    {
        return $this->hasMany(Structure::class);
    }
}
