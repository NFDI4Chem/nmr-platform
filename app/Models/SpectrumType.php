<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SpectrumType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'neuclei',
        'dimentionality',
        'name',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    public function samples(): BelongsToMany
    {
        return $this->belongsToMany(Sample::class);
    }

    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(Device::class);
    }
}
