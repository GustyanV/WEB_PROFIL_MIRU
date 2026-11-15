<?php
namespace App\Providers;

use App\Database\PostgresBooleanConnection;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class PostgresBooleanServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Registered before boot/controllers; physical connections remain lazy.
        Connection::resolverFor('pgsql',static function($pdo,$database,$prefix,array $config) {
            return new PostgresBooleanConnection($pdo,$database,$prefix,$config);
        });

        // Laravel primitive casts are applied on read, not on assignment. Normalize
        // validated 0/1 inputs only for declared Boolean attributes on pgsql saves.
        $this->app['events']->listen('eloquent.saving: *',static function($event,array $payload) {
            $model=$payload[0] ?? null;
            if (!$model instanceof Model || $model->getConnection()->getDriverName()!=='pgsql') return;
            $attributes=$model->getAttributes();
            foreach ($model->getCasts() as $key=>$cast) {
                if (in_array($cast,['bool','boolean'],true) && array_key_exists($key,$attributes)
                    && in_array($attributes[$key],[0,1,'0','1'],true)) {
                    $model->setAttribute($key,(bool)$attributes[$key]);
                }
            }
        });
    }
}