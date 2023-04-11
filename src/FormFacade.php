<?php

namespace Collective\Html;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\HtmlString open(array $options = [])
 * @method static \Illuminate\Support\HtmlString model(mixed $model, array $options = [])
 * @method static void setModel(mixed $model)
 * @method static mixed getModel()
 * @method static string close()
 * @method static string token()
 * @method static \Illuminate\Support\HtmlString label(string $name, string $value = null, array $options = [], bool $escape_html = true)
 * @method static \Illuminate\Support\HtmlString input(string $type, string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString text(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString password(string $name, array $options = [])
 * @method static \Illuminate\Support\HtmlString range(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString hidden(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString search(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString email(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString tel(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString number(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString date(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString datetime(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString datetimeLocal(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString time(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString url(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString week(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString file(string $name, array $options = [])
 * @method static \Illuminate\Support\HtmlString textarea(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString select(string $name, array $list = [], string|bool $selected = null, array $selectAttributes = [], array $optionsAttributes = [], array $optgroupsAttributes = [])
 * @method static \Illuminate\Support\HtmlString selectRange(string $name, string $begin, string $end, string $selected = null, array $options = [])
 * @method static mixed selectYear(string $name = null, string $begin = null, string $end = null, string $selected = null, array $options = null)
 * @method static \Illuminate\Support\HtmlString selectMonth(string $name, string $selected = null, array $options = [], string $format = '%B')
 * @method static \Illuminate\Support\HtmlString getSelectOption(string $display, string $value, string $selected, array $attributes = [], array $optgroupAttributes = [])
 * @method static \Illuminate\Support\HtmlString checkbox(string $name, mixed $value = 1, bool $checked = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString radio(string $name, mixed $value = null, bool $checked = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString reset(string $value, array $attributes = [])
 * @method static \Illuminate\Support\HtmlString image(string $url, string $name = null, array $attributes = [])
 * @method static \Illuminate\Support\HtmlString month(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString color(string $name, string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString submit(string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString button(string $value = null, array $options = [])
 * @method static \Illuminate\Support\HtmlString datalist(string $id, array $list = [])
 * @method static string getIdAttribute(string $name, array $attributes)
 * @method static mixed getValueAttribute(string $name, string $value = null)
 * @method static mixed considerRequest(bool $consider = true)
 * @method static mixed old(string $name)
 * @method static bool oldInputIsEmpty()
 * @method static \Illuminate\Contracts\Session\Session getSessionStore()
 * @method static \Collective\Html\FormBuilder setSessionStore(\Illuminate\Contracts\Session\Session $session)
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 * @method static mixed macroCall(string $method, array $parameters)
 * @method static void component($name, $view, array $signature)
 * @method static bool hasComponent($name)
 * @method static \Illuminate\Contracts\View\View|mixed componentCall(string $method, array $parameters)
 *
 * @see \Collective\Html\FormBuilder
 */
class FormFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'form';
    }
}
