<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleCall extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'remind_me_at', 'email_sent', 'call_id'
    ];

    /**
     * Get the questions for the contact.
     */
    public function call()
    {
        return $this->belongsTo('App\Call');
    }
}
