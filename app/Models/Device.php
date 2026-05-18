<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'kandang_id',
        'device_id',
        'device_name',
        'device_type',
        'component_type',
        'profile_image',
        'status',
        'connection_status',
        'device_state',
        'door_status',
        'light_status',
        'health_status',
        'signal_strength',
        'installation_date',
        'last_updated',
        'last_seen',
    ];

    protected $casts = [
        'last_updated' => 'datetime',
        'last_seen' => 'datetime',
        'installation_date' => 'date',
    ];

    public function kandang()
    {
        return $this->belongsTo(Kandang::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function deteksis()
    {
        return $this->hasMany(Deteksi::class);
    }

    public function suhus()
    {
        return $this->hasMany(Suhu::class);
    }

    public function ayams()
    {
        return $this->hasMany(Ayam::class);
    }
}