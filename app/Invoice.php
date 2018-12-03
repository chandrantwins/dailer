<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoice';
    protected $fillable = [
            'customerid','status','amount','user_id','contact_id','customername'
    ];
}
