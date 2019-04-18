<?php

use Collective\Html\FormBuilder;
use Collective\Html\HtmlBuilder;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Session\Store;
use Mockery as m;

class FormBuilderTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var FormBuilder
     */
    protected $formBuilder;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->urlGenerator = new UrlGenerator(new RouteCollection(), Request::create('/foo', 'GET'));
        $this->viewFactory = m::mock(Factory::class);
        $this->htmlBuilder = new HtmlBuilder($this->urlGenerator, $this->viewFactory);

        // prepare request for test with some data
        $request = Request::create('/foo', 'GET', [
            "person" => [
                "name" => "John",
                "surname" => "Doe",
            ],
            "agree" => 1,
            "checkbox_array" => [1, 2, 3],
        ]);

        $request = Request::createFromBase($request);

        $this->formBuilder = new FormBuilder($this->htmlBuilder, $this->urlGenerator, $this->viewFactory, 'abc', $request);
    }

    /**
     * Destroy the test environment.
     */
    public function tearDown()
    {
        m::close();
    }

    public function testRequestValue()
    {
        $this->formBuilder->considerRequest();
        $name = $this->formBuilder->text("person[name]", "Not John");
        $surname = $this->formBuilder->text("person[surname]", "Not Doe");
        $this->assertEquals('<input name="person[name]" type="text" value="John">', $name);
        $this->assertEquals('<input name="person[surname]" type="text" value="Doe">', $surname);

        $checked = $this->formBuilder->checkbox("agree", 1);
        $unchecked = $this->formBuilder->checkbox("no_value", 1);
        $this->assertEquals('<input checked="checked" name="agree" type="checkbox" value="1">', $checked);
        $this->assertEquals('<input name="no_value" type="checkbox" value="1">', $unchecked);

        $checked_array = $this->formBuilder->checkbox("checkbox_array[]", 1);
        $unchecked_array = $this->formBuilder->checkbox("checkbox_array[]", 4);
        $this->assertEquals('<input checked="checked" name="checkbox_array[]" type="checkbox" value="1">', $checked_array);
        $this->assertEquals('<input name="checkbox_array[]" type="checkbox" value="4">', $unchecked_array);

        $checked = $this->formBuilder->radio("agree", 1);
        $unchecked = $this->formBuilder->radio("no_value", 1);
        $this->assertEquals('<input checked="checked" name="agree" type="radio" value="1">', $checked);
        $this->assertEquals('<input name="no_value" type="radio" value="1">', $unchecked);

        // now we check that Request is ignored and value take precedence
        $this->formBuilder->considerRequest(false);
        $name = $this->formBuilder->text("person[name]", "Not John");
        $surname = $this->formBuilder->text("person[surname]", "Not Doe");
        $this->assertEquals('<input name="person[name]" type="text" value="Not John">', $name);
        $this->assertEquals('<input name="person[surname]" type="text" value="Not Doe">', $surname);
    }

    public function testOpeningForm()
    {
        $form1 = $this->formBuilder->open(['method' => 'GET']);
        $form2 = $this->formBuilder->open(['method' => 'POST', 'class' => 'form', 'id' => 'id-form']);
        $form3 = $this->formBuilder->open(['method' => 'GET', 'accept-charset' => 'UTF-16']);
        $form4 = $this->formBuilder->open(['method' => 'GET', 'accept-charset' => 'UTF-16', 'files' => true]);
        $form5 = $this->formBuilder->open(['method' => 'PUT']);

        $this->assertEquals('<form method="GET" action="http://localhost/foo" accept-charset="UTF-8">', $form1);
        $this->assertEquals('<form method="POST" action="http://localhost/foo" accept-charset="UTF-8" class="form" id="id-form"><input name="_token" type="hidden" value="abc">',
          $form2);
        $this->assertEquals('<form method="GET" action="http://localhost/foo" accept-charset="UTF-16">', $form3);
        $this->assertEquals('<form method="GET" action="http://localhost/foo" accept-charset="UTF-16" enctype="multipart/form-data">',
          $form4);
        $this->assertEquals('<form method="POST" action="http://localhost/foo" accept-charset="UTF-8"><input name="_method" type="hidden" value="PUT"><input name="_token" type="hidden" value="abc">',
          $form5);
    }

    public function testClosingForm()
    {
        $this->assertEquals('</form>', $this->formBuilder->close());
    }

    public function testFormLabel()
    {
        $form1 = $this->formBuilder->label('foo', 'Foobar');
        $form2 = $this->formBuilder->label('foo', 'Foobar', ['class' => 'control-label']);
        $form3 = $this->formBuilder->label('foo', 'Foobar <i>bar</i>', null, false);

        $this->assertEquals('<label for="foo">Foobar</label>', $form1);
        $this->assertEquals('<label for="foo" class="control-label">Foobar</label>', $form2);
        $this->assertEquals('<label for="foo">Foobar <i>bar</i></label>', $form3);
    }

    public function testFormInput()
    {
        $form1 = $this->formBuilder->input('text', 'foo');
        $form2 = $this->formBuilder->input('text', 'foo', 'foobar');
        $form3 = $this->formBuilder->input('date', 'foobar', null, ['class' => 'span2']);
        $form4 = $this->formBuilder->input('hidden', 'foo', true);
        $form6 = $this->formBuilder->input('checkbox', 'foo-check', true);

        $this->assertEquals('<input name="foo" type="text">', $form1);
        $this->assertEquals('<input name="foo" type="text" value="foobar">', $form2);
        $this->assertEquals('<input class="span2" name="foobar" type="date">', $form3);
        $this->assertEquals('<input name="foo" type="hidden" value="1">', $form4);
        $this->assertEquals('<input name="foo-check" type="checkbox" value="1">', $form6);
    }

    public function testMacroField()
    {
        $this->formBuilder->macro('data_field', function ($name, $value, $data) {
            $dataAttributes = [];
            foreach ($data as $key => $attribute) {
                $dataAttributes[] = $key.'="'.$attribute.'"';
            }
            return '<input name="'.$name.'" type="text" value="'.$value.'" '.implode(' ', $dataAttributes).'>';
        });

        $form = $this->formBuilder->data_field('foo', null, [
            'role' => 'set_name',
            'data-titlecase' => 'ucfirst',
            'data-inputmask-type' => 'Regex',
            'data-inputmask-regex' => '[A-Za-z0-9\\s-\\(\\)&]{2,70}',
        ]);

        $this->assertEquals('<input name="foo" type="text" value="" role="set_name" data-titlecase="ucfirst" data-inputmask-type="Regex" data-inputmask-regex="[A-Za-z0-9\s-\(\)&]{2,70}">', $form);
    }

    public function testPasswordsNotFilled()
    {
        $this->formBuilder->setSessionStore($session = m::mock('Illuminate\Contracts\Session\Session'));

        $session->shouldReceive('getOldInput')->never();

        $form1 = $this->formBuilder->password('password');

        $this->assertEquals('<input name="password" type="password" value="">', $form1);
    }

    public function testFilesNotFilled()
    {
        $this->formBuilder->setSessionStore($session = m::mock('Illuminate\Contracts\Session\Session'));

        $session->shouldReceive('getOldInput')->never();

        $form = $this->formBuilder->file('img');

        $this->assertEquals('<input name="img" type="file">', $form);
    }

    public function testFormText()
    {
        $form1 = $this->formBuilder->input('text', 'foo');
        $form2 = $this->formBuilder->text('foo');
        $form3 = $this->formBuilder->text('foo', 'foobar');
        $form4 = $this->formBuilder->text('foo', null, ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="text">', $form1);
        $this->assertEquals($form1, $form2);
        $this->assertEquals('<input name="foo" type="text" value="foobar">', $form3);
        $this->assertEquals('<input class="span2" name="foo" type="text">', $form4);
    }

    public function testFormTextArray()
    {
        $form1 = $this->formBuilder->input('text', 'foo[]', 'testing');
        $form2 = $this->formBuilder->text('foo[]');

        $this->assertEquals('<input name="foo[]" type="text" value="testing">', $form1);
        $this->assertEquals('<input name="foo[]" type="text">', $form2);
    }

    public function testFormTextRepopulation()
    {
        $this->formBuilder->setSessionStore($session = m::mock('Illuminate\Contracts\Session\Session'));
        $this->setModel($model = ['relation' => ['key' => 'attribute'], 'other' => 'val']);

        $session->shouldReceive('getOldInput')->once()->with('name_with_dots')->andReturn('some value');
        $input = $this->formBuilder->text('name.with.dots', 'default value');
        $this->assertEquals('<input name="name.with.dots" type="text" value="some value">', $input);

        $session->shouldReceive('getOldInput')->once()->with('text.key.sub')->andReturn(null);
        $input = $this->formBuilder->text('text[key][sub]', 'default value');
        $this->assertEquals('<input name="text[key][sub]" type="text" value="default value">', $input);

        $session->shouldReceive('getOldInput')->with('relation.key')->andReturn(null);
        $input1 = $this->formBuilder->text('relation[key]');

        $this->setModel($model, false);
        $input2 = $this->formBuilder->text('relation[key]');

        $this->assertEquals('<input name="relation[key]" type="text" value="attribute">', $input1);
        $this->assertEquals($input1, $input2);
    }

    public function testFormRepopulationWithMixOfArraysAndObjects()
    {
        $this->formBuilder->model(['user' => (object) ['password' => 'apple']]);
        $input = $this->formBuilder->text('user[password]');
        $this->assertEquals('<input name="user[password]" type="text" value="apple">', $input);

        $this->formBuilder->model((object) ['letters' => ['a', 'b', 'c']]);
        $input = $this->formBuilder->text('letters[1]');
        $this->assertEquals('<input name="letters[1]" type="text" value="b">', $input);
    }

    public function testFormPassword()
    {
        $form1 = $this->formBuilder->password('foo');
        $form2 = $this->formBuilder->password('foo', ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="password" value="">', $form1);
        $this->assertEquals('<input class="span2" name="foo" type="password" value="">', $form2);
    }

    public function testFormRange()
    {
        $form1 = $this->formBuilder->range('foo');
        $form2 = $this->formBuilder->range('foo', 1);
        $form3 = $this->formBuilder->range('foo', null, ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="range">', $form1);
        $this->assertEquals('<input name="foo" type="range" value="1">', $form2);
        $this->assertEquals('<input class="span2" name="foo" type="range">', $form3);
    }

    public function testFormHidden()
    {
        $form1 = $this->formBuilder->hidden('foo');
        $form2 = $this->formBuilder->hidden('foo', 'foobar');
        $form3 = $this->formBuilder->hidden('foo', null, ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="hidden">', $form1);
        $this->assertEquals('<input name="foo" type="hidden" value="foobar">', $form2);
        $this->assertEquals('<input class="span2" name="foo" type="hidden">', $form3);
    }

    public function testFormMonth()
    {
        $form1 = $this->formBuilder->month('foo');
        $form2 = $this->formBuilder->month('foo', \Carbon\Carbon::now());
        $form3 = $this->formBuilder->month('foo', null, ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="month">', $form1);
        $this->assertEquals('<input name="foo" type="month" value="' . \Carbon\Carbon::now()->format('Y-m') . '">',
          $form2);
        $this->assertEquals('<input class="span2" name="foo" type="month">', $form3);
    }

    public function testFormSearch()
    {
        $form1 = $this->formBuilder->search('foo');
        $form2 = $this->formBuilder->search('foo', 'foobar');
        $form3 = $this->formBuilder->search('foo', null, ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="search">', $form1);
        $this->assertEquals('<input name="foo" type="search" value="foobar">', $form2);
        $this->assertEquals('<input class="span2" name="foo" type="search">', $form3);
    }

    public function testFormEmail()
    {
        $form1 = $this->formBuilder->email('foo');
        $form2 = $this->formBuilder->email('foo', 'foobar');
        $form3 = $this->formBuilder->email('foo', null, ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="email">', $form1);
        $this->assertEquals('<input name="foo" type="email" value="foobar">', $form2);
        $this->assertEquals('<input class="span2" name="foo" type="email">', $form3);
    }

    public function testFormTel()
    {
        $form1 = $this->formBuilder->tel('foo');
        $form2 = $this->formBuilder->tel('foo', 'foobar');
        $form3 = $this->formBuilder->tel('foo', null, ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="tel">', $form1);
        $this->assertEquals('<input name="foo" type="tel" value="foobar">', $form2);
        $this->assertEquals('<input class="span2" name="foo" type="tel">', $form3);
    }

    public function testFormNumber()
    {
        $form1 = $this->formBuilder->number('foo');
        $form2 = $this->formBuilder->number('foo', 1);
        $form3 = $this->formBuilder->number('foo', null, ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="number">', $form1);
        $this->assertEquals('<input name="foo" type="number" value="1">', $form2);
        $this->assertEquals('<input class="span2" name="foo" type="number">', $form3);
    }

    public function testFormDate()
    {
        $form1 = $this->formBuilder->date('foo');
        $form2 = $this->formBuilder->date('foo', '2015-02-20');
        $form3 = $this->formBuilder->date('foo', \Carbon\Carbon::now());
        $form4 = $this->formBuilder->date('foo', null, ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="date">', $form1);
        $this->assertEquals('<input name="foo" type="date" value="2015-02-20">', $form2);
        $this->assertEquals('<input name="foo" type="date" value="' . \Carbon\Carbon::now()->format('Y-m-d') . '">',
          $form3);
        $this->assertEquals('<input class="span2" name="foo" type="date">', $form4);
    }

    public function testFormTime()
    {
        $form1 = $this->formBuilder->time('foo');
        $form2 = $this->formBuilder->time('foo', \Carbon\Carbon::now()->format('H:i'));
        $form3 = $this->formBuilder->time('foo', null, ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="time">', $form1);
        $this->assertEquals('<input name="foo" type="time" value="' . \Carbon\Carbon::now()->format('H:i') . '">',
          $form2);
        $this->assertEquals('<input class="span2" name="foo" type="time">', $form3);
    }

    public function testFormUrl()
    {
        $form1 = $this->formBuilder->url('foo');
        $form2 = $this->formBuilder->url('foo', 'http://foobar.com');
        $form3 = $this->formBuilder->url('foo', null, ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="url">', $form1);
        $this->assertEquals('<input name="foo" type="url" value="http://foobar.com">', $form2);
        $this->assertEquals('<input class="span2" name="foo" type="url">', $form3);
    }

    public function testFormWeek()
    {
        $form1 = $this->formBuilder->week('foo');
        $form2 = $this->formBuilder->week('foo', \Carbon\Carbon::now());
        $form3 = $this->formBuilder->week('foo', null, ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="week">', $form1);
        $this->assertEquals('<input name="foo" type="week" value="' . \Carbon\Carbon::now()->format('Y-\WW') . '">',
          $form2);
        $this->assertEquals('<input class="span2" name="foo" type="week">', $form3);
    }

    public function testFormFile()
    {
        $form1 = $this->formBuilder->file('foo');
        $form2 = $this->formBuilder->file('foo', ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="file">', $form1);
        $this->assertEquals('<input class="span2" name="foo" type="file">', $form2);
    }

    public function testFormTextarea()
    {
        $form1 = $this->formBuilder->textarea('foo');
        $form2 = $this->formBuilder->textarea('foo', 'foobar');
        $form3 = $this->formBuilder->textarea('foo', null, ['class' => 'span2']);
        $form4 = $this->formBuilder->textarea('foo', null, ['size' => '60x15']);
        $form5 = $this->formBuilder->textarea('encoded_html', 'Eggs & Sausage', ['size' => '60x50']);
        $form6 = $this->formBuilder->textarea('encoded_html', 'Eggs &amp;&amp; Sausage', ['size' => '60x50']);

        $this->assertEquals('<textarea name="foo" cols="50" rows="10"></textarea>', $form1);
        $this->assertEquals('<textarea name="foo" cols="50" rows="10">foobar</textarea>', $form2);
        $this->assertEquals('<textarea class="span2" name="foo" cols="50" rows="10"></textarea>', $form3);
        $this->assertEquals('<textarea name="foo" cols="60" rows="15"></textarea>', $form4);
        $this->assertEquals('<textarea name="encoded_html" cols="60" rows="50">Eggs &amp; Sausage</textarea>', $form5);
        $this->assertEquals('<textarea name="encoded_html" cols="60" rows="50">Eggs &amp;&amp; Sausage</textarea>', $form6);
    }

    public function testSelect()
    {
        $select = $this->formBuilder->select(
          'size',
          ['L' => 'Large', 'S' => 'Small']
        );
        $this->assertEquals($select,
          '<select name="size"><option value="L">Large</option><option value="S">Small</option></select>');

        $select = $this->formBuilder->select(
          'size',
          ['L' => 'Large', 'S' => 'Small'],
          'L'
        );
        $this->assertEquals($select,
          '<select name="size"><option value="L" selected="selected">Large</option><option value="S">Small</option></select>');

        $select = $this->formBuilder->select(
            'size',
            ['0' => 'All Sizes', 'L' => 'Large', 'M' => 'Medium', 'S' => 'Small'],
            ['M'],
            ['multiple']
        );
        $this->assertEquals(
            $select,
            '<select multiple name="size"><option value="0">All Sizes</option><option value="L">Large</option><option value="M" selected="selected">Medium</option><option value="S">Small</option></select>');

        $select = $this->formBuilder->select(
          'size',
          ['L' => 'Large', 'S' => 'Small'],
          null,
          ['class' => 'class-name', 'id' => 'select-id']
        );
        $this->assertEquals($select,
          '<select class="class-name" id="select-id" name="size"><option value="L">Large</option><option value="S">Small</option></select>');

        $this->formBuilder->label('select-name-id');
        $select = $this->formBuilder->select(
          'select-name-id',
          [],
          null,
          ['name' => 'select-name']
        );
        $this->assertEquals($select, '<select name="select-name" id="select-name-id"></select>');

        $select = $this->formBuilder->select(
            'size',
            [
                'Large sizes' => [
                    'L' => 'Large',
                    'XL' => 'Extra Large',
                ],
                'S' => 'Small',
            ],
            null,
            [
                'class' => 'class-name',
                'id' => 'select-id',
            ]
        );

        $this->assertEquals(
            $select,
            '<select class="class-name" id="select-id" name="size"><optgroup label="Large sizes"><option value="L">Large</option><option value="XL">Extra Large</option></optgroup><option value="S">Small</option></select>'
        );

        $select = $this->formBuilder->select(
            'size',
            [
                'Large sizes' => [
                    'L' => 'Large',
                    'XL' => 'Extra Large',
                ],
                'M' => 'Medium',
                'Small sizes' => [
                    'S' => 'Small',
                    'XS' => 'Extra Small',
                ],
            ],
            null,
            [],
            [
                'Large sizes' => [
                    'L' => ['disabled']
                ],
                'M' => ['disabled'],
            ],
            [
                'Small sizes' => ['disabled'],
            ]
        );

        $this->assertEquals(
            $select,
            '<select name="size"><optgroup label="Large sizes"><option value="L" disabled>Large</option><option value="XL">Extra Large</option></optgroup><option value="M" disabled>Medium</option><optgroup label="Small sizes" disabled><option value="S">Small</option><option value="XS">Extra Small</option></optgroup></select>'
        );

        $select = $this->formBuilder->select(
            'encoded_html',
            ['no_break_space' => '&nbsp;', 'ampersand' => '&amp;', 'lower_than' => '&lt;'],
            null
        );

        $this->assertEquals(
            $select,
            '<select name="encoded_html"><option value="no_break_space">&nbsp;</option><option value="ampersand">&amp;</option><option value="lower_than">&lt;</option></select>'
        );

        $select = $this->formBuilder->select(
            'size',
            ['L' => 'Large', 'S' => 'Small'],
            null,
            [],
            ['L' => ['data-foo' => 'bar', 'disabled']]
        );
        $this->assertEquals($select,
            '<select name="size"><option value="L" data-foo="bar" disabled>Large</option><option value="S">Small</option></select>');

        $store = new Store('name', new \SessionHandler());
        $store->put('_old_input', ['countries' => ['1']]);
        $this->formBuilder->setSessionStore($store);

        $result = $this->formBuilder->select('countries', [1 => 'L', 2 => 'M']);

        $this->assertEquals(
            '<select name="countries"><option value="1" selected="selected">L</option><option value="2">M</option></select>',
            $result
        );

        $select = $this->formBuilder->select('avc', [1 => 'Yes', 0 => 'No'], true, ['placeholder' => 'Select']);
        $this->assertEquals(
            '<select name="avc"><option value="">Select</option><option value="1" selected>Yes</option><option value="0" >No</option></select>',
            $select
        );
    }

    public function testSelectCollection()
    {
        $select = $this->formBuilder->select(
            'size',
            collect(['L' => 'Large', 'S' => 'Small']),
            null,
            [],
            ['L' => ['data-foo' => 'bar', 'disabled']]
        );
        $this->assertEquals($select,
            '<select name="size"><option value="L" data-foo="bar" disabled>Large</option><option value="S">Small</option></select>');

        $select = $this->formBuilder->select(
            'size',
            collect([
                'Large sizes' => collect([
                    'L' => 'Large',
                    'XL' => 'Extra Large',
                ]),
                'S' => 'Small',
            ]),
            null,
            [
                'class' => 'class-name',
                'id' => 'select-id',
            ]
        );

        $this->assertEquals(
            $select,
            '<select class="class-name" id="select-id" name="size"><optgroup label="Large sizes"><option value="L">Large</option><option value="XL">Extra Large</option></optgroup><option value="S">Small</option></select>'
        );

        $select = $this->formBuilder->select(
            'size',
            collect([
                'Large sizes' => collect([
                    'L' => 'Large',
                    'XL' => 'Extra Large',
                ]),
                'M' => 'Medium',
                'Small sizes' => collect([
                    'S' => 'Small',
                    'XS' => 'Extra Small',
                ]),
            ]),
            null,
            [],
            [
                'Large sizes' => [
                    'L' => ['disabled']
                ],
                'M' => ['disabled'],
            ],
            [
                'Small sizes' => ['disabled'],
            ]
        );

        $this->assertEquals(
            $select,
            '<select name="size"><optgroup label="Large sizes"><option value="L" disabled>Large</option><option value="XL">Extra Large</option></optgroup><option value="M" disabled>Medium</option><optgroup label="Small sizes" disabled><option value="S">Small</option><option value="XS">Extra Small</option></optgroup></select>'
        );
    }

    public function testFormSelectRepopulation()
    {
        $list = ['L' => 'Large', 'M' => 'Medium', 'S' => 'Small'];
        $this->formBuilder->setSessionStore($session = m::mock('Illuminate\Contracts\Session\Session'));
        $this->setModel($model = ['size' => ['key' => 'S'], 'other' => 'val']);

        $session->shouldReceive('getOldInput')->once()->with('size')->andReturn('M');
        $select = $this->formBuilder->select('size', $list, 'S');
        $this->assertEquals($select,
          '<select name="size"><option value="L">Large</option><option value="M" selected="selected">Medium</option><option value="S">Small</option></select>');

        $session->shouldReceive('getOldInput')->once()->with('size.multi')->andReturn(['L', 'S']);
        $select = $this->formBuilder->select('size[multi][]', $list, 'M', ['multiple' => 'multiple']);
        $this->assertEquals($select,
          '<select multiple="multiple" name="size[multi][]"><option value="L" selected="selected">Large</option><option value="M">Medium</option><option value="S" selected="selected">Small</option></select>');

        $session->shouldReceive('getOldInput')->once()->with('size.key')->andReturn(null);
        $select = $this->formBuilder->select('size[key]', $list);
        $this->assertEquals($select,
          '<select name="size[key]"><option value="L">Large</option><option value="M">Medium</option><option value="S" selected="selected">Small</option></select>');
    }

    public function testFormWithOptionalPlaceholder()
    {
        $select = $this->formBuilder->select(
          'size',
          ['L' => 'Large', 'S' => 'Small'],
          null,
          ['placeholder' => 'Select One...']
        );
        $this->assertEquals($select,
          '<select name="size"><option selected="selected" value="">Select One...</option><option value="L">Large</option><option value="S">Small</option></select>');

        $select = $this->formBuilder->select(
          'size',
          ['L' => 'Large', 'S' => 'Small'],
          'L',
          ['placeholder' => 'Select One...']
        );
        $this->assertEquals($select,
          '<select name="size"><option value="">Select One...</option><option value="L" selected="selected">Large</option><option value="S">Small</option></select>');

        $select = $this->formBuilder->select(
            'encoded_html',
            ['no_break_space' => '&nbsp;', 'ampersand' => '&amp;', 'lower_than' => '&lt;'],
            null,
            ['placeholder' => 'Select the &nbsp;']
        );
        $this->assertEquals($select,
            '<select name="encoded_html"><option selected="selected" value="">Select the &nbsp;</option><option value="no_break_space">&nbsp;</option><option value="ampersand">&amp;</option><option value="lower_than">&lt;</option></select>'
        );
    }

    public function testFormSelectYear()
    {
        $select1 = (string) $this->formBuilder->selectYear('year', 2000, 2020);
        $select2 = (string) $this->formBuilder->selectYear('year', 2000, 2020, null, ['id' => 'foo']);
        $select3 = (string) $this->formBuilder->selectYear('year', 2000, 2020, '2000');

        $this->assertContains('<select name="year"><option value="2000">2000</option><option value="2001">2001</option>',
          $select1);
        $this->assertContains('<select id="foo" name="year"><option value="2000">2000</option><option value="2001">2001</option>',
          $select2);
        $this->assertContains('<select name="year"><option value="2000" selected="selected">2000</option><option value="2001">2001</option>',
          $select3);
    }

    public function testFormSelectRange()
    {
        $range = (string) $this->formBuilder->selectRange('dob', 1900, 2013);

        $this->assertContains('<select name="dob"><option value="1900">1900</option>', $range);
        $this->assertContains('<option value="2013">2013</option>', $range);
    }

    public function testFormSelectMonth()
    {
        $month1 = (string) $this->formBuilder->selectMonth('month');
        $month2 = (string) $this->formBuilder->selectMonth('month', '1');
        $month3 = (string) $this->formBuilder->selectMonth('month', null, ['id' => 'foo']);

        $this->assertContains('<select name="month"><option value="1">January</option><option value="2">February</option>',
          $month1);
        $this->assertContains('<select name="month"><option value="1" selected="selected">January</option>', $month2);
        $this->assertContains('<select id="foo" name="month"><option value="1">January</option>', $month3);
    }

    public function testFormCheckbox()
    {
        $this->formBuilder->setSessionStore($session = m::mock('Illuminate\Contracts\Session\Session'));

        $session->shouldReceive('getOldInput')->withNoArgs()->andReturn([]);
        $session->shouldReceive('getOldInput')->with('foo')->andReturn(null);

        $form1 = $this->formBuilder->input('checkbox', 'foo');
        $form2 = $this->formBuilder->checkbox('foo');
        $form3 = $this->formBuilder->checkbox('foo', 'foobar', true);
        $form4 = $this->formBuilder->checkbox('foo', 'foobar', false, ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="checkbox">', $form1);
        $this->assertEquals('<input name="foo" type="checkbox" value="1">', $form2);
        $this->assertEquals('<input checked="checked" name="foo" type="checkbox" value="foobar">', $form3);
        $this->assertEquals('<input class="span2" name="foo" type="checkbox" value="foobar">', $form4);
    }

    public function testFormCheckboxRepopulation()
    {
        $this->formBuilder->setSessionStore($session = m::mock('Illuminate\Contracts\Session\Session'));
        $session->shouldReceive('getOldInput')->withNoArgs()->andReturn([1]);

        $session->shouldReceive('getOldInput')->once()->with('check')->andReturn(null);
        $check = $this->formBuilder->checkbox('check', 1, true);
        $this->assertEquals('<input name="check" type="checkbox" value="1">', $check);

        $session->shouldReceive('getOldInput')->with('check.key')->andReturn('yes');
        $check = $this->formBuilder->checkbox('check[key]', 'yes');
        $this->assertEquals('<input checked="checked" name="check[key]" type="checkbox" value="yes">', $check);

        $session->shouldReceive('getOldInput')->with('multicheck')->andReturn([1, 3]);
        $check1 = $this->formBuilder->checkbox('multicheck[]', 1);
        $check2 = $this->formBuilder->checkbox('multicheck[]', 2, true);
        $check3 = $this->formBuilder->checkbox('multicheck[]', 3);

        $this->assertEquals('<input checked="checked" name="multicheck[]" type="checkbox" value="1">', $check1);
        $this->assertEquals('<input name="multicheck[]" type="checkbox" value="2">', $check2);
        $this->assertEquals('<input checked="checked" name="multicheck[]" type="checkbox" value="3">', $check3);
    }

    public function testFormCheckboxWithModelRelation()
    {
        $this->formBuilder->setSessionStore($session = m::mock('Illuminate\Contracts\Session\Session'));
        $session->shouldReceive('getOldInput')->withNoArgs()->andReturn([]);
        $session->shouldReceive('getOldInput')->with('items')->andReturn(null);

        $mockModel2 = new StdClass();
        $mockModel2->id = 2;
        $mockModel3 = new StdClass();
        $mockModel3->id = 3;
        $this->setModel(['items' => new Collection([$mockModel2, $mockModel3])]);

        $check1 = $this->formBuilder->checkbox('items[]', 1);
        $check2 = $this->formBuilder->checkbox('items[]', 2);
        $check3 = $this->formBuilder->checkbox('items[]', 3, false);
        $check4 = $this->formBuilder->checkbox('items[]', 4, true);

        $this->assertEquals('<input name="items[]" type="checkbox" value="1">', $check1);
        $this->assertEquals('<input checked="checked" name="items[]" type="checkbox" value="2">', $check2);
        $this->assertEquals('<input name="items[]" type="checkbox" value="3">', $check3);
        $this->assertEquals('<input checked="checked" name="items[]" type="checkbox" value="4">', $check4);
    }

    public function testFormCheckboxWithoutSession()
    {
        $form1 = $this->formBuilder->checkbox('foo');
        $form2 = $this->formBuilder->checkbox('foo', 'foobar', true);

        $this->assertEquals('<input name="foo" type="checkbox" value="1">', $form1);
        $this->assertEquals('<input checked="checked" name="foo" type="checkbox" value="foobar">', $form2);
    }

    public function testFormRadio()
    {
        $form1 = $this->formBuilder->input('radio', 'foo');
        $form2 = $this->formBuilder->radio('foo');
        $form3 = $this->formBuilder->radio('foo', 'foobar', true);
        $form4 = $this->formBuilder->radio('foo', 'foobar', false, ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="radio">', $form1);
        $this->assertEquals('<input name="foo" type="radio" value="foo">', $form2);
        $this->assertEquals('<input checked="checked" name="foo" type="radio" value="foobar">', $form3);
        $this->assertEquals('<input class="span2" name="foo" type="radio" value="foobar">', $form4);
    }

    public function testFormRadioWithAttributeCastToBoolean()
    {
        $this->setModel(['itemA' => true, 'itemB' => false]);

        $radio1 = $this->formBuilder->radio('itemA', 1);
        $radio2 = $this->formBuilder->radio('itemA', 0);
        $radio3 = $this->formBuilder->radio('itemB', 1);
        $radio4 = $this->formBuilder->radio('itemB', 0);

        $this->assertEquals('<input checked="checked" name="itemA" type="radio" value="1">', $radio1);
        $this->assertEquals('<input name="itemA" type="radio" value="0">', $radio2);
        $this->assertEquals('<input name="itemB" type="radio" value="1">', $radio3);
        $this->assertEquals('<input checked="checked" name="itemB" type="radio" value="0">', $radio4);
    }

    public function testFormRadioRepopulation()
    {
        $this->formBuilder->setSessionStore($session = m::mock('Illuminate\Contracts\Session\Session'));

        $session->shouldReceive('getOldInput')->with('radio')->andReturn(1);

        $radio1 = $this->formBuilder->radio('radio', 1);
        $radio2 = $this->formBuilder->radio('radio', 2, true);

        $this->assertEquals('<input checked="checked" name="radio" type="radio" value="1">', $radio1);
        $this->assertEquals('<input name="radio" type="radio" value="2">', $radio2);
    }

    public function testFormSubmit()
    {
        $form1 = $this->formBuilder->submit('foo');
        $form2 = $this->formBuilder->submit('foo', ['class' => 'span2']);

        $this->assertEquals('<input type="submit" value="foo">', $form1);
        $this->assertEquals('<input class="span2" type="submit" value="foo">', $form2);
    }

    public function testFormButton()
    {
        $form1 = $this->formBuilder->button('foo');
        $form2 = $this->formBuilder->button('foo', ['class' => 'span2']);

        $this->assertEquals('<button type="button">foo</button>', $form1);
        $this->assertEquals('<button class="span2" type="button">foo</button>', $form2);
    }

    public function testResetInput()
    {
        $resetInput = $this->formBuilder->reset('foo');
        $this->assertEquals('<input type="reset" value="foo">', $resetInput);
    }

    public function testImageInput()
    {
        $url = 'http://laravel.com/';
        $image = $this->formBuilder->image($url);

        $this->assertEquals('<input src="' . $url . '" type="image">', $image);
    }

    public function testFormColor()
    {
        $form1 = $this->formBuilder->color('foo');
        $form2 = $this->formBuilder->color('foo', '#ff0000');
        $form3 = $this->formBuilder->color('foo', null, ['class' => 'span2']);

        $this->assertEquals('<input name="foo" type="color">', $form1);
        $this->assertEquals('<input name="foo" type="color" value="#ff0000">', $form2);
        $this->assertEquals('<input class="span2" name="foo" type="color">', $form3);
    }

    public function testDatalist()
    {
        // Associative array with string keys.
        $genders = ['M' => 'Male', 'F' => 'Female'];
        $datalist = $this->formBuilder->datalist('genders', $genders);
        $this->assertEquals('<datalist id="genders"><option value="M">Male</option><option value="F">Female</option></datalist>', $datalist);

        // Associative array with numeric Keys
        $genders = [5 => 'Male', 6 => 'Female'];
        $datalist = $this->formBuilder->datalist('genders', $genders);
        $this->assertEquals('<datalist id="genders"><option value="5">Male</option><option value="6">Female</option></datalist>', $datalist);

        // Not associative array.
        $genders = ['Male', 'Female'];
        $datalist = $this->formBuilder->datalist('genders', $genders);
        $this->assertEquals('<datalist id="genders"><option value="Male">Male</option><option value="Female">Female</option></datalist>', $datalist);
    }

    public function testBooleanAttributes()
    {
        $input = $this->formBuilder->text('test', null, ['disabled']);
        $this->assertEquals('<input disabled name="test" type="text">', $input);

        $input = $this->formBuilder->textarea('test', null, ['readonly']);
        $this->assertEquals('<textarea readonly name="test" cols="50" rows="10"></textarea>', $input);
    }

    public function testArrayClassAttributes()
    {
        $input = $this->formBuilder->text('test', null, ['class' => ['class-a', 'class-b']]);
        $this->assertEquals('<input class="class-a class-b" name="test" type="text">', $input);

        $input = $this->formBuilder->text('test', null, ['class' => [
            'class-a',
            false ? 'class-b' : 'class-c'
        ]]);
        $this->assertEquals('<input class="class-a class-c" name="test" type="text">', $input);
    }

    protected function setModel(array $data, $object = true)
    {
        if ($object) {
            $data = new FormBuilderModelStub($data);
        }

        $this->formBuilder->model($data, ['method' => 'GET']);
    }
}

class FormBuilderModelStub
{
    protected $data;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $val = new self($val);
            }

            $this->data[$key] = $val;
        }
    }

    public function __get($key)
    {
        return $this->data[$key];
    }

    public function __isset($key)
    {
        return isset($this->data[$key]);
    }
}
