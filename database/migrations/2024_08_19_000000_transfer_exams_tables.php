<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TransferExamsTables extends Migration
{
    public function up()
    {
        if (app()->environment('testing')) {
            return;
        }

        $topics = DB::connection('mysql_second')->table('exam_topics')->get();
        foreach ($topics as $topic) {
            DB::connection('mysql')->table('exam_topics')->insert((array) $topic);
        }

        $questions = DB::connection('mysql_second')->table('exam_questions')->get();
        foreach ($questions as $question) {
            DB::connection('mysql')->table('exam_questions')->insert((array) $question);
        }

    }
}
