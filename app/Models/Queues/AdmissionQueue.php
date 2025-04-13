<?php

namespace App\Models\Queues;

use App\Models\Patient\PatientSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionQueue extends Model
{
    use HasFactory;

    protected $table = 'admission_queue';

    protected $fillable = [
        'hospital_id',
        'session_id',
        'created_by',
        'status'
    ];

    public function session()
    {
        return $this->belongsTo(PatientSession::class, 'session_id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
