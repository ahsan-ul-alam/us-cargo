<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('image'); // image, image_text, video, text
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->unsignedInteger('delay_seconds')->default(3);
            $table->string('frequency')->default('once_per_session'); // always, once_per_session, once_per_day
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('body');
            $table->string('photo')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        foreach ([
            ['রিয়াদ হোসেন', 'লন্ডন', 'লন্ডন থেকে ঢাকায় আমার পার্সেল ঠিক ৬ দিনে পৌঁছেছে, একদম অক্ষত অবস্থায়।'],
            ['ফারহানা আক্তার', 'চট্টগ্রাম', 'ব্যবসার জন্য নিয়মিত এয়ার কার্গোতে মাল পাঠাই। রেট সবচেয়ে কম আর ডেলিভারি দ্রুত।'],
            ['তানভীর আহমেদ', 'বার্মিংহাম', 'কাস্টমসের ঝামেলা নিয়ে চিন্তিত ছিলাম, কিন্তু ওরা সব সামলে দিয়েছে।'],
        ] as $i => [$name, $loc, $body]) {
            DB::table('reviews')->insert([
                'name' => $name, 'location' => $loc, 'rating' => 5, 'body' => $body,
                'is_approved' => true, 'sort_order' => $i, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('popups');
        Schema::dropIfExists('site_settings');
    }
};
