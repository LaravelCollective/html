<?php

// add attributes in the following html tags
// and those attributes will be automatically
// added in the generated html element
// eg 'button' => ['class' => 'btn btn-primary']

return [

	// Html facade elements
	'html' => [
		'script'  => [],
		'style'   => [],
		'image'   => [],
		'favicon' => [],
		'link'    => [],
		'mailto'  => [],
		'dl'      => [],
		'listing' => [],
		'meta'    => [],
		'tag'     => [],
	],

	// Form facade elements
	'form' => [
		'open'          => [],
		'label'         => [],
		'input'         => [],
		'text'          => [],
		'password'      => [],
		'submit'        => [],
		'email'         => [],
		'tel'           => [],
		'number'        => [],
		'date'          => [],
		'datetime'      => [],
		'datetimeLocal' => [],
		'time'          => [],
		'url'           => [],
		'file'          => [],
		'textarea'      => [],
		'select'        => [],
		'option'        => [],
		'checkable'     => [],
		'reset'         => [],
		'image'         => [],
		'color'         => [],
		'submit'        => [],
		'button'        => [],
	],

	// Group
	// these element groups can be added in any html tag
	// as attribute which will add the defined group of
	// attributes in the element
	// eg. {!! Form::open(url('/login'), ['group' => 'login-form']) !!} will generate
	// {!! Form::open(url('/login'), ['class' => 'form form--full-width form--login']) !!}
	'group' => [
		// add custom grouped elements
		// eg. 'login-form' => ['class' => 'form form--full-width form--login']
	],

];