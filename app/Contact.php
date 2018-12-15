<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    const TYPES = [
        'company'   =>  'company',
        'candidate' =>  'candidate',
        'reqruited' =>  'reqruited'
    ];
    const COMPANY = 'company';
    const CANDIDATE = 'candidate';
    const REQRUITED = 'reqruited';

    const SUCCESSFUL = "Successful";
    const PROGRESS = "Progress";
    const NOT_ANSWERED = "Not answered";
    const GATEKEEPER = "Gatekeeper";
    const BLACKLIST = "Blacklist";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name','last_name','title','company_name','position','city_position','email','phone','mobile','description','note','type','user_id',
    ];

    /**
     * Get the questions for the contact.
     */
    public function calls()
    {
        return $this->hasMany('App\Call');
    }

    /**
     * Get the caller that owns the question.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}