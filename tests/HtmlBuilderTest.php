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

	public function testMeta()
	{
		$result = $this->htmlBuilder->meta('description', 'Lorem ipsum dolor sit amet.');

		$this->assertEquals('<meta name="description" content="Lorem ipsum dolor sit amet.">'.PHP_EOL, $result);
	}

	public function testMetaOpenGraph()
	{
		$result = $this->htmlBuilder->meta(null, 'website', ['property' => 'og:type']);

		$this->assertEquals('<meta content="website" property="og:type">'.PHP_EOL, $result);
	}

    public function testFavicon()
    {
        $this->urlGenerator->forceRootUrl('http://foo.com');
        $target = $this->urlGenerator->to('bar.ico');
        $result = $this->htmlBuilder->favicon('http://foo.com/bar.ico');

        $this->assertEquals('<link rel="shortcut icon" type="image/x-icon" href="'.$target.'">' . PHP_EOL, $result);
    }

}
