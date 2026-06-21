<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TransferBlogsTables extends Migration
{
    public function up()
    {
        if (app()->environment('testing')) {
            return;
        }

        $categories = DB::connection('mysql_second')->table('categories_blogs')->get();
        foreach ($categories as $categorie) {
            DB::connection('mysql')->table('blog_categories')->insert((array) $categorie);
        }

        $blogs = DB::connection('mysql_second')->table('blogs')->get();
        foreach ($blogs as $blog) {
            DB::connection('mysql')->table('blogs')->insert((array) $blog);
        }

    }
}
