<?php

namespace App\Providers;

use Config;
use DB;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    public function register()
    {
        try {
            DB::connection()->getPdo();
            if (! DB::getSchemaBuilder()->hasTable('settings')) {

                return;
            } else {

                $config = [
                    'driver' => @(DB::table('settings')->where('key', 'mail_driver')->first()->value),
                    'host' => @(DB::table('settings')->where('key', 'mail_host')->first()->value),
                    'port' => @(DB::table('settings')->where('key', 'mail_port')->first()->value),
                    'from' => @['address' => @(DB::table('settings')->where('key', 'mail_from_address')->first()->value), 'name' => @(DB::table('settings')->where('key', 'mail_from_name')->first()->value)],
                    'encryption' => @(DB::table('settings')->where('key', 'mail_encryption')->first()->value),
                    'username' => @(DB::table('settings')->where('key', 'mail_username')->first()->value),
                    'password' => @(DB::table('settings')->where('key', 'mail_password')->first()->value),
                    'sendmail' => @'/usr/sbin/sendmail -bs',
                    'pretend' => @false,
                ];
                Config::set('mail', $config);
            }
        } catch (\Exception $e) {
            return;
            exit('Could not connect to the database.  Please check your configuration. error:'.$e);
        }
    }

    public function boot()
    {
        //
    }
}
