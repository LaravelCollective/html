<?php

namespace TestModels;


use Collective\Html\Eloquent\FormAccessible;
use Illuminate\Database\Eloquent\Model;

class Main extends Model
{
    use FormAccessible;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function related()
    {
        return $this->belongsTo(Related::class);
    }
}