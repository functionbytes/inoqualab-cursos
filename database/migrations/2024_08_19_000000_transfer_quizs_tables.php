<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TransferQuizsTables extends Migration
{
    public function up()
    {

        $topics = DB::connection('mysql_second')->table('quiz_topics')->get();
        foreach ($topics as $topic) {
            DB::connection('mysql')->table('quiz_topics')->insert((array) $topic);
        }

        $questions = DB::connection('mysql_second')->table('exam_questions')->get();
        foreach ($questions as $question) {
            DB::connection('mysql')->table('exam_questions')->insert((array) $question);
        }

    }
}
