<?php

namespace App\Models\Patient;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NurseInstruction extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'instruction',
        'created_by'
    ];

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
