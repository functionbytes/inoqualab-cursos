<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Check whether an index exists on a table using SHOW INDEX.
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $result = DB::select(
            'SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?',
            [$indexName]
        );

        return count($result) > 0;
    }

    public function up(): void
    {
        // ---------------------------------------------------------------
        // courses — slug lookup (public pages), available filter
        // ---------------------------------------------------------------
        if (! $this->hasIndex('courses', 'courses_slug_index')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->index('slug', 'courses_slug_index');
            });
        }

        if (! $this->hasIndex('courses', 'courses_available_index')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->index('available', 'courses_available_index');
            });
        }

        if (! $this->hasIndex('courses', 'courses_certification_id_index')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->index('certification_id', 'courses_certification_id_index');
            });
        }

        if (! $this->hasIndex('courses', 'courses_certifier_id_index')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->index('certifier_id', 'courses_certifier_id_index');
            });
        }

        // ---------------------------------------------------------------
        // users — role filter (admin listing), available filter, slack lookup
        // ---------------------------------------------------------------
        if (! $this->hasIndex('users', 'users_role_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('role', 'users_role_index');
            });
        }

        if (! $this->hasIndex('users', 'users_available_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('available', 'users_available_index');
            });
        }

        if (! $this->hasIndex('users', 'users_slack_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('slack', 'users_slack_index');
            });
        }

        if (! $this->hasIndex('users', 'users_departament_id_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('departament_id', 'users_departament_id_index');
            });
        }

        // ---------------------------------------------------------------
        // orders — condition_id FK (used in JOIN with order_condition)
        // user_id already has orders_user_id_foreign; condition_id needs one
        // ---------------------------------------------------------------
        if (! $this->hasIndex('orders', 'orders_condition_id_status_index')) {
            Schema::table('orders', function (Blueprint $table) {
                // condition_id already has its own FK key; skip single index
                // Add composite for the dashboard query: user + condition
                $table->index(['user_id', 'condition_id'], 'orders_user_id_condition_id_index');
            });
        }

        // ---------------------------------------------------------------
        // inscriptions — user_id + course_id composite (lookup / check duplicate)
        // Existing: inscriptions_user_id_course_id_order_id_percent_culminated_index
        // covers user_id + course_id at the prefix — no new index needed.
        // Add: culminated alone for "active inscriptions" filter queries.
        // ---------------------------------------------------------------
        if (! $this->hasIndex('inscriptions', 'inscriptions_culminated_expire_index')) {
            Schema::table('inscriptions', function (Blueprint $table) {
                $table->index(['culminated', 'expire'], 'inscriptions_culminated_expire_index');
            });
        }

        // ---------------------------------------------------------------
        // course_progress — inscription_id + lesson_id composite
        // Used for "has user completed this lesson?" lookup.
        // Existing key covers (user_id, course_id, inscription_id) and separate lesson_id.
        // Adding (inscription_id, lesson_id) for direct lesson completion checks.
        // ---------------------------------------------------------------
        if (! $this->hasIndex('course_progress', 'course_progress_inscription_id_lesson_id_index')) {
            Schema::table('course_progress', function (Blueprint $table) {
                $table->index(['inscription_id', 'lesson_id'], 'course_progress_inscription_id_lesson_id_index');
            });
        }

        // ---------------------------------------------------------------
        // certificates — user_id alone (listing user's certificates)
        // Existing composite (user_id, course_id, inscription_id, exam_id) covers it at prefix.
        // No new index needed — covered.
        //
        // course_reviews — inscription_id FK has no index
        // ---------------------------------------------------------------
        if (! $this->hasIndex('course_reviews', 'course_reviews_inscription_id_index')) {
            Schema::table('course_reviews', function (Blueprint $table) {
                $table->index('inscription_id', 'course_reviews_inscription_id_index');
            });
        }

        // ---------------------------------------------------------------
        // enterprises — available filter (admin listing), slack lookup
        // ---------------------------------------------------------------
        if (! $this->hasIndex('enterprises', 'enterprises_available_index')) {
            Schema::table('enterprises', function (Blueprint $table) {
                $table->index('available', 'enterprises_available_index');
            });
        }

        if (! $this->hasIndex('enterprises', 'enterprises_slack_index')) {
            Schema::table('enterprises', function (Blueprint $table) {
                $table->index('slack', 'enterprises_slack_index');
            });
        }

        // ---------------------------------------------------------------
        // coupons — available filter (storefront coupon validation), code lookup
        // ---------------------------------------------------------------
        if (! $this->hasIndex('coupons', 'coupons_code_index')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->index('code', 'coupons_code_index');
            });
        }

        if (! $this->hasIndex('coupons', 'coupons_available_index')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->index('available', 'coupons_available_index');
            });
        }

        // ---------------------------------------------------------------
        // bundles — available filter, slug lookup
        // ---------------------------------------------------------------
        if (! $this->hasIndex('bundles', 'bundles_available_index')) {
            Schema::table('bundles', function (Blueprint $table) {
                $table->index('available', 'bundles_available_index');
            });
        }

        if (! $this->hasIndex('bundles', 'bundles_slug_index')) {
            Schema::table('bundles', function (Blueprint $table) {
                $table->index('slug', 'bundles_slug_index');
            });
        }

        // ---------------------------------------------------------------
        // settings — key lookup (used heavily by Setting model)
        // ---------------------------------------------------------------
        if (! $this->hasIndex('settings', 'settings_key_index')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->index('key', 'settings_key_index');
            });
        }

        // ---------------------------------------------------------------
        // tickets — composite (status_id, user_id) for inbox view
        // Existing: single indexes on status_id, user_id separately.
        // ---------------------------------------------------------------
        if (! $this->hasIndex('tickets', 'tickets_status_id_user_id_index')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->index(['status_id', 'user_id'], 'tickets_status_id_user_id_index');
            });
        }

        // ---------------------------------------------------------------
        // reviewables — (reviewable_type, reviewable_id) polymorphic lookup
        // ---------------------------------------------------------------
        if (! $this->hasIndex('reviewables', 'reviewables_reviewable_type_reviewable_id_index')) {
            Schema::table('reviewables', function (Blueprint $table) {
                $table->index(['reviewable_type', 'reviewable_id'], 'reviewables_reviewable_type_reviewable_id_index');
            });
        }

        // ---------------------------------------------------------------
        // blog_categories — available filter, slug lookup
        // ---------------------------------------------------------------
        if (! $this->hasIndex('blog_categories', 'blog_categories_slug_index')) {
            Schema::table('blog_categories', function (Blueprint $table) {
                $table->index('slug', 'blog_categories_slug_index');
            });
        }

        // ---------------------------------------------------------------
        // blogs — available + categorie_id composite (listing by category)
        // Existing: fk_blogs_categories_idx covers categorie_id alone.
        // ---------------------------------------------------------------
        if (! $this->hasIndex('blogs', 'blogs_available_categorie_id_index')) {
            Schema::table('blogs', function (Blueprint $table) {
                $table->index(['available', 'categorie_id'], 'blogs_available_categorie_id_index');
            });
        }

        // ---------------------------------------------------------------
        // course_chapters — available filter (lessons tree loads only available)
        // ---------------------------------------------------------------
        if (! $this->hasIndex('course_chapters', 'course_chapters_available_index')) {
            Schema::table('course_chapters', function (Blueprint $table) {
                $table->index('available', 'course_chapters_available_index');
            });
        }

        // ---------------------------------------------------------------
        // course_lessons — available filter
        // ---------------------------------------------------------------
        if (! $this->hasIndex('course_lessons', 'course_lessons_available_index')) {
            Schema::table('course_lessons', function (Blueprint $table) {
                $table->index('available', 'course_lessons_available_index');
            });
        }
    }

    public function down(): void
    {
        $drops = [
            'courses' => [
                'courses_slug_index',
                'courses_available_index',
                'courses_certification_id_index',
                'courses_certifier_id_index',
            ],
            'users' => [
                'users_role_index',
                'users_available_index',
                'users_slack_index',
                'users_departament_id_index',
            ],
            'orders' => [
                'orders_user_id_condition_id_index',
            ],
            'inscriptions' => [
                'inscriptions_culminated_expire_index',
            ],
            'course_progress' => [
                'course_progress_inscription_id_lesson_id_index',
            ],
            'course_reviews' => [
                'course_reviews_inscription_id_index',
            ],
            'enterprises' => [
                'enterprises_available_index',
                'enterprises_slack_index',
            ],
            'coupons' => [
                'coupons_code_index',
                'coupons_available_index',
            ],
            'bundles' => [
                'bundles_available_index',
                'bundles_slug_index',
            ],
            'settings' => [
                'settings_key_index',
            ],
            'tickets' => [
                'tickets_status_id_user_id_index',
            ],
            'reviewables' => [
                'reviewables_reviewable_type_reviewable_id_index',
            ],
            'blog_categories' => [
                'blog_categories_slug_index',
            ],
            'blogs' => [
                'blogs_available_categorie_id_index',
            ],
            'course_chapters' => [
                'course_chapters_available_index',
            ],
            'course_lessons' => [
                'course_lessons_available_index',
            ],
        ];

        foreach ($drops as $table => $indexes) {
            foreach ($indexes as $indexName) {
                if ($this->hasIndex($table, $indexName)) {
                    Schema::table($table, function (Blueprint $t) use ($indexName) {
                        $t->dropIndex($indexName);
                    });
                }
            }
        }
    }
};
