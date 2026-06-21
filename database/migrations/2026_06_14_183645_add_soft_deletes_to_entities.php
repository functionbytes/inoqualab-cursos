<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'certifiers',
            'departments',
            'quiz_topics',
            'quiz_questions',
            'exam_topics',
            'exam_questions',
            'faqs',
            'faq_categories',
            'instruction_categories',
            'blog_categories',
            'blog_tags',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->softDeletes();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'certifiers',
            'departments',
            'quiz_topics',
            'quiz_questions',
            'exam_topics',
            'exam_questions',
            'faqs',
            'faq_categories',
            'instruction_categories',
            'blog_categories',
            'blog_tags',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropSoftDeletes();
            });
        }
    }
};
