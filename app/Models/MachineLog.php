<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineLog extends Model
{
    use HasFactory;

    protected $table = "machine_logs";

    protected $fillable = [
        'machine_id',
        'speed',
        'mode',
        'pick',
        'machine_datetime',
    ];

    public $timestamps = true;

    public function machine() {
        return $this->belongsTo(MachineMaster::class, 'machine_id', 'id');
    }
}
