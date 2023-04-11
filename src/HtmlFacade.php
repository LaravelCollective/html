<?php

namespace Collective\Html;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string entities(string $value)
 * @method static string decode(string $value)
 * @method static \Illuminate\Support\HtmlString script(string $url, array $attributes = [], bool $secure = null)
 * @method static \Illuminate\Support\HtmlString style(string $url, array $attributes = [], bool $secure = null)
 * @method static \Illuminate\Support\HtmlString image(string $url, string $alt = null, array $attributes = [], bool $secure = null)
 * @method static \Illuminate\Support\HtmlString favicon(string $url, array $attributes = [], bool $secure = null)
 * @method static \Illuminate\Support\HtmlString link(string $url, string $title = null, array $attributes = [], bool $secure = null, bool $escape = true)
 * @method static \Illuminate\Support\HtmlString secureLink(string $url, string $title = null, array $attributes = [], bool $escape = true)
 * @method static \Illuminate\Support\HtmlString linkAsset(string $url, string $title = null, array $attributes = [], bool $secure = null, bool $escape = true)
 * @method static \Illuminate\Support\HtmlString linkSecureAsset(string $url, string $title = null, array $attributes = [], bool $escape = true)
 * @method static \Illuminate\Support\HtmlString linkRoute(string $name, string $title = null, array $parameters = [], array $attributes = [], bool $secure = null, bool $escape = true)
 * @method static \Illuminate\Support\HtmlString linkAction(string $action, string $title = null, array $parameters = [], array $attributes = [], bool $secure = null, bool $escape = true)
 * @method static \Illuminate\Support\HtmlString mailto(string $email, string $title = null, array $attributes = [], bool $escape = true)
 * @method static string email(string $email)
 * @method static string nbsp(int $num = 1)
 * @method static \Illuminate\Support\HtmlString|string ol(array $list, array $attributes = [])
 * @method static \Illuminate\Support\HtmlString|string ul(array $list, array $attributes = [])
 * @method static \Illuminate\Support\HtmlString dl(array $list, array $attributes = [])
 * @method static string attributes(array $attributes)
 * @method static string obfuscate(string $value)
 * @method static \Illuminate\Support\HtmlString meta(string $name, string $content, array $attributes = [])
 * @method static \Illuminate\Support\HtmlString tag(string $tag, mixed $content, array $attributes = [])
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 * @method static mixed macroCall(string $method, array $parameters)
 * @method static void component($name, $view, array $signature)
 * @method static bool hasComponent($name)
 * @method static \Illuminate\Contracts\View\View|mixed componentCall(string $method, array $parameters)
 *
 * @see \Collective\Html\HtmlBuilder
 */
class HtmlFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'html';
    }
}
