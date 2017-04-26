<?php

namespace Collective\Html;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class HtmlServiceProvider extends ServiceProvider
{
    /**
     * Supported Blade Directives
     *
     * @var array
     */
    protected $directives = ['entities','decode','script','style','image','favicon','link','secureLink','linkAsset','linkSecureAsset','linkRoute','linkAction','mailto','email','ol','ul','dl','meta','tag','open','model','close','token','label','input','text','password','hidden','email','tel','number','date','datetime','datetimeLocal','time','url','file','textarea','select','selectRange','selectYear','selectMonth','getSelectOption','checkbox','radio','reset','image','color','submit','button','old'
    ];

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerHtmlBuilder();

        $this->registerFormBuilder();

        $this->app->alias('html', HtmlBuilder::class);
        $this->app->alias('form', FormBuilder::class);

        $this->setBladeDirectives('Html', get_class_methods(HtmlBuilder::class));
        $this->setBladeDirectives('Form', get_class_methods(FormBuilder::class));
    }

    /**
     * Register the HTML builder instance.
     *
     * @return void
     */
    protected function registerHtmlBuilder()
    {
        $this->app->singleton('html', function ($app) {
            return new HtmlBuilder($app['url'], $app['view']);
        });
    }

    /**
     * Register the form builder instance.
     *
     * @return void
     */
    protected function registerFormBuilder()
    {
        $this->app->singleton('form', function ($app) {
            $form = new FormBuilder($app['html'], $app['url'], $app['view'], $app['session.store']->token());

            return $form->setSessionStore($app['session.store']);
        });
    }

    /**
     * Set blade directives
     *
     * @param string $namespace
     * @param array $methods
     *
     * @return void
     */
    protected function setBladeDirectives($namespace, $methods)
    {
        foreach ($methods as $method) {
            if (in_array($method, $this->directives)) {
                $snakeMethod = Str::snake($method);
                $directive = strtolower($namespace).'_'.$snakeMethod;

                Blade::directive($directive, function ($expression) use ($namespace, $method) {
                    return "<?php echo $namespace::$method($expression); ?>";
                });
            }
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['html', 'form', HtmlBuilder::class, FormBuilder::class];
    }
}
