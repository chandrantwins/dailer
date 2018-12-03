<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    const ANSWER_SUCCESSFULLY = 0;
    const ANSWER_PROGRESS = 1;

    const ANSWER_ASKED_SIGNUP = 2;
    const ANSWER_UNSUCCESSFUL = 2;

    const ANSWER_ASKED_REMOVED = 3;
    const ANSWER_NOT_ANSWERED = 4;
    const ANSWER_LEFT_MESSAGE = 5;
    const ANSWER_GATEKEEPER = 6;
    const ANSWER_WRONG_NUMBER = 7;

    const QUESTION_SHORT = [
        "candidate" => [
            "Successfully.",
            "Follow up.",
            "Asked to be sent sign up link.",
            "Asked to be removed.",
            "No message left.",
            "Left message.",
        ],
        "company" => [
            "Successfully.",
            "Follow up.",
            "Unsuccessful.",
            "Asked to be removed.",
            "No message left.",
            "Left message.",
            "Past gatekeeper.",
            "Wrong number.",
            "Not Interested"
        ],
        "closer"=>[
            "Successfully",
            "Unsuccessful",
            "Not Show"
        ],
        'reqruited'=>[            
            "Successfully",
            "Unsuccessful",
            "Not Show"
        ]
    ];

    const QUESTION = [
        "candidate" => [
            "Candidate successfully signed up.",
            "Call answered, but need to follow up.",
            "Call answered, and asked to be sent sign up link.",
            "Call answered, but asked to be removed.",
            "Call not answer, and no message left.",
            "Call not answer, but message left.",
        ],
        "company" => [
            "Demo Successfully scheduled.",
            "Call answered, but need to follow up.",
            "Call answered, but unsuccessful.",
            "Call answered, but asked to be removed.",
            "Call not answer, and no message left.",
            "Call not answer, but message left.",
            "Could not get past gatekeeper.",
            "Wrong number.",
            "Presently not interested",
        ],
        "closer" => [
            "Company signed up",
            "Company did not sign up",
            "Company did not show for the call"
        ],
        "reqruited" => [            
            "Company signed up",
            "Company did not sign up",
            "Company did not show for the call"            
         ]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['answer', 'note', 'remind_me_at', 'update_at'];

    /**
     * Get the contact that owns the question.
     */
    public function contact()
    {
        return $this->belongsTo('App\Contact');
    }

    /**
     * Get the caller that owns the question.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}