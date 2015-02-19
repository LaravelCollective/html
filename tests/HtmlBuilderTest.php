<?php

use Illuminate\Session\Store;
use Mockery as m;
use Illuminate\Http\Request;
use Collective\Html\FormBuilder;
use Collective\Html\HtmlBuilder;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Routing\RouteCollection;

class HtmlBuilderTest extends PHPUnit_Framework_TestCase {

  /**
   * Setup the test environment.
   */
  public function setUp()
  {
    $this->urlGenerator = new UrlGenerator(new RouteCollection, Request::create('/foo', 'GET'));
    $this->htmlBuilder  = new HtmlBuilder($this->urlGenerator);
  }


  /**
   * Destroy the test environment.
   */
  public function tearDown()
  {
    m::close();
  }

  public function testDl()
  {
    $list = [
      'foo' => 'bar',
      'bing' => 'baz'
    ];

    $attributes = ['class' => 'example'];

    $result = $this->htmlBuilder->dl($list, $attributes);

    $this->assertEquals('<dl class="example"><dt>foo</dt><dd>bar</dd><dt>bing</dt><dd>baz</dd></dl>', $result);
  }

}