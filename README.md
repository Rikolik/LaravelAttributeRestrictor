# LaravelAttributeRestrictor
This package offers an easy way to hide specific attributes from a model to specific situations.
You can hide important data to specific users by using a trait with a couple of methods. Every other place that uses the model's data, will be restricted

## How to use
### Install
Install the package from [packagist](https://packagist.org/packages/enriko/laravel-attribute-restrictor) using composer:
```bash
composer require enriko/laravel-attribute-restrictor
```
(**Recomended**) Publish the config file:
```bash
php artisan vendor:publish --tag=config
```
### Configure
Add the **RestrictedAttributes** Trait to your *model*:
```php
use Enriko\LaravelAttributeRestrictor\Traits\RestrictedAttributes;

class User extends Authenticatable
{
    use RestrictedAttributes;
    // ...
}
```
Choose the attributes you wish to restrict with the **getRestrictedAttributes** method:
```php
public static function getRestrictedAttributes(): array
{
    return [
        'name',
        'email'
    ];
}
```
Define the **restrictions conditions** by:
#### Specific definition using getAttributeRestrictions
If the attribute's condition is ***false***, the attribute will be replaced:
```php
public static function getAttributeRestrictions($attr): callable|bool
{
    return match ($attr) {
        'name' => fn() => Auth::user()->can('see_names'), // varies by user
        'email' => false, // always restricts
        default => true // doesn't restrict
    };
}
```
#### Specific definition using array
Same as the previous options, but expects an array returning method:
```php
public static function getAttributeRestrictionsArray(): array
{
    return [
        'name' => fn() => Auth::user()->can('see_names'), // varies by user
        'email' => false, // always restricts
    ];
}
```
#### Globally restriction
Defines a single restriction, if it's false, ***all*** attributes defined on **getRestrictedAttributes** will be restricted
```php
public static function getGlobalRestriction() : callable|bool {
    return Auth::user()->can('see_sensitive_data');
}
```

### Define the substitute text
If the attribute is restricted, you can return something else in it's place.
If you **published** the *config* file, you can modify it on:
> root\config\enriko\attribute-restriction.php

But, you can also set individual messages for the model using the **getReplacedText** method:
```php
private function getReplacedText()
{
    return 'You can't see user information';
}
```
### Get the data
Finally, when you **get** the model's data, it will validate the restrictions. If your user pass, it'll see the info, unchanged. If not, it'll be replaced by the substitute restriction text.
#### Examples
Imagine a User model with the following data:

| id  | name | account_number |
| --- | ---- | -------------- |
|  1  | Foo  | 12345          |
|  2  | Bar  | 55555          |
|  3  | Baz  | 00220          |

Normally, if you use **$User->get()**, you will get:
```json
{
    id: 1,
    name: 'Foo',
    account_number: '12345'
},
{
    id: 2,
    name: 'Bar',
    account_number: '55555'
}
{
    id: 3,
    name: 'Baz',
    account_number: '00220'
}
```
Now, let's say we configure the **account_number** as restricted. If the user can't access this information, **$User->get()** will return:
```json
{
    id: 1,
    name: 'Foo',
    account_number: 'Restricted'
},
{
    id: 2,
    name: 'Bar',
    account_number: 'Restricted'
}
{
    id: 3,
    name: 'Baz',
    account_number: 'Restricted'
}
```
Same thing for using **$User->only(['name', 'account_number'])** and **$User->account_number**.
#### Get *original* data
Sometimes, you might want to deal with the data, in this case, you don't want it to be substituted by some generic text.

Fundamentally, the restriction works as another cast, so you can ignore with by using **getRawOriginal**:
```php
// $User = 'Foo'
$User->getRawOriginal('account_number'); // will return 12345, no matter what
```