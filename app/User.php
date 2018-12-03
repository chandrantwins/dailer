<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property string full_name
 * @property string username
 * @property string email
 * @property string password
 * @property mixed role
 */
class User extends Authenticatable
{
    use Notifiable;

    const ROLES = [
        'admin'     =>  'admin',
        'subadmin'     =>  'subadmin',
        'company'   =>  'company',
        'candidate' =>  'candidate',
        'closer' => 'closer',
        'reqruited' => 'reqruited'
    ];
    const ADMIN = 'admin';
    const SUBADMIN = 'subadmin';
    const COMPANY = 'company';
    const CANDIDATE = 'candidate';
    const CLOSER = 'closer';
    const REQRUITED = 'reqruited';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name','last_name','role','affiliate','username','email','phone','password','user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @param string $role
     * 
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->role == $role;
    }

    /**
     * Get the calls for the user.
     */
    public function calls()
    {
        return $this->hasMany('App\Call');
    }

    /**
     * Get the contacts for the user.
     */
    public function contacts()
    {
        return $this->hasMany('App\Contact');
    }
    /**
     * Get the users for the leader.
     */
    public function users()
    {
        return $this->hasMany('App\User');
    }
    
    public function getFullNameAttribute ($value) {
        return ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
    }
}