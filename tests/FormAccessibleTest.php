<?php

use Carbon\Carbon;
use Collective\Html\Eloquent\FormAccessible;
use Illuminate\Database\Eloquent\Model;
use Collective\Html\FormBuilder;
use Collective\Html\HtmlBuilder;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Database\Capsule\Manager as Capsule;
use Mockery as m;

class FormAccessibleTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Capsule::table('models')->truncate();
        Model::unguard();

        $this->modelData = [
          'string'     => 'abcdefghijklmnop',
          'email'      => 'tj@tjshafer.com',
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ];

        $this->urlGenerator = new UrlGenerator(new RouteCollection(), Request::create('/foo', 'GET'));
        $this->viewFactory = m::mock(Factory::class);
        $this->htmlBuilder = new HtmlBuilder($this->urlGenerator, $this->viewFactory);
        $this->formBuilder = new FormBuilder($this->htmlBuilder, $this->urlGenerator, 'abc');
    }

    public function testItCanMutateValuesForForms()
    {
        $model = new ModelThatUsesForms($this->modelData);
        $this->formBuilder->setModel($model);

        $this->assertEquals($model->getFormValue('string'), 'ponmlkjihgfedcba');
        $this->assertEquals($model->getFormValue('created_at'), time());
    }

    public function testItCanStillMutateValuesForViews()
    {
        $model = new ModelThatUsesForms($this->modelData);
        $this->formBuilder->setModel($model);

        $this->assertEquals($model->string, 'ABCDEFGHIJKLMNOP');
        $this->assertEquals($model->created_at, Carbon::now()->diffForHumans());
    }

    public function testItDoesntRequireTheUseOfThisFeature()
    {
        $model = new ModelThatDoesntUseForms($this->modelData);
        $this->formBuilder->setModel($model);

        $this->assertEquals($model->string, 'ABCDEFGHIJKLMNOP');
        $this->assertEquals($model->created_at, Carbon::now()->diffForHumans());
    }

}

class ModelThatUsesForms extends Model
{

    use FormAccessible;

    protected $table = 'models';

    public function formStringAttribute($value)
    {
        return strrev($value);
    }

    public function getStringAttribute($value)
    {
        return strtoupper($value);
    }

    public function formCreatedAtAttribute(Carbon $value)
    {
        return $value->timestamp;
    }

    public function getCreatedAtAttribute($value)
    {
        return '1 second ago';
    }

}

class ModelThatDoesntUseForms extends Model
{

    protected $table = 'models';

    public function getStringAttribute($value)
    {
        return strtoupper($value);
    }

    public function getCreatedAtAttribute($value)
    {
        return '1 second ago';
    }

}