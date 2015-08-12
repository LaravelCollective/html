# Forms & HTML

[![Build Status](https://travis-ci.org/LaravelCollective/html.svg)](https://travis-ci.org/LaravelCollective/html)
[![Total Downloads](https://poser.pugx.org/LaravelCollective/html/downloads)](https://packagist.org/packages/laravelcollective/html)
[![Latest Stable Version](https://poser.pugx.org/LaravelCollective/html/v/stable.svg)](https://packagist.org/packages/laravelcollective/html)
[![Latest Unstable Version](https://poser.pugx.org/LaravelCollective/html/v/unstable.svg)](https://packagist.org/packages/laravelcollective/html)
[![License](https://poser.pugx.org/LaravelCollective/html/license.svg)](https://packagist.org/packages/laravelcollective/html)

Official documentation for Forms & Html for The Laravel Framework can be found at the [LaravelCollective](http://laravelcollective.com) website.

Added the ability to display messages of Validator:

## Specifying Messages of Validator
    echo Form::text('email', null, null, $errors);
    echo Form::password('password', null, null, $errors);
    echo Form::email('email', null, null, $errors);
    echo Form::textarea('textarea', null, null, $errors);
