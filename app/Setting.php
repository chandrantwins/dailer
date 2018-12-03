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
    const TIMEZONES = [
        '1' =>  'Pacific Standard Time (UTC - 8)',
        '2' =>  'Mountain Standard Time (UTC - 7)',
        '3' =>  'Central Standard Time (UTC - 6)',
        '4' =>  'Eastern Standard Time (UTC - 5)'        
    ];

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
