<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suhu extends Model
{
    protected $fillable = [
        'kandang_id',
        'device_id',
        'temperature',
        'humidity'
    ];

    public function kandang()
    {
        return $this->belongsTo(Kandang::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
