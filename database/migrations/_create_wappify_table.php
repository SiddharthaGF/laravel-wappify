<?php

declare(strict_types=1);

use AiluraCode\Wappify\Enums\MessageStatusType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp', function (Blueprint $table) {
            $table->id();
            $table->string('wamid', 100)->unique();
            $table->string('profile', 100);
            $table->string('from', 20);
            $table->string('type', 20);
            $table->json('message');
            $table->string('state', 20)->default(MessageStatusType::WAITING->value);
            $table->bigInteger('timestamp');
        });
    }
};
