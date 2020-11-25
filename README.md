# Searching in model's columns for Laravel 5.5-8
A package for searching in your eloquent model's column, with relations in Laravel.

[![Total Downloads](https://poser.pugx.org/tasdan/column-searchable/downloads)](//packagist.org/packages/tasdan/column-searchable)
[![Latest Stable Version](https://poser.pugx.org/tasdan/column-searchable/v)](//packagist.org/packages/tasdan/column-searchable)
[![Latest Unstable Version](https://poser.pugx.org/tasdan/column-searchable/v/unstable)](//packagist.org/packages/tasdan/column-searchable)
[![License](https://poser.pugx.org/tasdan/column-searchable/license)](//packagist.org/packages/tasdan/column-searchable)


#### Table of contents
- [Installation](#installation)
    - [Composer](#composer)
    - [Service provider](#service-provider)
- [Usage](#usage)
- [Configurations](#configurations)
    - [Config examples](#config-examples)
- [Blade Extension](#blade-extension)
    - [Searchable input field](#searchable-input-field)
    - [Searchable script](#searchable-script)
- [Pagination](#pagination)
- [Full example](#full-example)
- [Library Note](#library-note)
- [License](#license) 

## Installation

### Composer
Install the package with composer require command:
```sh
composer require "tasdan/column-searchable"
```
### Service provider
Next, add the new provider to the `providers` array in `config/app.php` (only when Laravel < 5.5):
```php
'providers' => [
    // ...
    /**
     * Third Party Service Providers...
     */
    Tasdan\ColumnSearchable\ColumnSearchableServiceProvider::class,
    // ...
],
```

## Usage
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

After you defined the **$searchable** array, you can use **searchable** scope in your controllers
```php
 $users = User::searchable()->paginate(15);
```

You can execute a search function when the requested URL has one of the defined **$searchable** key, see this example:
```URL
https://yourdomain.com/users?search_field_name_1=test%20user
``` 
>**Note**: the parameter values in URL are URL-encoded.


## Configurations
You can configure search field as much as you want, with the following pattern:
- **search_field_name_1**: the name of this search field, can be anything, or the column name where you want to search
- **relation**: the related table name, can be omitted, or the current model's table name
- **columns**: the column name(s) where you want to search, use an array, when you want to search in multiple columns
- **type**: the column type, must be string or int (if string, the query be like 'LIKE %xyz%'), can be omitted

###### Config examples
```php
 public $searchable = [
        //search in name column in current table with string type 
        'name',

        //search in selected columns with OR relation in current table with string type
        'address' => [
            'columns' => [
                'country',
                'city',
                'zip_code',
                'street'
            ]
        ],
        
        //search in activation_code column in current table with string type, but the search field name is activation
        'activation' => [
            'columns' => 'activation_code'
        ],
       
        //search in is_active column in current table with int type
        'is_active' => [
            'type' => 'int'
        ],
        
        //search in a belongs-to related table's name column, with string type
        'school_name' => [
            'relation' => 'school',
            'columns' => 'name',
            'type' => 'string'
        ],
    
        //search in a belongs-to related table's city and street column with string type'
        'school_address' => [
            'relation' => 'school',
            'columns' => [
                'city',
                'street'
            ],
            'type' => 'string'
        ],   

        //search in a has-many related table's firstname and lastname column with string type
        'student_name' => [
            'relation' => 'students',
            'columns' => [
                'firstname',
                'lastname'
            ],
            'type' => 'string'     
        ]     
    ];
```

## Blade Extension
There are two blade extension for you to use searchable functions

###### Searchable input field
In blade files you can use the **@searchablefield()** extension to automatically generated search input form.
```blade
@searchablefield('field_type', 'search_field_name', 'Title', $selectOptionsArray, ['class' => 'form-control'])
```
>**Note**: the search-input class automatically added to this generated fields, to able to work the **@searchablescript()** extension.

This first parameter (**field_type**) must be one of the following:
- text
- select

The second parameter is the searchable field name, which is already defined in model's **$searchable** array.
The third parameter is the title-placeholder value of the field. The fourth parameter depends on the type parameter:
- if type is text, this is an array with additional form values (optional)
- if type is select, this is an array with select options key-value pairs

The fifth parameter is optional when type is select, and this is an array with additional form values.

Possible examples and usages of this balde extension:
```blade
@searchablefield('text', 'name', __('fields.name'))
@searchablefield('text', 'location', __('fields.location'), ['class' => 'form-control form-control-sm'])
@searchablefield('select', 'is_active', __('fields.active'), $activeSelectOptions)
@searchablefield('select', 'position', __('fields.position'), ["1" => "POS 1", "2" => "POS 2"] ,['class' => 'form-control'])
```
###### Searchable script
In blade files you can use the **@searchablescript()** extension to automatically generated search Javascript functions. 
After this you can call **search()** function to execute a search request.

Example use of **@searchablescript()** extension:
```javascript 1.8
<script>
    @searchablescript()
</script>
// ...
<script>
    $("#searchButton").click(function () {
        search();
    });
</script>
```

>**Note**: you have to call **@searchablescript()** extension inside script tag, because this extension returns only with JS functions code. In this way, you can use CSP-nonce on this include. 

## Pagination
If you use pagination in blade, you have to update the pagination links, to keep searching parameters in URL when page change.
```blade
{!! $users->appends(\Request::except('page'))->render() !!}
```

## Full example
**_TODO_**

## Library Note:
This is my first Laravel package, so maybe there is some problem with that, but I will handle those as soon as possible.

## License
This package is free software distributed under the terms of the [MIT license](https://opensource.org/licenses/MIT). Enjoy!


