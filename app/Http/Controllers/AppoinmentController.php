<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppoinmentController extends Controller
{
     /**
     * Display a listing of the appoinments.
     *
     * @param $type
     * @return \Illuminate\Http\Response
     */
    public function index()
    {        
        return view("appoinment.calendar");
    }
}
