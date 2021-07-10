<?php

namespace Rockbuzz\LaraOrders;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;

class ServiceProvider extends SupportServiceProvider
{

    public function boot(Filesystem $filesystem)
    {
        $projectPath = database_path('migrations') . '/';
        $localPath = __DIR__ . '/../database/migrations/';

        if (! $this->hasMigrationInProject($projectPath, $filesystem)) {
            $this->loadMigrationsFrom($localPath . '2021_07_10_000000_create_orders_tables.php');

            $this->publishes([
                $localPath . '2021_07_10_000000_create_orders_tables.php' =>
                    $projectPath . now()->format('Y_m_d_his') . '_create_orders_tables.php'
            ], 'migrations');
        }

        $this->publishes([
            __DIR__ . '/../config/orders.php' => config_path('orders.php')
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/orders.php', 'orders');
    }

    private function hasMigrationInProject(string $path, Filesystem $filesystem)
    {
        return count($filesystem->glob($path . '*_create_orders_tables.php')) > 0;
    }
}
