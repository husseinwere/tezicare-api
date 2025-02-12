<?php

namespace App\Models\Queues;

use App\Models\PatientSession;
use App\Models\User;
use App\Models\Ward\Bed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InpatientQueue extends Model
{
    use HasFactory;

    protected $table = 'inpatients_queue';

    protected $fillable = [
        'session_id',
        'bed_id',
        'created_by',
        'status'
    ];

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function session()
    {
        return $this->belongsTo(PatientSession::class, 'session_id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
