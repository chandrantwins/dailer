<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'email_template';
	
	protected $fillable = array('subject', 'content', 'handle', 'layout_id', 'type', 'use_me','smtp');
}