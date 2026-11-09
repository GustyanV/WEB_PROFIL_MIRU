<?php
namespace App\Support;

use Illuminate\Support\Facades\DB;

class DatabaseSearch
{
    public static function operator(): string {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}
