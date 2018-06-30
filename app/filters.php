<?php

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

//allows backend api access depending on the user's role once they are logged in
Route::filter('role', function()  {
    if(!in_array(Auth::user()->role, User::ROLES)) {
        // do something
        return Redirect::to('/');
    }
});
