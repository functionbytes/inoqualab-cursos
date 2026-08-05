<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dropea 11 tablas huérfanas: el sistema de tickets de soporte (10 tablas) y
 * `payment_gateways`. Ninguna tiene código que la use -- ni modelo, ni
 * controller, ni ruta -- solo aparecen en migraciones ARCHIVADAS
 * (`database/migrations/ojo/` y `ojo/ya/`), no en el esquema mantenido.
 * Confirmado antes de dropear: las 11 están vacías (count = 0) tanto en dev
 * como se espera en producción, dado que ningún flujo de la app escribe en
 * ellas.
 *
 * Verificado con SHOW CREATE TABLE contra el esquema real antes de escribir
 * esta migración -- ver el down() para la definición exacta de rollback.
 */
return new class extends Migration
{
    /** Orden de DROP: hijos con FK primero, padres después. */
    private const DROP_ORDER = [
        'ticket_assigns',
        'ticket_comments',
        'ticket_histories',
        'ticket_notes',
        'tickets',
        'ticket_categories',
        'ticket_priorities',
        'ticket_status',
        'ticket_canneds',
        'ticket_drafts',
        'payment_gateways',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach (self::DROP_ORDER as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        // Padres antes que hijos (orden inverso al up()).
        DB::unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `ticket_priorities` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `slack` varchar(191) NOT NULL,
              `title` varchar(191) NOT NULL,
              `slug` varchar(191) NOT NULL,
              `color` varchar(191) NOT NULL,
              `available` tinyint(4) NOT NULL DEFAULT 1,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `ticket_status` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `slack` varchar(191) NOT NULL,
              `title` varchar(191) NOT NULL,
              `slug` varchar(191) NOT NULL,
              `color` varchar(191) NOT NULL,
              `available` tinyint(4) NOT NULL DEFAULT 1,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `ticket_canneds` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `slack` varchar(45) DEFAULT NULL,
              `title` varchar(255) NOT NULL,
              `messages` longtext NOT NULL,
              `available` tinyint(1) NOT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

            CREATE TABLE IF NOT EXISTS `ticket_drafts` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `ticket_id` varchar(191) DEFAULT NULL,
              `description` longtext DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `payment_gateways` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `identifier` varchar(191) NOT NULL,
              `currency` varchar(191) NOT NULL,
              `title` varchar(191) NOT NULL,
              `description` text NOT NULL,
              `keys` text NOT NULL,
              `modal_name` text NOT NULL,
              `enabled_test` tinyint(4) DEFAULT 0,
              `available` tinyint(4) DEFAULT 0,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `ticket_categories` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `slack` varchar(191) NOT NULL,
              `title` varchar(191) NOT NULL,
              `slug` varchar(191) NOT NULL,
              `available` tinyint(4) NOT NULL DEFAULT 1,
              `priority_id` bigint(20) unsigned NOT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `categories_tickets_priority_id_foreign` (`priority_id`),
              CONSTRAINT `categories_tickets_priority_id_foreign` FOREIGN KEY (`priority_id`) REFERENCES `ticket_priorities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `tickets` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `cust_id` bigint(20) unsigned NOT NULL,
              `user_id` bigint(20) unsigned NOT NULL,
              `priority_id` bigint(20) unsigned NOT NULL,
              `status_id` bigint(20) unsigned NOT NULL,
              `category_id` bigint(20) unsigned NOT NULL,
              `assign_id` bigint(20) DEFAULT NULL,
              `selfassignuser_id` bigint(20) DEFAULT NULL,
              `number` varchar(191) DEFAULT NULL,
              `slack` varchar(45) DEFAULT NULL,
              `subject` varchar(191) NOT NULL,
              `message` longtext NOT NULL,
              `replystatus` varchar(191) DEFAULT NULL,
              `toassignuser_id` bigint(20) NOT NULL,
              `myassignuser_id` bigint(20) NOT NULL,
              `last_reply` datetime DEFAULT NULL,
              `auto_replystatus` datetime DEFAULT NULL,
              `closing_ticket` date DEFAULT NULL,
              `auto_close_ticket` date DEFAULT NULL,
              `closedby_user` varchar(45) DEFAULT NULL,
              `overduestatus` varchar(191) DEFAULT NULL,
              `auto_overdue_ticket` date DEFAULT NULL,
              `employeesreplying` varchar(191) DEFAULT NULL,
              `usernameverify` varchar(191) DEFAULT NULL,
              `emailticketfile` varchar(191) DEFAULT NULL,
              `lastreply_mail` varchar(45) DEFAULT NULL,
              `note` text DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              `deleted_at` varchar(45) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `tickets_ticket_id_unique` (`number`),
              KEY `tickets_cust_id_foreign` (`cust_id`),
              KEY `tickets_user_id_foreign` (`user_id`),
              KEY `tickets_status_id_foreign` (`status_id`),
              KEY `tickets_priority_id_foreign` (`priority_id`),
              KEY `tickets_category_id_foreign` (`category_id`),
              KEY `tickets_status_id_user_id_index` (`status_id`,`user_id`),
              CONSTRAINT `tickets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `ticket_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `tickets_cust_id_foreign` FOREIGN KEY (`cust_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `tickets_priority_id_foreign` FOREIGN KEY (`priority_id`) REFERENCES `ticket_priorities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `tickets_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `ticket_status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `ticket_assigns` (
              `ticket_id` bigint(20) unsigned NOT NULL,
              `toassign_id` bigint(20) unsigned NOT NULL,
              PRIMARY KEY (`toassign_id`,`ticket_id`),
              KEY `ticketassignchildren_ticket_id_foreign` (`ticket_id`),
              CONSTRAINT `ticketassignchildren_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `ticketassignchildren_toassignuser_id_foreign` FOREIGN KEY (`toassign_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `ticket_comments` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `ticket_id` bigint(20) unsigned NOT NULL,
              `cust_id` bigint(20) unsigned NOT NULL,
              `user_id` bigint(20) unsigned NOT NULL,
              `comment` longtext NOT NULL,
              `display` int(11) DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `ticket_comments_ticket_id_index` (`ticket_id`),
              KEY `ticket_comments_cust_id_foreign` (`cust_id`),
              KEY `ticket_comments_user_id_foreign` (`user_id`),
              CONSTRAINT `ticket_comments_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `ticket_comments_cust_id_foreign` FOREIGN KEY (`cust_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `ticket_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `ticket_histories` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `ticket_id` bigint(20) unsigned NOT NULL,
              `ticketactions` longtext DEFAULT NULL,
              `ticketstatus` longtext DEFAULT NULL,
              `deleted_at` timestamp NULL DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `tickethistories_ticket_id_foreign` (`ticket_id`),
              CONSTRAINT `tickethistories_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `ticket_notes` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `ticket_id` bigint(20) unsigned NOT NULL,
              `user_id` bigint(20) unsigned NOT NULL,
              `notes` text NOT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `ticketnotes_user_id_foreign` (`user_id`),
              KEY `ticketnotes_ticket_id_foreign` (`ticket_id`),
              CONSTRAINT `ticketnotes_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `ticketnotes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);

        Schema::enableForeignKeyConstraints();
    }
};
