<?php

namespace TasDan\ColumnSearchable;

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
        //
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
    }
}
