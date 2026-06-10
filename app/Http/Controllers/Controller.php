<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function availableOptions(bool $withBlank = false): Collection
    {
        $options = collect(['1' => 'Publico', '0' => 'Oculto']);

        if ($withBlank) {
            $options->prepend('', '');
        }

        return $options;
    }

    public function generate_slack($table)
    {
        do {
            $slack = Str::random(6);
            $exist = DB::table($table)->where('slack', $slack)->exists();
        } while ($exist);

        return $slack;
    }

    public function generate_number($table)
    {
        $lastId = DB::table($table)->max('id');

        return $lastId ? $lastId + 1 : 1;

    }
}
