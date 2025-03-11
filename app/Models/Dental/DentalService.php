<?php

namespace App\Models\Dental;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentalService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'created_by'
    ];

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
