<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickCalculation extends Model
{
    use HasFactory;

    protected $table = "pick_calculations";

    protected $fillable = [
        'machine_status_id',
        'intime_pick',
        'shift_pick',
        'total_pick',
        'new_pick',
        'difference_pick',
    ];

    public $timestamps = true;

    public function machine_status()
    {
        return $this->belongsTo(MachineStatus::class, 'machine_status_id', 'id');
    }
}
