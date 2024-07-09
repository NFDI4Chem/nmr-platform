<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sample extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_id',
        'company_id',
        'identifier',
        'solvent_id',
        'molecule_id',
        'spectrum_type',
        'instructions',
        'featured_image_id',
        'priority',
        'operator_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'device_id' => 'integer',
        'company_id' => 'integer',
        'solvent_id' => 'integer',
        'molecule_id' => 'integer',
        'operator_id' => 'integer',
    ];

    public function stectrumTypes(): BelongsToMany
    {
        return $this->belongsToMany(StectrumType::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function solvent(): BelongsTo
    {
        return $this->belongsTo(Solvent::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function molecule(): BelongsTo
    {
        return $this->belongsTo(Molecule::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
}
