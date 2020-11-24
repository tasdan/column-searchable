# Searching in model's columns for Laravel 5.5-8

With this package you can easily search in eloquent model's column in Laravel.

# Setup

## Installation

Install the package with composer require command:
```sh
composer require "tasdan/column-searchable":"^1.0"
```

Next, add the new provider to the `providers` array in `config/app.php` (only when Laravel < 5.5):
```php
'providers' => [
    // ...
    Tasdan\ColumnSearchable\ColumnSearchableServiceProvider::class,
    // ...
],
```

# Usage
To use searchable scope, you have to add **Searchable** trait inside the *Eloquent* models. 
**Searchable** trait adds Searchable scope to the models.
Then define `$searchable` array in model's definition (check the configuration section to more info):

```php
use Tasdan\ColumnSearchable\Searchable;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, Searchable;
    // ...

    public $searchable = [
        'search_field_name_1' => [
            'relation' => 'relation_table_name',
            'columns' => 'column_name',
            'type' => 'string/int'
        ],
        'search_field_name_2' => [
            'relation' => 'relation_table_name',
            'columns' => 'column_name',
            'type' => 'string/int'
        ],
    // ...
}
```

## Configuration


## Blade Extension
