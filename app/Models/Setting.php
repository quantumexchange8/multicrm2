<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use SoftDeletes;

    public static function getKeyValue(): array
    {
        $plucked = Setting::all()->pluck(
            'value',
            'name'
        );

        return $plucked->all();
    }
}
