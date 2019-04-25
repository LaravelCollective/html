# Forms & HTML

[![Build Status](https://travis-ci.org/LaravelCollective/html.svg)](https://travis-ci.org/LaravelCollective/html)
[![Total Downloads](https://poser.pugx.org/LaravelCollective/html/downloads)](https://packagist.org/packages/laravelcollective/html)
[![Latest Stable Version](https://poser.pugx.org/LaravelCollective/html/v/stable.svg)](https://packagist.org/packages/laravelcollective/html)
[![Latest Unstable Version](https://poser.pugx.org/LaravelCollective/html/v/unstable.svg)](https://packagist.org/packages/laravelcollective/html)
[![License](https://poser.pugx.org/LaravelCollective/html/license.svg)](https://packagist.org/packages/laravelcollective/html)

# Forms & HTML

- [Installation](#installation)
- [Opening A Form](#opening-a-form)
- [CSRF Protection](#csrf-protection)
- [Form Model Binding](#form-model-binding)
- [Form Model Accessors](#form-model-accessors)
- [Labels](#labels)
- [Text, Text Area, Password & Hidden Fields](#text)
- [Checkboxes and Radio Buttons](#checkboxes-and-radio-buttons)
- [File Input](#file-input)
- [Number Input](#number)
- [Date Input](#date)
- [Drop-Down Lists](#drop-down-lists)
- [Buttons](#buttons)
- [Custom Macros](#custom-macros)
- [Custom Components](#custom-components)
- [Generating URLs](#generating-urls)

<a name="installation"></a>
## Installation

Begin by installing this package through Composer. Edit your project's `composer.json` file to require `laravelcollective/html`.

    composer require "laravelcollective/html":"^5.8.0"

Next, add your new provider to the `providers` array of `config/app.php`:

```php
  'providers' => [
    // ...
    Collective\Html\HtmlServiceProvider::class,
    // ...
  ],
```

Finally, add two class aliases to the `aliases` array of `config/app.php`:

```php
  'aliases' => [
    // ...
      'Form' => Collective\Html\FormFacade::class,
      'Html' => Collective\Html\HtmlFacade::class,
    // ...
  ],
```

> Looking to install this package in <a href="http://lumen.laravel.com" target="\_blank">Lumen</a>? First of all, making this package compatible with Lumen will require some core changes to Lumen, which we believe would dampen the effectiveness of having Lumen in the first place. Secondly, it is our belief that if you need this package in your application, then you should be using Laravel anyway.

<a name="opening-a-form"></a>
## Opening A Form

#### Opening A Form

```php
{{ Form::open(['url' => 'foo/bar']) }}
	//
{{ Form::close() }}
```

By default, a `POST` method will be assumed; however, you are free to specify another method:

```php
echo Form::open(['url' => 'foo/bar', 'method' => 'put'])
```

> **Note:** Since HTML forms only support `POST` and `GET`, `PUT` and `DELETE` methods will be spoofed by automatically adding a `_method` hidden field to your form.

You may also open forms that point to named routes or controller actions:

```php
echo Form::open(['route' => 'route.name'])

echo Form::open(['action' => 'Controller@method'])
```

You may pass in route parameters as well:

```php
echo Form::open(['route' => ['route.name', $user->id]])

echo Form::open(['action' => ['Controller@method', $user->id]])
```

If your form is going to accept file uploads, add a `files` option to your array:

```php
echo Form::open(['url' => 'foo/bar', 'files' => true])
```

<a name="csrf-protection"></a>
## CSRF Protection

#### Adding The CSRF Token To A Form

Laravel provides an easy method of protecting your application from cross-site request forgeries. First, a random token is placed in your user's session. If you use the `Form::open` method with `POST`, `PUT` or `DELETE` the CSRF token will be added to your forms as a hidden field automatically. Alternatively, if you wish to generate the HTML for the hidden CSRF field, you may use the `token` method:

```php
echo Form::token();
```

#### Attaching The CSRF Filter To A Route

```php
Route::post('profile',
    [
        'before' => 'csrf',
        function()
        {
            //
        }
    ]
);
```

<a name="form-model-binding"></a>
## Form Model Binding

#### Opening A Model Form

Often, you will want to populate a form based on the contents of a model. To do so, use the `Form::model` method:

```php
echo Form::model($user, ['route' => ['user.update', $user->id]])
```

Now, when you generate a form element, like a text input, the model's value matching the field's name will automatically be set as the field value. So, for example, for a text input named `email`, the user model's `email` attribute would be set as the value. However, there's more! If there is an item in the Session flash data matching the input name, that will take precedence over the model's value. So, the priority looks like this:

1. Session Flash Data ([Old Input](https://laravel.com/docs/requests#old-input))
2. Data From Current [Request](https://laravel.com/docs/requests) (via `Request::input` method)
3. Explicitly Passed Value
4. Model Attribute Data

This allows you to quickly build forms that not only bind to model values, but easily re-populate if there is a validation error on the server!

> **Note:** When using `Form::model`, be sure to close your form with `Form::close`!

<a name="form-model-accessors"></a>
#### Form Model Accessors

Laravel's [Eloquent Accessor](http://laravel.com/docs/5.2/eloquent-mutators#accessors-and-mutators) allow you to manipulate a model attribute before returning it. This can be extremely useful for defining global date formats, for example. However, the date format used for display might not match the date format used for form elements. You can solve this by creating two separate accessors: a standard accessor, *and/or* a form accessor.

To define a form accessor add the `FormAccessible` trait to your model and create a `formFooAttribute` method on your model where `Foo` is the "camel" cased name of the column you wish to access. In this example, we'll define an accessor for the `date_of_birth` attribute. The accessor will automatically be called by the HTML Form Builder when attempting to pre-fill a form field when `Form::model()` is used.

```php
<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Collective\Html\Eloquent\FormAccessible;

class User extends Model
{
    use FormAccessible;

    /**
     * Get the user's date of birth.
     *
     * @param  string  $value
     * @return string
     */
    public function getDateOfBirthAttribute($value)
    {
        return Carbon::parse($value)->format('m/d/Y');
    }

    /**
     * Get the user's date of birth for forms.
     *
     * @param  string  $value
     * @return string
     */
    public function formDateOfBirthAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }
}
```

<a name="labels"></a>
## Labels

#### Generating A Label Element

```php
echo Form::label('email', 'E-Mail Address');
```

#### Specifying Extra HTML Attributes

```php
echo Form::label('email', 'E-Mail Address', ['class' => 'awesome']);
```

> **Note:** After creating a label, any form element you create with a name matching the label name will automatically receive an ID matching the label name as well.

<a name="text"></a>
## Text, Text Area, Password & Hidden Fields

#### Generating A Text Input

```php
echo Form::text('username');
```

#### Specifying A Default Value

```php
echo Form::text('email', 'example@gmail.com');
```

> **Note:** The *hidden* and *textarea* methods have the same signature as the *text* method.

#### Generating A Password Input

```php
echo Form::password('password', ['class' => 'awesome']);
```

#### Generating Other Inputs

```php
echo Form::email($name, $value = null, $attributes = []);
echo Form::file($name, $attributes = []);
```

<a name="checkboxes-and-radio-buttons"></a>
## Checkboxes and Radio Buttons

#### Generating A Checkbox Or Radio Input

```php
echo Form::checkbox('name', 'value');

echo Form::radio('name', 'value');
```

#### Generating A Checkbox Or Radio Input That Is Checked

```php
echo Form::checkbox('name', 'value', true);

echo Form::radio('name', 'value', true);
```

<a name="number"></a>
## Number

#### Generating A Number Input

```php
echo Form::number('name', 'value');
```

<a name="date"></a>
## Date

#### Generating A Date Input

```php
echo Form::date('name', \Carbon\Carbon::now());
```

<a name="file-input"></a>
## File Input

#### Generating A File Input

```php
echo Form::file('image');
```

> **Note:** The form must have been opened with the `files` option set to `true`.

<a name="drop-down-lists"></a>
## Drop-Down Lists

#### Generating A Drop-Down List

```php
echo Form::select('size', ['L' => 'Large', 'S' => 'Small']);
```

#### Generating A Drop-Down List With Selected Default

```php
echo Form::select('size', ['L' => 'Large', 'S' => 'Small'], 'S');
```

#### Generating a Drop-Down List With an Empty Placeholder

This will create an `<option>` element with no value as the very first option of your drop-down.

```php
echo Form::select('size', ['L' => 'Large', 'S' => 'Small'], null, ['placeholder' => 'Pick a size...']);
```

#### Generating A Grouped List

```php
echo Form::select('animal',[
	'Cats' => ['leopard' => 'Leopard'],
	'Dogs' => ['spaniel' => 'Spaniel'],
]);
```

#### Generating A Drop-Down List With A Range

```php
echo Form::selectRange('number', 10, 20);
```

#### Generating A List With Month Names

```php
echo Form::selectMonth('month');
```

#### Generating A List From A Model

```php
echo Form::select('size', \App\ShirtSizes::pluck('size','id'), null, ['placeholder' => 'Choose a size'])
```

<a name="buttons"></a>
## Buttons

#### Generating A Submit Button

```php
echo Form::submit('Click Me!');
```

> **Note:** Need to create a button element? Try the *button* method. It has the same signature as *submit*.

<a name="custom-macros"></a>
## Custom Macros

#### Registering A Form Macro

It's easy to define your own custom Form class helpers called "macros". Here's how it works. First, simply register the macro with a given name and a Closure:

```php
Form::macro('myField', function()
{
	return '<input type="awesome">';
});
```

Now you can call your macro using its name:

#### Calling A Custom Form Macro

```php
echo Form::myField();
```

<a name="custom-components"></a>
## Custom Components

#### Registering A Custom Component

Custom Components are similar to Custom Macros, however instead of using a closure to generate the resulting HTML, Components utilize [Laravel Blade Templates](http://laravel.com/docs/5.2/blade). Components can be incredibly useful for developers who use [Twitter Bootstrap](http://getbootstrap.com/), or any other front-end framework, which requires additional markup to properly render forms.

Let's build a Form Component for a simple Bootstrap text input. You might consider registering your Components inside a Service Provider's `boot` method.

```php
Form::component('bsText', 'components.form.text', ['name', 'value', 'attributes']);
```

Notice how we reference a view path of `components.form.text`. Also, the array we provided is a sort of method signature for your Component. This defines the names of the variables that will be passed to your view. Your view might look something like this:

```php
// resources/views/components/form/text.blade.php
<div class="form-group">
    {{ Form::label($name, null, ['class' => 'control-label']) }}
    {{ Form::text($name, $value, array_merge(['class' => 'form-control'], $attributes)) }}
</div>
```

> Custom Components can also be created on the `Html` facade in the same fashion as on the `Form` facade.

##### Providing Default Values

When defining your Custom Component's method signature, you can provide default values simply by giving your array items values, like so:

```php
Form::component('bsText', 'components.form.text', ['name', 'value' => null, 'attributes' => []]);
```

#### Calling A Custom Form Component

Using our example from above (specifically, the one with default values provided), you can call your Custom Component like so:

```php
{{ Form::bsText('first_name') }}
```

This would result in something like the following HTML output:

```php
<div class="form-group">
    <label for="first_name">First Name</label>
    <input type="text" name="first_name" value="" class="form-control">
</div>
```

<a name="generating-urls"></a>
## Generating URLs

#### link_to

Generate a HTML link to the given URL.

```php
echo link_to('foo/bar', $title = null, $attributes = [], $secure = null);
```

#### link_to_asset

Generate a HTML link to the given asset.

```php
echo link_to_asset('foo/bar.zip', $title = null, $attributes = [], $secure = null);
```

#### link_to_route

Generate a HTML link to the given named route.

```php
echo link_to_route('route.name', $title = null, $parameters = [], $attributes = []);
```

#### link_to_action

Generate a HTML link to the given controller action.

```php
echo link_to_action('HomeController@getIndex', $title = null, $parameters = [], $attributes = []);
```
