<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índices de performance para el constructor de cursos. `slack` es la columna de
 * acceso más caliente (edit/update/destroy/create la consultan sin índice → full
 * scan). Los compuestos (course_id, available, position) cubren el listado y las
 * relaciones chapters()/lessons() (filtran course_id+available, ordenan position).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->index('slack', 'courses_slack_index');
            $table->index('created_at', 'courses_created_at_index');
        });

        Schema::table('course_chapters', function (Blueprint $table) {
            $table->index('slack', 'course_chapters_slack_index');
            $table->index(['course_id', 'available', 'position'], 'course_chapters_course_avail_pos_index');
        });

        Schema::table('course_lessons', function (Blueprint $table) {
            $table->index('slack', 'course_lessons_slack_index');
            $table->index(['course_id', 'available', 'position'], 'course_lessons_course_avail_pos_index');
        });

        Schema::table('exam_questions', function (Blueprint $table) {
            $table->index('slack', 'exam_questions_slack_index');
        });

        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->index('slack', 'quiz_questions_slack_index');
        });

        Schema::table('exam_topics', function (Blueprint $table) {
            $table->index('slack', 'exam_topics_slack_index');
        });

        Schema::table('quiz_topics', function (Blueprint $table) {
            $table->index('slack', 'quiz_topics_slack_index');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('courses_slack_index');
            $table->dropIndex('courses_created_at_index');
        });
        Schema::table('course_chapters', function (Blueprint $table) {
            $table->dropIndex('course_chapters_slack_index');
            $table->dropIndex('course_chapters_course_avail_pos_index');
        });
        Schema::table('course_lessons', function (Blueprint $table) {
            $table->dropIndex('course_lessons_slack_index');
            $table->dropIndex('course_lessons_course_avail_pos_index');
        });
        Schema::table('exam_questions', fn (Blueprint $table) => $table->dropIndex('exam_questions_slack_index'));
        Schema::table('quiz_questions', fn (Blueprint $table) => $table->dropIndex('quiz_questions_slack_index'));
        Schema::table('exam_topics', fn (Blueprint $table) => $table->dropIndex('exam_topics_slack_index'));
        Schema::table('quiz_topics', fn (Blueprint $table) => $table->dropIndex('quiz_topics_slack_index'));
    }
};
