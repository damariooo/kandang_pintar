<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kandang_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('device_id')->unique();
            $table->string('device_name')->nullable();
            $table->enum('device_type', [
                'gateway',
                'sensor',
                'actuator'
            ])->nullable();
            $table->enum('component_type', [
                'esp32',
                'dht22',
                'ultrasonic',
                'servo',
                'led',
                'buzzer'
            ])->nullable();
            $table->string('profile_image')->nullable();
            $table->enum('status', ['aktif', 'non-aktif'])->default('non-aktif');
            $table->enum('connection_status', ['online', 'offline'])
                ->default('offline');
            $table->enum('device_state', [
                'active',
                'idle',
                'error',
                'maintenance'
            ])->default('active');
            $table->enum('door_status', ['TERBUKA', 'TERTUTUP'])
                ->nullable();
            $table->enum('light_status', ['HIDUP', 'MATI'])
                ->nullable();
            $table->enum('health_status', [
                'EXCELLENT',
                'DEGRADED',
                'CRITICAL',
                'MAINTENANCE'
            ])->default('EXCELLENT');
            $table->integer('signal_strength')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->date('installation_date')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
