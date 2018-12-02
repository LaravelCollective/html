<?php

use Illuminate\Contracts\View\Factory;
use Collective\Html\HtmlBuilder;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\UrlGenerator;
use Mockery as m;

class HtmlBuilderTest extends PHPUnit\Framework\TestCase
{

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->urlGenerator = new UrlGenerator(new RouteCollection(), Request::create('/foo', 'GET'));
        $this->viewFactory = m::mock(Factory::class);
        $this->htmlBuilder = new HtmlBuilder($this->urlGenerator, $this->viewFactory);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testDl()
    {
        $list = [
          'foo'  => 'bar',
          'bing' => 'baz',
        ];

        $attributes = ['class' => 'example'];

        $result = $this->htmlBuilder->dl($list, $attributes);

        $this->assertEquals('<dl class="example"><dt>foo</dt><dd>bar</dd><dt>bing</dt><dd>baz</dd></dl>', $result);
    }

    public function testOl()
    {
        $list = ['foo', 'bar', '&amp;'];

        $attributes = ['class' => 'example'];

        $ol = $this->htmlBuilder->ol($list, $attributes);

        $this->assertEquals('<ol class="example"><li>foo</li><li>bar</li><li>&amp;</li></ol>', $ol);
    }

    public function testUl()
    {
        $list = ['foo', 'bar', '&amp;'];

        $attributes = ['class' => 'example'];

        $ul = $this->htmlBuilder->ul($list, $attributes);

        $this->assertEquals('<ul class="example"><li>foo</li><li>bar</li><li>&amp;</li></ul>', $ul);
    }

    public function testMeta()
    {
        $result = $this->htmlBuilder->meta('description', 'Lorem ipsum dolor sit amet.');

        $this->assertEquals('<meta name="description" content="Lorem ipsum dolor sit amet.">', $result);
    }

    public function testTag()
    {
        $result1 = $this->htmlBuilder->tag('p', 'Lorem ipsum dolor sit amet.');

        $result2 = $this->htmlBuilder->tag('p', 'Lorem ipsum dolor sit amet.', ['class' => 'text-center']);

        $result3 = $this->htmlBuilder->tag('div', '<p>Lorem ipsum dolor sit amet.</p>', ['class' => 'row']);

        $content = [
            $this->htmlBuilder->image('http://example.com/image1'),
            $this->htmlBuilder->image('http://example.com/image2'),
        ];

        $result4 = $this->htmlBuilder->tag('div', $content, ['class' => 'row']);

        $this->assertEquals('<p>Lorem ipsum dolor sit amet.</p>', $result1);
        $this->assertEquals('<p class="text-center">Lorem ipsum dolor sit amet.</p>', $result2);
        $this->assertEquals('<div class="row"><p>Lorem ipsum dolor sit amet.</p></div>', $result3);
        $this->assertEquals('<div class="row"><img src="http://example.com/image1"><img src="http://example.com/image2"></div>', $result4);
    }

    public function testMetaOpenGraph()
    {
        $result = $this->htmlBuilder->meta(null, 'website', ['property' => 'og:type']);

        $this->assertEquals('<meta content="website" property="og:type">', $result);
    }

    public function testFavicon()
    {
        $this->urlGenerator->forceRootUrl('http://foo.com');
        $target = $this->urlGenerator->to('bar.ico');
        $result = $this->htmlBuilder->favicon('http://foo.com/bar.ico');

        $this->assertEquals('<link rel="shortcut icon" type="image/x-icon" href="' . $target . '">', $result);
    }

    public function testComponentRegistration()
    {
        $this->htmlBuilder->component('tweet', 'components.tweet', ['handle', 'body', 'date']);

        $this->assertTrue($this->htmlBuilder->hasComponent('tweet'));
    }

    public function testLink()
    {
        $result1 = $this->htmlBuilder->link("http://www.example.com", "<span>Example.com</span>", ["class" => "example-link"], null, true);

        $result2 = $this->htmlBuilder->link("http://www.example.com", "<span>Example.com</span>", ["class" => "example-link"], null, false);

        $result3 = $this->htmlBuilder->link("https://a.com/b?id=4&not_id=5", "URL which needs escaping");

        $this->assertEquals('<a href="http://www.example.com" class="example-link">&lt;span&gt;Example.com&lt;/span&gt;</a>', $result1);
        $this->assertEquals('<a href="http://www.example.com" class="example-link"><span>Example.com</span></a>', $result2);
        $this->assertEquals('<a href="https://a.com/b?id=4&amp;not_id=5">URL which needs escaping</a>', $result3);
    }

    public function testMailto()
    {
        $htmlBuilder = m::mock('Collective\Html\HtmlBuilder[obfuscate,email]', [$this->urlGenerator, $this->viewFactory]);
        $htmlBuilder->shouldReceive('obfuscate', 'email')->andReturnUsing(function () {
            $args = func_get_args();
            return $args[0];
        });

        $result1 = $htmlBuilder->mailto("person@example.com", "<span>First Name Last</span>", ["class" => "example-link"], true);

        $result2 = $htmlBuilder->mailto("person@example.com", "<span>First Name Last</span>", ["class" => "example-link"], false);

        $this->assertEquals('<a href="mailto:person@example.com" class="example-link">&lt;span&gt;First Name Last&lt;/span&gt;</a>', $result1);
        $this->assertEquals('<a href="mailto:person@example.com" class="example-link"><span>First Name Last</span></a>', $result2);
    }

    public function testBooleanAttributes()
    {
        $result1 = $this->htmlBuilder->attributes(['my-property' => true]);

        $result2 = $this->htmlBuilder->attributes(['my-property' => false]);

        $this->assertEquals('my-property', trim($result1));

        $this->assertEquals('', trim($result2));
    }

    public function testArrayClassAttributes()
    {
        $result = $this->htmlBuilder->attributes(['class' => ['class-a', 'class-b']]);

        $this->assertEquals('class="class-a class-b"', trim($result));

        $result = $this->htmlBuilder->attributes(['class' => [
            'class-a',
            false ? 'class-b' : 'class-c'
        ]]);

        $this->assertEquals('class="class-a class-c"', trim($result));
    }
}
