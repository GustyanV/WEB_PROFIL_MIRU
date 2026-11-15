<?php
namespace App\Database;

use Illuminate\Database\PostgresConnection;

/** PostgreSQL accepts these bound string representations in Boolean contexts. */
class PostgresBooleanConnection extends PostgresConnection
{
    public function prepareBindings(array $bindings)
    {
        foreach ($bindings as $key=>$value) {
            if (is_bool($value)) $bindings[$key]=$value ? 'true' : 'false';
        }
        // Preserve Laravel date formatting and handling of every non-Boolean value.
        return parent::prepareBindings($bindings);
    }
}