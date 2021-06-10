<?php

namespace Tasdan\ColumnSearchable;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class ColumnSearchableServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/columnsearchable.php', 'columnsearchable');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('searchablefield', function ($expression) {
            $expression = ($expression[0] === '(') ? substr($expression, 1, -1) : $expression;

            return "<?php echo \Tasdan\ColumnSearchable\SearchableField::render(array ({$expression}));?>";
        });

        Blade::directive('searchablescript', function () {
            return "<?php echo \Tasdan\ColumnSearchable\SearchableScript::render();?>";
        });

        $this->publishes([
            __DIR__.'/../config/columnsearchable.php' => config_path('columnsearchable.php'),
        ], 'config');
    }
}
