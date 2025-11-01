<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dbo_migrations', static function (Blueprint $table) {
            $table->id();
            $table->string('object_name');
            $table->string('group')->nullable();
            $table->string('driver', 32)->nullable();
            $table->string('object_type', 32)->nullable();
            $table->char('checksum', 64)->nullable();
            $table->unsignedInteger('batch')->default(0);
            $table->string('status', 16)->default('applied');
            $table->timestamp('migrated_at')->nullable();
            $table->json('meta')->nullable();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('dbo_migrations');
    }
};
