<?php

namespace App\Models;

use Filament\Forms;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'manufacturer',
        'model_no',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    public function spectrumTypes(): BelongsToMany
    {
        return $this->belongsToMany(SpectrumType::class);
    }

    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class);
    }

    public static function getForm(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('manufacturer')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('model_no')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('spectrum_type')
                ->relationship('spectrumTypes', 'name')
                ->multiple()
                ->options(function () {
                    return SpectrumType::all()?->pluck('name', 'id');
                }),
        ];
    }
}
