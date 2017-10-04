<?php

namespace TestModels;


use Collective\Html\Eloquent\FormAccessible;
use Illuminate\Database\Eloquent\Model;

class Related extends Model
{
    use FormAccessible;

    public function formTypeAttribute()
    {
        return snake_case(array_get($this->attributes, 'type'));
    }

}