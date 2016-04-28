<?php

namespace Collective\Html\Eloquent;

use ReflectionClass;
use ReflectionMethod;
use Illuminate\Support\Str;

trait FormAccessible
{

    /**
     * A cached ReflectionClass instance for $this
     *
     * @var ReflectionClass
     */
    protected $reflection;

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getFormValue($key)
    {
        $value = $this->getAttribute($key);

        // If the attribute has a get mutator, we will call that then return what
        // it returns as the value, which is useful for transforming values on
        // retrieval from the model to a form that is more useful for usage.
        if ($this->hasFormMutator($key)) {
            return $this->mutateFormAttribute($key, $value);
        }

        return $value;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    protected function hasFormMutator($key)
    {
        $methods = $this->getReflection()->getMethods(ReflectionMethod::IS_PUBLIC);

        $mutator = collect($methods)
          ->first(function ($index, ReflectionMethod $method) use ($key) {
              return $method->getName() == 'form' . Str::studly($key) . 'Attribute';
          });

        return (bool) $mutator;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    private function mutateFormAttribute($key, $value)
    {
        return $this->{'form' . Str::studly($key) . 'Attribute'}($value);
    }

    /**
     * Get a ReflectionClass Instance
     * @return ReflectionClass
     */
    protected function getReflection()
    {
        if (! $this->reflection) {
            $this->reflection = new ReflectionClass($this);
        }

        return $this->reflection;
    }
}
