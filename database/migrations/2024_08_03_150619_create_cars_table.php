<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // При проектировании сделано допущение, что id (external_id) уникален, для упращения реализации (метод upsert), в реальности это бы уточнялось.
    // Также сделано допущение, что любое поле кроме id может быть null
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id()->from(1001);
            $table->bigInteger('external_id')->unique();
            $table->string('mark', 200)->nullable()->index();
            $table->string('model')->nullable()->index();
            $table->string('generation')->nullable()->index();
            $table->smallInteger('year')->nullable()->index();
            $table->integer('run')->nullable()->index();
            $table->string('color')->nullable()->index();
            $table->string('body_type')->nullable()->index();
            $table->string('engine_type')->nullable()->index();
            $table->string('transmission')->nullable()->index();
            $table->string('gear_type')->nullable()->index();
            $table->bigInteger('generation_id')->nullable();
            $table->timestampTz('created_at')->default(new Expression('CURRENT_TIMESTAMP'));
            $table->timestampTz('updated_at')->default(new Expression('CURRENT_TIMESTAMP'))->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
