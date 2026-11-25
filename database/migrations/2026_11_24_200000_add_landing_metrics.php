<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('landing_visits', function (Blueprint $table) {
            $table->date('day');
            $table->char('locale', 2);
            $table->unsignedBigInteger('sessions')->default(0);
            $table->primary(['day', 'locale']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->char('signup_landing_locale', 2)->nullable();
            $table->timestamp('first_week_classroom_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['signup_landing_locale', 'first_week_classroom_at']);
        });
        Schema::dropIfExists('landing_visits');
    }
};
