<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string key
 * @property string name
 * @property string value
 */
class Setting extends Model
{
    const ENCRYPTION = [
        NULL  => 'NONE',
        'ssl' => 'SSL',
        'tls' => 'TLS'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['key','name','value'];
}
