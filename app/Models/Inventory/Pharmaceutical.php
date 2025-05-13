<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmaceutical extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'name',
        'price',
        'quantity',
        'created_by',
        'status'
    ];
    
    public static function foreignKey()
    {
        return 'pharmaceutical_id';
    }

    public function prices()
    {
        return $this->hasMany(PharmaceuticalPrice::class);
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
