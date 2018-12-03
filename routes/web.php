<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();
Route::resource('appointments', 'AppointmentController');
Route::get('logout','\App\Http\Controllers\Auth\LoginController@logout');
Route::redirect('/','/login',301);
Route::get('/','DashboardController@index')->name('dashboard')->middleware('auth');
Route::get('/reporting/dashboard','DashboardController@dashboard')->name('reporting.dashboard')->middleware('auth');
Route::get('/checkout', 'AuthorizeController@index');
Route::post('/checkout', 'AuthorizeController@chargeCreditCard');
// admin
Route::group(['middleware' => ['auth','role:admin,subadmin']], function () {
    Route::resource('users','UsersController');
    Route::get('/user/activate','UsersController@activate')->name('users.activate');
    Route::get('/user/deactivate_index','UsersController@deactivate_index')->name('users.deactivate_index');
    Route::get('/user/deactivate','UsersController@deactivate')->name('users.deactivate');
    Route::get('/user/leaders','UsersController@leaders')->name('users.leaders');
    Route::get('/user/leaders/data','UsersController@leadersData')->name('users.leaders.data');
    Route::post('/user/leaders/save','UsersController@leadersSave')->name('users.leaders.save');
    Route::get('/user/leaders/memberRevert','UsersController@memberRevert')->name('users.leaders.memberRevert');
    Route::get('/user/dealflow','UsersController@dealflow')->name('users.dealflow');
    Route::get('/user/todaystatus','UsersController@todaystatus')->name('users.todaystatus');
    Route::get('/contacts/{type}','ContactController@index')->name('contact.index');
    Route::get('/contact/{type}/create','ContactController@create')->name('contact.create');
    Route::post('/contact/{type}/store','ContactController@store')->name('contact.store');
    Route::get('/contact/{id}/info','ContactController@show')->name('contact.show');
    Route::get('/contact/{id}/edit','ContactController@edit')->name('contact.edit');
    Route::post('/contact/{id}/update','ContactController@update')->name('contact.update');
    Route::delete('/contact/{id}/destroy','ContactController@destroy')->name('contact.destroy');
    Route::get('/contact/import','ContactController@import')->name('contact.import');
    Route::post('/contact/import','ContactController@import')->name('contact.import');
    Route::get('/contact/assign','ContactController@assign')->name('contact.assign');
    Route::get('/contact/deactivate','ContactController@deactivate')->name('contact.deactivate');
    Route::get('/contact/deactivate_index','ContactController@deactivate_index')->name('contact.deactivate_index');
    Route::get('/contact/activate','ContactController@activate')->name('contact.activate');

    Route::get('/reporting/blacklist','ReportingController@blacklist')->name('reporting.blacklist');
    Route::get('/reporting/calls/all','ReportingController@callsAll')->name('reporting.calls.all');
    Route::get('/reporting/calls','ReportingController@calls')->name('reporting.calls');
    Route::get('/reporting/calls/data','ReportingController@callsData')->name('reporting.calls.data');
    Route::get('/reporting/calls/edit','ReportingController@edit_call')->name('reporting.calls.edit');
    Route::post('/reporting/calls/edit','ReportingController@edit_call')->name('reporting.calls.edit');
    Route::get('/reporting/statistics','ReportingController@statistics')->name('reporting.statistics');
    Route::get('/reporting/statistics/data','ReportingController@statisticsData')->name('reporting.statistics.data');
    Route::get('/reporting/dealflow/data','ReportingController@dealflow')->name('reporting.dealflow.data');
    Route::get('/reporting/todaystatus/data','ReportingController@todaystatus')->name('reporting.todaystatus.data');
    Route::get('/reporting/appointments','ReportingController@appointment')->name('reporting.appointment');

    Route::get('/appointment/upcoming','AppointmentController@upcoming')->name('appointments.upcoming');
    Route::get('/appointment/finished','AppointmentController@finished')->name('appointments.finished');

    Route::get('/setting','SettingController@index')->name('setting.index');
    Route::post('/setting','SettingController@index')->name('setting.index');
    Route::get('/setting/check','SettingController@testSMTP')->name('setting.check');
    
    Route::resource('emails','EmailController');
    Route::get('/sendmail',	'EmailController@sendmail')->name('email.send');
});

// user -> candidate|company
Route::group(array('before' => 'role'), function() {
    Route::get('/contact','ContactController@show')->name('contact.get');
    Route::get('/contact/email','ContactController@email')->name('contact.email');
    Route::get('/contact/{id}/question','ContactController@question')->name('contact.question');
    Route::post('/contact/{id}/question','ContactController@question')->name('contact.question');
    Route::get('/contact/follow-ups','ContactController@follow_ups')->name('contact.follow-ups');
    Route::get('/contact/{id}/information','ContactController@information')->name('contact.information');
	Route::post('/appointment/information/','AppointmentController@information')->name('appointment.information');
	Route::get('/contact/events/events-json/{id}','ContactController@getEventsJson');
	Route::get('/calendar/{id}','ContactController@calendar')->name('contact.calendar');
	Route::post('/calendar/{id}','ContactController@calendar')->name('contact.postcalendar');        
});

Route::group(array('before' => 'closerrole'), function() {
        Route::get('/closer/contactview/{id}/{appointmentid}','ContactController@closercontactview')->name('contact.closercontactview');
        Route::get('/closer/calendar/{id}/{appointmentid}','ContactController@closercalendar')->name('contact.closercalendar');
        Route::post('/closer/calendar/{id}/{appointmentid}','ContactController@closercalendar')->name('contact.closerpostcalendar');
        Route::post('/closer/appointment/information/','AppointmentController@closerinformation')->name('appointment.closerinformation');
	Route::get('/appoinments','AppointmentController@index')->name('appoinments');
	Route::get('/closer/setting','SettingController@closerlist')->name('closer.setting');
        Route::post('/closer/setting','SettingController@closerlist')->name('closer.postsetting');
        Route::get('/closer/question/{contactid}','ContactController@closerquestion')->name('closer.question');
        Route::post('/closer/question/{contactid}','ContactController@closerquestion');
});

Route::group(array('before'=>'reqruitedrole'), function(){
    Route::get('/contact/contactview/{id}','ContactController@contactview')->name('contact.contactview');
    Route::post('/contact/invoice','ContactController@invoice')->name('contact.invoice');
    Route::get('/reqruited/calendar/{id}','ContactController@reqcalendar')->name('contact.reqcalendar');
    Route::post('/reqruited/appointment/information/','AppointmentController@reqinformation')->name('appointment.reqinformation');
    Route::get('/reqruited/appoinments','AppointmentController@reqruitedindex')->name('reqruited.appointments');
    Route::get('/reqruited/setting','SettingController@closerlist')->name('reqruited.setting');
    Route::post('/reqruited/setting','SettingController@closerlist')->name('reqruited.postsetting');
    Route::get('/reqruited/unpaidinvoice','ContactController@invoicelist')->name('reqruited.pendinginvoice');
});