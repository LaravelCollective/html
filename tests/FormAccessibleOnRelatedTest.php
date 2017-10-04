<?php

use Illuminate\Database\Eloquent\Model;
use Collective\Html\FormBuilder;
use Collective\Html\HtmlBuilder;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Database\Capsule\Manager as Capsule;
use Mockery as m;
use TestModels\Main;
use TestModels\Related;

class FormAccessibleOnRelatedTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Capsule::table('main')->truncate();
        Capsule::table('related')->truncate();
        Model::unguard();

        $this->urlGenerator = new UrlGenerator(new RouteCollection(), Request::create('/foo', 'GET'));
        $this->viewFactory = m::mock(Factory::class);
        $this->htmlBuilder = new HtmlBuilder($this->urlGenerator, $this->viewFactory);
        $this->formBuilder = new FormBuilder($this->htmlBuilder, $this->urlGenerator, $this->viewFactory, 'abc');
    }

    /**
     * @dataProvider relatedModelDataProvider
     */
    public function testItCanMutateRelatedModelAttributesWhenAccessedFromTheMainForm($id, $relatedAttributes, $expected)
    {
        $main = new Main(['id' => $id]);
        $related = new Related($relatedAttributes);
        $main->related()->associate($related);

        $this->formBuilder->setModel($main);

        $value = $this->formBuilder->getValueAttribute('related[type]');

        $this->assertEquals($expected, $value);

    }

    public function relatedModelDataProvider()
    {
        return [
            [1, ['type' => 'snakeCase'], 'snake_case'],
            [2, ['type' => 'sneakyCase'], 'sneaky_case']
        ];
    }

}
