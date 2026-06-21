<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TransferCoursesTables extends Migration
{
    public function up()
    {
        if (app()->environment('testing')) {
            return;
        }

        $courseCategories = DB::connection('mysql_second')->table('categories')->get();
        foreach ($courseCategories as $category) {
            DB::connection('mysql')->table('course_categories')->insert((array) $category);
        }

        $courses = DB::connection('mysql_second')->table('courses')->get();
        foreach ($courses as $course) {
            DB::connection('mysql')->table('courses')->insert((array) $course);
        }

        $courseChapters = DB::connection('mysql_second')->table('course_chapters')->get();
        foreach ($courseChapters as $chapter) {
            DB::connection('mysql')->table('course_chapters')->insert((array) $chapter);
        }

        $courseTypes = DB::connection('mysql_second')->table('types')->get();
        foreach ($courseTypes as $type) {
            DB::connection('mysql')->table('course_types')->insert((array) $type);
        }

        $courseLessons = DB::connection('mysql_second')->table('course_classes')->get();
        foreach ($courseLessons as $lesson) {
            DB::connection('mysql')->table('course_lessons')->insert((array) $lesson);
        }
    }
}
