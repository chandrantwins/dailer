<?php

namespace App\Http\Controllers;

use App\User;
use App\Contact;
use App\Call;
use App\Email;
use App\Invoice;
use App\ScheduleCall;
use App\Setting;
use App\Appointment;
use App\EmailTemplate;
use Calendar;
use Validator;
use Invoiced\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param $type
     * @return \Illuminate\Http\Response
     */
    public function index($type)
    {
        $type = $this->type($type);
        $contacts = Contact::where('type', $type)
            ->where('enabled', 1)
            ->get();

        // load the view and pass the contacts
        return view("contact.$type.index", compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $type
     * @return \Illuminate\Http\Response
     */
    public function create($type)
    {
        $type = $this->type($type);

        return view("contact.$type.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param $type
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $type)
    {
        $type = $this->type($type);
        $validate = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ];

        if ('company' == $type) {
            $validate = array_merge($validate, [
                'company_name' => 'required|string|max:255',
                'phone' => 'required|string|max:255',
            ]);
        }

        request()->validate($validate);
        $request->merge(['type' => $type]);

        $contact = Contact::create($request->all());
        $contact->save();

        DB::table('queues')->insert([
            'contact_id' => $contact->id,
            'type' => $type,
            'enabled' => true,
        ]);

        return redirect()->route("contact.index", ['type' => $type])->with('alert', [
            'class' => 'success',
            'message' => 'Contact created successfully'
        ]);
    }
    
    public function closercontactview(Request $request){        
        $contact = Contact::find($request->id);
        $appointment = Appointment::find($request->appointmentid);
        return view("contact.closer.show", compact('contact','appointment'));        
    }
    
    public function contactview(Request $request){
        $user = Auth::user();
        $contact = Contact::find($request->id);
        if($user->role == 'closer'){
            $type = 'closer';
        }else{
            $type = $contact->type;
        }
        return view("contact.$type.show", compact('contact'));        
    }

    public function reqruitedview(Request $request){
        $contact = Contact::find($request->id);
        return view("contact.reqruited.contactview", compact('contact'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id = 1)
    {
        $user = Auth::user();

        if (in_array($user->role, [User::ADMIN, User::SUBADMIN])) {
            $contact = Contact::find($id);
            return view("contact.$contact->type.show", compact('contact'));
        } else {
            // check logged in user has already any assigned contacts (contact will have user_id column to identify this)
            if ($user->contacts()->count() == 0) {
                $now = Carbon::now();
                $contact_queue = DB::table('queues')
                    ->where('type', $user->role)
                    ->where('enabled', true)
                    ->first();
                if (null != $contact_queue) {
                    $contact = Contact::find($contact_queue->contact_id);
                    $contact->user_id = $user->id;
                    $contact->save();

                    DB::table('assign_log')->insert([
                        'via_questions' => true,
                        'user_id' => $user->id,
                        'contact_id' => $contact->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    DB::table('queues')
                        ->where('contact_id', $contact->id)
                        ->update([
                            'user_id' => $user->id,
                            'enabled' => false
                        ]);
                } else {
                    return view("contact.no_contact");
                }
            } else {
                // here it comes only already assigned contact so we have to update only timestamp
                $contact = $user->contacts()->first();
                $assignLog = DB::table('assign_log')
                    ->where('user_id', $user->id)
                    ->where('contact_id', $contact->id)
                    ->orderBy('updated_at', 'desc')
                    ->first();
                if (null != $assignLog) {
                    $assignsLogDate = new Carbon($assignLog->updated_at);

                    if ($assignsLogDate->lt(Carbon::now()) && $assignsLogDate->diffInDays(Carbon::now()) > 7) {
                        $contact_id = DB::table('queues')
                            ->orderBy('id', 'asc')
                            ->where('type', $user->role)
                            ->first()
                            ->contact_id;
                        $contact = Contact::find($contact_id);
                    }
                }
            }
            return view("contact.$user->role.show", compact('contact'));
        }
    }
	
    public function calendar(Request $request)
    {
        $user = Auth::user();
        $contact = Contact::find($request->id);
        $event_list = [];
        $appoinments = [];
        $availablehours = [];
        if(isset($request->timezoneindex)){ // Display only blocked dates                    
                $closerlist = User::where('enabled', true)->where('role', USER::CLOSER)->get();
                $closerids = [];
                foreach($closerlist as $closer){
                    $timezone = Setting::select('value')->where('key','timezone_' . $closer->id)->get();
                    $workhours = Setting::select('value')->where('key', 'workhours_' . $closer->id)->get();
                    if(isset($workhours[0]) && isset($timezone[0])){
                        $closerids[] = $closer->id;
                        $closertimezone = $timezone[0]->value;
                        foreach (json_decode($workhours[0]->value) as $k=>$day){
                            if($k < 5){ // Monday to friday only                                 
                                $timing ='';
                                $timezone = $this->getTimezoneNameByIndex($request->timezoneindex);
                                if(!empty(json_decode($workhours[0]->value)[$k]->timeFrom)){
                                    $todayFromTime = date('Y-m-d').' '.json_decode($workhours[0]->value)[$k]->timeFrom.':00';
                                    // UTC to selectedTimezone conversion
                                    $timeFrom = $this->getConvertedDateTime($todayFromTime,'UTC',$timezone, 'H:i');                                    
                                    $timing .= $timeFrom;
                                }
                                if(!empty($timing)){
                                    if(!empty(json_decode($workhours[0]->value)[$k]->timeTill)){
                                        $todayTillTime = date('Y-m-d').' '.json_decode($workhours[0]->value)[$k]->timeTill.':00';
                                        // UTC to selectedTimezone conversion
                                        $timeTill = $this->getConvertedDateTime($todayTillTime,'UTC',$timezone, 'H:i');
                                        $timing .= '-'.$timeTill;
                                    }
                                }
                                if(!empty($timing)){
                                    $availablehours[++$k][] = $timing;                                        
                                }
                            }
                        }
                    }
                }
                for($t=1;$t<6;$t++){
                    if(!isset($availablehours[$t])){
                        // if no closers available for particular day ..then block that day in all months
                        $event_list[] = Calendar::event('Unavailable', false, '00:00', '23:59','calhbevn',array('dow'=>[$t],'className'=>'fc-unavailable'));
                    }
                }
                // TO handle no closer avilable timings
                $favailablehrs = [];
                foreach($availablehours as $kindex=>$available){
                    foreach($available as $k=>$avail){
                        $timerange = explode('-',$avail);
                        $favailablehrs[$kindex][] = $this->create_time_range($timerange[0],$timerange[1],'5 mins','24');
                    }                        
                }
                foreach($favailablehrs as $mindex=>$favailable){
                    // handling all closer timings
                    if(count($favailable) > 1){
                        $inter = [];
                        foreach($favailable as $k=>$fav){
                            foreach($fav as $f){
                                $inter[] = $f;
                            }
                        }
                        $timings = $this->getTimeranges($inter);
                    }else{
                        // handling only one closer timing
                        $timings = $this->getTimeranges($favailable[0]);
                    }                    
                    foreach($timings as $cuitems){
                            $fitem = reset($cuitems);
                            $litem = end($cuitems);
                            if($litem == '00:00'){
                                $litem = '23:59';
                            }
                            $event_list[] = Calendar::event('Unavailable', false, $fitem, $litem,'calhbevn',array('dow'=>[$mindex],'className'=>'fc-unavailable'));                         
                     }
                }               
                if(count($closerids) > 0){
                    $appoinments = Appointment::orderBy('id','DESC')->whereIn('assigned_to',$closerids)->where('timezone', '!=', '')->groupBy('originalnotificationTime')->get();
                }
        }
        //print_r($closerids);
        if(count($appoinments) > 0){            
                foreach($appoinments as $key => $appoinment){
                        //echo $appoinment->originalnotificationTime.'<br>';
                        $finalcloserids = $this->checkavailableClosers($appoinment->originalnotificationTime,$closerids,$request->timezoneindex);
                        $timezone = $this->getTimezoneNameByIndex($request->timezoneindex);
                        $notificationdate = $this->getConvertedDateTime($appoinment->originalnotificationTime,'UTC',$timezone,'Y-m-d H:i:s');
                        //echo '<pre>';
                        //print_r($finalcloserids);
                        //echo '</pre>';
                        // if appoinments count is equal to closers count then its all booked
                        if(count(Appointment::where('originalnotificationTime',$appoinment->originalnotificationTime)->get()) == count($finalcloserids)){
                            $event_list[] = Calendar::event(
                                'All Booked',
                                false,
                                new \DateTime($notificationdate),
                                new \DateTime($notificationdate.'+30 minutes'),
                                'calevent_'.$appoinment->id,
                                array('color'=>"#ff9f89",'className'=>'fc-custom','editable'=>false,'overlap'=>false)
                            );
                            // if appoinment is greater than closer id then it is unavailable
                        }else if(count(Appointment::where('originalnotificationTime',$appoinment->originalnotificationTime)->get()) > count($finalcloserids)){
                            $event_list[] = Calendar::event(
                                'Unavailable',
                                false,
                                new \DateTime($notificationdate),
                                new \DateTime($notificationdate.'+30 minutes'),
                                'calevent_'.$appoinment->id,
                                array('color'=>"#ff9f89",'className'=>'fc-unavailable','editable'=>false,'overlap'=>false)
                            );
                        }
                }
        }
        $businessHours = [["start" => "08:00", "end" => "12:00", "dow" => [1,2,3,4,5],"className"=>"fc-nonbusiness"],["start" => "14:00", "end" => "17:00", "dow" => [1,2,3,4,5]]];
        $calendar_details = Calendar::addEvents($event_list)->setOptions(['defaultView' => 'agendaWeek','businessHours'=>$businessHours])->setCallbacks([
                       'header'=> '{
                        left: "prev,next today",
                        center: "title",
                        right: "agendaWeek,agendaDay"
                }',
                'allDaySlot'=> 'false',						
                'editable'=> 'true',
                'weekends'=> 'false',
                'eventLimit'=> 'true', // allow "more" link when too many events
                'selectable'=> 'true',
                'selectHelper'=>'true',
                'eventColor'=>"'green'",
                'disableDragging'=>'true',
                'navLinks'=> 'true', // can click day/week names to navigate views
                /**'businessHours'=> '[ 
                    { start: "07:00",end: "12:00",dow: [1, 2, 3 ,4 , 5]},
                    { start: "14:00",end: "17:00",dow: [1, 2, 3 ,4 , 5]},
                ]',**/
                'select'=> 'function(start, end, jsEvent, view) {
                        //https://stackoverflow.com/questions/42871482/jquery-inarray-disabling-days-with-select-option-in-fullcalendar
                        var events = $("div[id^=\"calendar\"]").fullCalendar("clientEvents");
                        var businessHours = $("div[id^=\"calendar\"]").fullCalendar("option","businessHours");
                        console.log(businessHours);
                        console.log(events);
                        if (moment().diff(start, "days") > 0) {
                            alert("Please select Future date");
                            $("div[id^=\"calendar\"]").fullCalendar("unselect");
                            return false;
                        }
                        if($("#timezoneindex").val() == ""){
                                alert("Please select timezone");
                        }else{								
                            $("div[id^=\"eventmodal\"]").attr("id","eventmodal"+Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15));
                            // Display the modal.
                            // You could fill in the start and end fields based on the parameters
                            $("div[id^=\"eventmodal\"]").modal("show");
                            $("div[id^=\"eventmodal\"]").find("#time-of-appointment-local").val(moment(start).format("MM/DD/YYYY hh:mm A"));
                        }
                }',
                'eventClick' => 'function(event) { 
                        console.log("You clicked on an event!");
                        console.log(event);
                        // Display the modal and set the values to the event values.
                        $("div[id^=\"eventmodal\"]").modal("show");
                        $("div[id^=\"eventmodal\"]").find("#name").val(event.title);
                        $("div[id^=\"eventmodal\"]").find("#time-of-appointment-local").val(moment(event.start).format("MM/DD/YYYY hh:mm A"));
                }',
                'dayClick'=>'function(date, jsEvent, view) {
                    if (jsEvent.target.classList.contains("fc-custom")) {
                        alert("Click Background Event Area");
                    }
                }',
                'eventRender'=>'function (event, element) {
                    console.log(event);
                }'
        ]);          
        $timezones = Setting::TIMEZONES;
        return view("contact.$user->role.calendar", compact('contact','calendar_details','timezones'));
    }
    
    public function closercalendar(Request $request)
    {
        $user = Auth::user();        
        $contact = Contact::find($request->id);
        $appointment = Appointment::find($request->appointmentid);
        $event_list = [];
        $appoinments = [];
        $availablehours = [];
        if(isset($request->timezoneindex)){ // Display only blocked dates                    
                $closerlist = User::where('enabled', true)->where('id',$appointment->assigned_to)->where('role', USER::CLOSER)->get();                
                $closerids = [];
                foreach($closerlist as $closer){
                    $timezone = Setting::select('value')->where('key','timezone_' . $closer->id)->get();
                    $workhours = Setting::select('value')->where('key', 'workhours_' . $closer->id)->get();
                    if(isset($workhours[0]) && isset($timezone[0])){
                        $closerids[] = $closer->id;
                        $closertimezone = $timezone[0]->value;                        
                        foreach (json_decode($workhours[0]->value) as $k=>$day){
                            if($k < 5){ // Monday to friday only                                 
                                $timing ='';
                                $timezone = $this->getTimezoneNameByIndex($request->timezoneindex);
                                if(!empty(json_decode($workhours[0]->value)[$k]->timeFrom)){
                                    $todayFromTime = date('Y-m-d').' '.json_decode($workhours[0]->value)[$k]->timeFrom.':00';
                                    // UTC to selectedTimezone conversion
                                    $timeFrom = $this->getConvertedDateTime($todayFromTime,'UTC',$timezone, 'H:i');                                    
                                    $timing .= $timeFrom;
                                }
                                if(!empty($timing)){
                                    if(!empty(json_decode($workhours[0]->value)[$k]->timeTill)){
                                        $todayTillTime = date('Y-m-d').' '.json_decode($workhours[0]->value)[$k]->timeTill.':00';
                                        // UTC to selectedTimezone conversion
                                        $timeTill = $this->getConvertedDateTime($todayTillTime,'UTC',$timezone, 'H:i');
                                        $timing .= '-'.$timeTill;
                                    }
                                }
                                if(!empty($timing)){
                                    $availablehours[++$k][] = $timing;                                        
                                }
                            }
                        }
                    }
                }
                for($t=1;$t<6;$t++){          
                    if(!isset($availablehours[$t])){                            
                        // if no closers available for particular day ..then block that day in all months
                        $event_list[] = Calendar::event('Unavailable', false, '00:00', '23:59','calhbevn',array('dow'=>[$t],'className'=>'fc-unavailable'));
                    }
                }
                // TO handle no closer avilable timings                                   
                $favailablehrs = [];
                foreach($availablehours as $kindex=>$available){
                    foreach($available as $k=>$avail){
                        $timerange = explode('-',$avail);
                        $favailablehrs[$kindex][] = $this->create_time_range($timerange[0],$timerange[1],'5 mins','24');   
                    }                        
                }
                foreach($favailablehrs as $mindex=>$favailable){
                    // handling all closer timings
                    if(count($favailable) > 1){
                        $inter = [];
                        foreach($favailable as $k=>$fav){
                            foreach($fav as $f){
                                $inter[] = $f;
                            }
                        }
                        $timings = $this->getTimeranges($inter);
                    }else{
                        // handling only one closer timing
                        $timings = $this->getTimeranges($favailable[0]);
                    }                    
                    foreach($timings as $cuitems){
                        $fitem = reset($cuitems);
                        $litem = end($cuitems);
                        if($litem == '00:00'){
                            $litem = '23:59';
                        }
                        $event_list[] = Calendar::event('Unavailable', false, $fitem, $litem,'calhbevn',array('dow'=>[$mindex],'className'=>'fc-unavailable'));
                    }
                }                    
                if(count($closerids) > 0){                    
                    $appoinments = Appointment::orderBy('id','DESC')->whereIn('assigned_to',$closerids)->groupBy('originalnotificationTime')->get();
                }
        }
        if(count($appoinments) > 0){            
                foreach($appoinments as $key => $appoinment){                      
                        $finalcloserids = $this->checkavailableClosers($appoinment->originalnotificationTime,$closerids,$request->timezoneindex);
                        $timezone = $this->getTimezoneNameByIndex($request->timezoneindex);                       
                        $notificationdate = $this->getConvertedDateTime($appoinment->originalnotificationTime,'UTC',$timezone,'Y-m-d H:i:s');                                                
                        // if appoinments count is equal to closers count then its all booked
                        if(count(Appointment::where('originalnotificationTime',$appoinment->originalnotificationTime)->get()) == count($finalcloserids)){
                            $event_list[] = Calendar::event(
                                'All Booked',
                                false,
                                new \DateTime($notificationdate),
                                new \DateTime($notificationdate.'+30 minutes'),
                                'calevent_'.$appoinment->id,
                                array('color'=>"#ff9f89",'className'=>'fc-custom','editable'=>false,'overlap'=>false)
                            );
                            // if appoinment is greater than closer id then it is unavailable
                        }else if(count(Appointment::where('originalnotificationTime',$appoinment->originalnotificationTime)->get()) > count($finalcloserids)){
                            $event_list[] = Calendar::event(
                                'Unavailable',
                                false,
                                new \DateTime($notificationdate),
                                new \DateTime($notificationdate.'+30 minutes'),
                                'calevent_'.$appoinment->id,
                                array('color'=>"#ff9f89",'className'=>'fc-unavailable','editable'=>false,'overlap'=>false)
                            );
                        }
                }
        }
        $businessHours = [["start" => "08:00", "end" => "12:00", "dow" => [1,2,3,4,5],"className"=>"fc-nonbusiness"],["start" => "14:00", "end" => "17:00", "dow" => [1,2,3,4,5]]];
        $calendar_details = Calendar::addEvents($event_list)->setOptions(['defaultView' => 'agendaWeek','businessHours'=>$businessHours])->setCallbacks([
                       'header'=> '{
                        left: "prev,next today",
                        center: "title",
                        right: "agendaWeek,agendaDay"
                }',
                'allDaySlot'=> 'false',						
                'editable'=> 'true',
                'weekends'=> 'false',
                'eventLimit'=> 'true', // allow "more" link when too many events
                'selectable'=> 'true',
                'selectHelper'=>'true',
                'eventColor'=>"'green'",
                'disableDragging'=>'true',
                'navLinks'=> 'true', // can click day/week names to navigate views
                /**'businessHours'=> '[ 
                    { start: "07:00",end: "12:00",dow: [1, 2, 3 ,4 , 5]},
                    { start: "14:00",end: "17:00",dow: [1, 2, 3 ,4 , 5]},
                ]',**/
                'select'=> 'function(start, end, jsEvent, view) {
                        //https://stackoverflow.com/questions/42871482/jquery-inarray-disabling-days-with-select-option-in-fullcalendar
                        var events = $("div[id^=\"calendar\"]").fullCalendar("clientEvents");
                        var businessHours = $("div[id^=\"calendar\"]").fullCalendar("option","businessHours");
                        console.log(businessHours);
                        console.log(events);
                        if (moment().diff(start, "days") > 0) {
                            alert("Please select Future date");
                            $("div[id^=\"calendar\"]").fullCalendar("unselect");
                            return false;
                        }
                        if($("#timezoneindex").val() == ""){
                                alert("Please select timezone");
                        }else{								
                            $("div[id^=\"eventmodal\"]").attr("id","eventmodal"+Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15));
                            // Display the modal.
                            // You could fill in the start and end fields based on the parameters
                            $("div[id^=\"eventmodal\"]").modal("show");
                            $("div[id^=\"eventmodal\"]").find("#time-of-appointment-local").val(moment(start).format("MM/DD/YYYY hh:mm A"));
                        }
                }',
                'eventClick' => 'function(event) { 
                        console.log("You clicked on an event!");
                        console.log(event);
                        // Display the modal and set the values to the event values.
                        $("div[id^=\"eventmodal\"]").find("#save-event").attr("name",event.id);
                        $("div[id^=\"eventmodal\"]").modal("show");
                        $("div[id^=\"eventmodal\"]").find("#name").val(event.title);
                        $("div[id^=\"eventmodal\"]").find("#time-of-appointment-local").val(moment(event.start).format("MM/DD/YYYY hh:mm A"));
                }',
                'dayClick'=>'function(date, jsEvent, view) {
                    if (jsEvent.target.classList.contains("fc-custom")) {
                        alert("Click Background Event Area");
                    }
                }',
                'eventRender'=>'function (event, element) {
                    console.log(event);
                }'
            ]);          
            $timezones = Setting::TIMEZONES;
            return view("contact.$user->role.calendar", compact('contact','calendar_details','timezones','appointment'));
    }
    
    public function reqcalendar(Request $request)
    {
        $user = Auth::user();
        $id = $request->id;
		
            $contact = Contact::find($id);
            $event_list = [];            
            $appoinments = Appointment::where('assigned_to',$user->id)->get();
            foreach($appoinments as $key => $appoinment){                      
                $event_list[] = Calendar::event(
                    'Booked - '.$appoinment->name,
                    false,
                    new \DateTime($appoinment->originalnotificationTime),
                    new \DateTime($appoinment->originalnotificationTime.'+30 minutes'),
                    'calevent_'.$appoinment->id,
                    array('color'=>"#ff9f89",'className'=>'fc-custom','editable'=>false,'overlap'=>false)
                );
            }
            $businessHours = [["start" => "08:00", "end" => "12:00", "dow" => [1,2,3,4,5],"className"=>"fc-nonbusiness"],["start" => "14:00", "end" => "17:00", "dow" => [1,2,3,4,5]]];
            $calendar_details = Calendar::addEvents($event_list)->setOptions(['defaultView' => 'agendaWeek','businessHours'=>$businessHours])->setCallbacks([
                           'header'=> '{
                            left: "prev,next today",
                            center: "title",
                            right: "agendaWeek,agendaDay"
                    }',
                    'allDaySlot'=> 'false',						
                    'editable'=> 'true',
                    'weekends'=> 'false',
                    'eventLimit'=> 'true', // allow "more" link when too many events
                    'selectable'=> 'true',
                    'selectHelper'=>'true',
                    'eventColor'=>"'green'",
                    'disableDragging'=>'true',
                    'navLinks'=> 'true', // can click day/week names to navigate views
                    /**'businessHours'=> '[ 
                        { start: "07:00",end: "12:00",dow: [1, 2, 3 ,4 , 5]},
                        { start: "14:00",end: "17:00",dow: [1, 2, 3 ,4 , 5]},
                    ]',**/
                    'select'=> 'function(start, end, jsEvent, view) {
                            //https://stackoverflow.com/questions/42871482/jquery-inarray-disabling-days-with-select-option-in-fullcalendar
                            var events = $("div[id^=\"calendar\"]").fullCalendar("clientEvents");
                            var businessHours = $("div[id^=\"calendar\"]").fullCalendar("option","businessHours");
                            console.log(businessHours);
                            console.log(events);
                            if (moment().diff(start, "days") > 0) {
                                alert("Please select Future date");
                                $("div[id^=\"calendar\"]").fullCalendar("unselect");
                                return false;
                            }
                            if($("#timezoneindex").val() == ""){
                                    alert("Please select timezone");
                            }else{								
                                $("div[id^=\"eventmodal\"]").attr("id","eventmodal"+Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15));
                                // Display the modal.
                                // You could fill in the start and end fields based on the parameters
                                $("div[id^=\"eventmodal\"]").modal("show");
                                $("div[id^=\"eventmodal\"]").find("#time-of-appointment-local").val(moment(start).format("MM/DD/YYYY hh:mm A"));
                            }
                    }',
                    'eventClick' => 'function(event) { 
                            console.log("You clicked on an event!");
                            console.log(event);
                            // Display the modal and set the values to the event values.
                            $("div[id^=\"eventmodal\"]").modal("show");
                            $("div[id^=\"eventmodal\"]").find("#name").val(event.title);
                            $("div[id^=\"eventmodal\"]").find("#time-of-appointment-local").val(moment(event.start).format("MM/DD/YYYY hh:mm A"));
                    }',
                    'dayClick'=>'function(date, jsEvent, view) {
                        if (jsEvent.target.classList.contains("fc-custom")) {
                            alert("Click Background Event Area");
                        }
                    }',
                    'eventRender'=>'function (event, element) {
                        console.log(event);
                    }'
            ]);          
            $timezones = Setting::TIMEZONES;
            return view("contact.$user->role.calendar", compact('contact','calendar_details','timezones'));
    }
    /**
     * It will time ranges for blocking ..which are unavailable timings
     * @param type $timeranges
     * @return type
     */
    private function getTimeranges($timeranges){
        $wholeday = $this->create_time_range('00:05', '24:00', '5 mins', '24');
        $items = array_diff($wholeday,$timeranges);
        $length = count($items);
        $cuitem = [];
        $iindex = 0;
        $lastindex = $length - 1;

        for($i = 0; $i < $lastindex; ++$i) {
            $citem = current($items);
            $nitem = next($items);
            $start_t = new \DateTime($citem);
            $current_t = new \DateTime($nitem);
            $difference = $start_t ->diff($current_t );
            $diff = $difference ->format('%I');            
            if ($diff == '05') {
                $endTime = strtotime("-5 minutes", strtotime($citem));
                $cuitem[$iindex][] = date('H:i', $endTime);
            }else{
                $endTime = strtotime("+5 minutes", strtotime($citem));                
                $cuitem[$iindex][] = date('H:i', $endTime);
                ++$iindex;                
            }
        }
      return $cuitem;
    }

    function checktime($arr,$time) {
        list($h,$m)=explode(":",$time);
        if(!is_array($arr[0])) {
            $r1=explode(":",$arr[0]);
            $r2=explode(":",$arr[1]);

            if($r1[0]>$r2[0]) {
                if(($h>$r1[0] || ($h==$r1[0] && $m>=$r1[1])) || ($h<$r2[0] || ($h==$r2[0] && $m<=$r2[1]))) return true;
            }
            else {
                if(($h>$r1[0] || ($h==$r1[0] && $m>=$r1[1])) && ($h<$r2[0] || ($h==$r2[0] && $m<=$r2[1]))) return true;
            }
        }
    }
    
    /**
     * It will return closers according to given hours
     * @param type $datetime
     * @param type $closerids
     * @return type
     */
    private function checkavailableClosers($datetime,$closerids,$timezoneindex){
        $dt = Carbon::parse($datetime);
        $finalcloserids = [];
        foreach($closerids as $closerid){
            $workhours = Setting::select('value')->where('key', 'workhours_' . $closerid)->get();
            $index = $dt->dayOfWeekIso;
            // identify here exactly ..closer available in this given time
            if(isset($workhours[0])){
                $exactday = json_decode($workhours[0]->value)[--$index];
                if($exactday->isActive == 'true'){
                    //print_r($exactday);
                    $hm = str_pad($dt->hour, 2, '0', STR_PAD_LEFT).':'.str_pad($dt->minute, 2, '0', STR_PAD_LEFT);
                    //echo 'HM'.$hm.'CID'.$closerid;
                    $fromtill = array($exactday->timeFrom,$exactday->timeTill);
                    //echo '<pre>';
                    //print_r($fromtill);
                    //echo '</pre>';
                    //echo 'STATUS'.$this->checktime($fromtill,$hm).'#';
                    // check all closerid available in particular date & time
                    if($this->checktime($fromtill,$hm)){
                        //echo count(Appointment::where('originalnotificationTime',$datetime)->whereIn('assigned_to',array($closerid))->get());
                        // if available check if he is already have appointment on same time & date
                        //if(count(Appointment::where('originalnotificationTime',$datetime)->whereIn('assigned_to',array($closerid))->get()) == 0){
                            $finalcloserids[] = $closerid;
                        //}
                    }
                }
            }
        }
        //echo '<pre>';
        //print_r($finalcloserids);
        //echo '</pre>';
        return $finalcloserids;
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $contact = Contact::find($id);
        $type = $contact->type;

        return view("contact.$type.edit", compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $contact = Contact::find($id);
        $type = $contact->type;

        $validate = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ];

        if ('company' == $contact->type) {
            $validate = array_merge($validate, [
                'company_name' => 'required|string|max:255',
            ]);
        }

        request()->validate($validate);
        $request->merge(['type' => $type]);

        $contact->update($request->all());

        return redirect()->route("contact.index", ['type' => $type])->with('alert', ['class' => 'success', 'message' => 'Contact updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $contact = Contact::find($id);

        if (0 < $contact->calls->count()) {
            return redirect()->route('contact.index', ['type' => $contact->type])->with('alert', ['class' => 'danger', 'message' => 'Contact didn\'t deleted, Has calls']);
        } else {
            $contact->delete();

            return redirect()->route('contact.index', ['type' => $contact->type])->with('alert', ['class' => 'success', 'message' => 'Contact deleted successfully']);
        }
    }
    
    public function closerquestion(Request $request){
        $user = Auth::user();
        $contact = Contact::find($request->contactid);
        $questions = Call::QUESTION['closer'];
        $app_recycled = DB::table('settings')->where('key', 'app_recycled')->first()->value * 24 * 3600;
        if ($request->isMethod('post')) {
            if ($request->has('answer')) {
                // After answering call will be created
                $call = Call::create($request->all());
                $call->contact()->associate($contact);
                $call->user()->associate($user);
                $call->save();
                
                // if that is follow up calls then delete old one
                ScheduleCall::join('calls', 'schedule_calls.call_id', '=', 'calls.id')
                    ->where('calls.contact_id', $contact->id)
                    ->where('calls.user_id', $user->id)
                    ->delete();
                
                if (in_array($request->input('answer'), [Call::ANSWER_PROGRESS])) {
                    if ($request->input('status') == Call::ANSWER_PROGRESS) {
                        $app_recycled = (new Carbon($request->input('date').' '.$request->input('time')))->diffInSeconds(Carbon::now());
                    }
                    // schedulecall created in the case of follow ups
                    $schedule = new ScheduleCall();
                    $schedule->remind_me_at = $app_recycled;
                    $schedule->call()->associate($call);
                    $schedule->save();
                } else {
                    DB::table('queues')
                        ->where('contact_id', $contact->id)
                        ->delete();
                }
                return redirect()->route("appoinments")->with('alert', [
                    'class' => 'success',
                    'message' => 'Answer saved successfully'
                ]);
            }else {
                return view("contact.closer.question", compact('contact', 'questions'))->with('alert', [
                    'class' => 'danger',
                    'message' => 'Choose an answer please!!'
                ]);
            }
        }
        return view("contact.closer.question", compact('contact', 'questions'));
    }
    
    public function invoicelist(Request $request){
        $user = Auth::user();
        $invoices = Invoice::where('status', 0)->get();
        
        $apikey = DB::table('settings')->where('key', 'app_invoicedapikey')->first()->value;
        $sandbox = DB::table('settings')->where('key', 'app_apisandbox')->first()->value;
        $sandboxstatus = ($sandbox == "yes")?true:false;
        $invoiced = new Client($apikey, $sandboxstatus);
        
        //$customer = $invoiced->Customer->retrieve("{CUSTOMER_ID}");
        return view("contact.$user->role.invoice", compact('invoices'));        
    }
    
    public function invoice(Request $request){
        if ($request->isMethod('post')) {
            $apikey = DB::table('settings')->where('key', 'app_invoicedapikey')->first()->value;
            $sandbox = DB::table('settings')->where('key', 'app_apisandbox')->first()->value;
            $sandboxstatus = ($sandbox == "yes")?true:false;
            $invoiced = new Client($apikey, $sandboxstatus);
            //create customer
            $customer = $invoiced->Customer->create([
                'name' => $request->first_name.' '.$request->last_name,
                'email' => $request->email,
                'number' => "CUST-".random_int(00000, 99999),
                'autopay' => true
            ]);
            // retrieve customer
            $existcustomer = $invoiced->Customer->retrieve($customer->id);
            stream_context_set_default(
                array(
                    'http' => array(
                        'method' => 'HEAD'
                    )
                )
            );

            $file = "https://s3.amazonaws.com/dialer.monikl.net/TermsOfService/reQruited/reQruited_terms_and_conditions.pdf";
            $headers = get_headers($file, 1);
            $headers = array_change_key_case($headers);        
            $file = $invoiced->File->create([
                'url' => $file,
                'name' => "reQruited_terms_and_conditions.pdf",
                'size' => trim($headers['content-length'],'"'),
                'type' => "application/pdf"
            ]);
            // create contact for this customer
            $existcustomer->contacts()->create([
                'name' => $request->first_name.' '.$request->last_name,
                'email' => $request->email
            ]);
            $cycle = $request->monthterm[0];
            if($request->monthterm[0] != 3){
                $cycle = ($request->monthterm[0] == 6)?5:10;
            }
            $settings = [
                ['minv'=>1, 'maxv'=>10,'license'=>100,'fee'=>100],
                ['minv'=>11, 'maxv'=>50,'license'=>75,'fee'=>250],
                ['minv'=>51, 'maxv'=>100,'license'=>50,'fee'=>500],
                ['minv'=>100, 'maxv'=>1000000,'license'=>25,'fee'=>1000]
            ];

            $settingobj = array_values(array_filter($settings,function($v,$k) use ($request){
              return $v['minv'] <= $request->noofusers && $request->noofusers <= $v['maxv'];
            }, ARRAY_FILTER_USE_BOTH));
            
            if($request->paymentterm[0] == 'onetime'){
                if($request->monthterm[0] != 3){
                    $amount = ($request->noofusers*$settingobj[0]['license'])*$cycle*0.90;
                }else{
                    $amount = ($request->noofusers*$settingobj[0]['license'])*$cycle;
                }
                $invoice = $invoiced->Invoice->create([
                    'customer' => $existcustomer->id,  
                    'items' => [
                      [
                         'name'=>$request->noofusers.' Users for '.$request->monthterm[0].' month contract',
                          'description'=>'One time payment',
                          'quantity'=>$request->noofusers,
                          'unit_cost'=>$amount/$request->noofusers
                      ],
                      [
                        'name' => "SetupCost",
                        'description'=>'One time setup fee',
                        'quantity' => 1,
                        'unit_cost' => $settingobj[0]['fee']
                      ]
                    ],
                  ]);                
                $invoice->attachments = [$file->id];
                if($invoice->save()){
                    $emails = $invoice->send();
                    $contact = Contact::find($request->id);
                    $user = Auth::user();
                    // once invoice created remove that reqruited user from that contact link
                    $contact->update(['user_id' => null]);
                    // remove from queues also
                    DB::table('queues')->where('contact_id', $contact->id)->delete();                
                    if($cycle != 3){
                        $months = ($cycle == 6)?5:10;
                        $amount = ($request->noofusers*$settingobj[0]['fee']*$months)*0.90;                    
                    }else
                        $amount = $request->noofusers*$settingobj[0]['fee']*$cycle;
                    $linvoice = new Invoice();
                    $linvoice->customerid = $existcustomer->id;
                    $linvoice->customername = $existcustomer->name;
                    $linvoice->status = 0;
                    $linvoice->amount = $amount;
                    $linvoice->user_id = $user->id;
                    $linvoice->contact_id = $contact->id;
                    $linvoice->save();                    
                    return response()->json(['success' => true, 'url' => route("contact.get", ['type' => $user->role])], 200);                    
                }
            }else{
                $setupitem = $existcustomer->lineItems()->create([
                    //'catalog_item' => "setupfee"
                    'name'=>'SetupCost',
                    'description'=>'Applicable only for first month',
                    'quantity'=>1,
                    'unit_cost'=>$settingobj[0]['fee']
                ]);
                // create subscription for this customer
                $subscriptionitem = $invoiced->Subscription->create([
                    'customer' => $existcustomer->id,
                    'plan' => "subscription-100monthuser",
                    'quantity'=>$request->noofusers,
                    'cycles'=>$cycle,
                     //"start_date"=> Carbon::now()->addMonths(1)->timestamp
                    "start_date"=> Carbon::now()->timestamp
                ]);
                // look up the subscription invoice
                list($invoices, $metadata) = $invoiced->Invoice->all(['per_page' => 1, 'filter' => ['subscription' => $subscriptionitem->id]]);
                $subscriptionInvoice = $invoices[0];
                if($cycle != 3){
                    $months = ($cycle == 6)?5:10;
                    $amount = ($request->noofusers*$settingobj[0]['fee']*$months)*0.90;                    
                }else
                    $amount = $request->noofusers*$settingobj[0]['fee']*$cycle;

                // attach the file to the invoice
                $subscriptionInvoice->attachments = [$file->id];
                if($subscriptionInvoice->save()){
                    $contact = Contact::find($request->id);
                    $user = Auth::user();
                    // once invoice created remove that reqruited user from that contact link
                    $contact->update(['user_id' => null]);
                    // remove from queues also
                    DB::table('queues')->where('contact_id', $contact->id)->delete();
                    $linvoice = new Invoice();
                    $linvoice->customerid = $existcustomer->id;
                    $linvoice->customername = $existcustomer->name;
                    $linvoice->status = 0;
                    $linvoice->amount = $amount;
                    $linvoice->user_id = $user->id;
                    $linvoice->contact_id = $contact->id;
                    $linvoice->save();
                    return response()->json(['success' => true, 'url' => route("contact.get", ['type' => $user->role])], 200);
                }                
            }
        }
    }
    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function question(Request $request, $id)
    {
        $user = Auth::user();
        $contact = Contact::find($id);
        $type = $user->role;
        $questions = Call::QUESTION[$type];
        $app_recycled = DB::table('settings')->where('key', 'app_recycled')->first()->value * 24 * 3600;

        if ($request->isMethod('post')) {
            if ($request->has('answer')) {
                // After answering call will be created
                $call = Call::create($request->all());
                $call->contact()->associate($contact);
                $call->user()->associate($user);
                $call->save();
                // if that is follow up calls then delete old one
                ScheduleCall::join('calls', 'schedule_calls.call_id', '=', 'calls.id')
                    ->where('calls.contact_id', $contact->id)
                    ->where('calls.user_id', $user->id)
                    ->delete();

                $contact->update(['user_id' => null]);

                if (in_array($request->input('answer'), [Call::ANSWER_PROGRESS, Call::ANSWER_NOT_ANSWERED, Call::ANSWER_LEFT_MESSAGE])) {
                    if ($request->input('status') == Call::ANSWER_PROGRESS) {
                        $app_recycled = (new Carbon($request->input('date').' '.$request->input('time')))->diffInSeconds(Carbon::now());
                    }
                    $request->merge([
                        'datepicker' => $request->input('date').' '.$request->input('time'),
                    ]);
                    // schedulecall created in the case of follow ups
                    $schedule = new ScheduleCall();
                    $schedule->timezoneOffset = $request->input('timezoneOffset');
                    $format = 'Y-m-d H:i:s';
                    //die($this->getLocalFromUTC(date($format, strtotime($request->datepicker)),$request->timezone,$format));
                    $schedule->timezone = $request->timezone;
                    $schedule->originalremindmeat = Carbon::parse(date($format, strtotime($request->datepicker)));
                    $schedule->remind_me_at = $this->getLocalFromUTC(date($format, strtotime($request->datepicker)),$request->timezone,$format);
                    $schedule->call()->associate($call);
                    $schedule->save();
                } else {
                    DB::table('queues')
                        ->where('contact_id', $contact->id)
                        ->delete();
                }				
                return redirect()->route("contact.get", ['type' => $type])->with('alert', [
                    'class' => 'success',
                    'message' => 'Answer saved successfully'
                ]);
            } else {
                return view("contact.$type.question", compact('contact', 'type', 'questions'))->with('alert', [
                    'class' => 'danger',
                    'message' => 'Choose an answer please!!'
                ]);
            }
        }

        return view("contact.$type.question", compact('contact', 'type', 'questions'));
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function assign(Request $request)
    {
        $type = $request->query('type');

        DB::table('queues')
            ->where('type', $type)
            ->update([
                'user_id' => null,
                'enabled' => true
            ]);

        $schedule_calls = DB::table('schedule_calls')
            ->join('calls', 'schedule_calls.call_id', '=', 'calls.id')
            ->join('contacts', 'calls.contact_id', '=', 'contacts.id')
            ->where('contacts.type', $type)
            ->get();

        foreach ($schedule_calls as $item) {
            $queue = DB::table('queues')
                ->where('contact_id', $item->contact_id)
                ->where('type', $type)
                ->first();

            if ($queue != null) DB::table('queues')
                ->where('contact_id', $item->contact_id)
                ->where('type', $type)
                ->update([
                    'user_id' => $item->user_id,
                    'enabled' => false
                ]);
            else DB::table('queues')
                ->where('type', $type)
                ->insert([
                    'contact_id' => $item->contact_id,
                    'user_id' => $item->user_id,
                    'type' => $type,
                    'enabled' => true
                ]);
        }

        $c = DB::select('
select *
from calls
where ( `created_at`, `contact_id`, `answer` ) in(
    select `created_at`, `contact_id`, `answer`
    from calls 
    group by `created_at`, `contact_id`, `answer`
    having count(*) > 1 
)
        ');

        for ($i = 0; $i < count($c); $i+=2) {
            DB::delete("
delete from calls
where `created_at` = '".$c[$i]->created_at."'
and `contact_id` = ".$c[$i]->contact_id."
and `answer` = ".$c[$i]->answer."
");
        }

//        DB::update('UPDATE `calls` join `contacts` on `contacts`.`id` = `calls`.`contact_id` SET `answer` = 5  WHERE contacts.`type` = \'candidate\' and `calls`.`answer` = 6;');
//        DB::update('UPDATE `calls` join `contacts` on `contacts`.`id` = `calls`.`contact_id` SET `answer` = 5  WHERE contacts.`type` = \'company\' and `calls`.`answer` = 6;');
//        DB::update('UPDATE `calls` join `contacts` on `contacts`.`id` = `calls`.`contact_id` SET `answer` = 6  WHERE contacts.`type` = \'company\' and `calls`.`answer` = 7;');
//        DB::update('UPDATE `calls` join `contacts` on `contacts`.`id` = `calls`.`contact_id` SET `answer` = 7  WHERE contacts.`type` = \'company\' and `calls`.`answer` = 8;');

        return redirect()->route('contact.index', ['type' => $type])->with('alert', [
            'class' => 'success',
            'message' => 'Migration done successfully'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function follow_ups(Request $request)
    {
        $user = Auth::user();
        if ($request->has('contact')) {
            $contact = Contact::find($request->query('contact'));
            $email = EmailTemplate::where('use_me', 1)->where('type', $user->role)->first();
            $subject = $email->subject;
            $message = $email->message;
            if ($request->has('contact') && $request->has('call')) {
                // Route::input('call', $request->query('call'));
                $request->session()->put('call', Call::find($request->query('call')));
            }

            return view("contact.$user->role.show", compact('contact', 'subject', 'message'));
        }
        $calls = Contact::join('calls', 'contacts.id', '=', 'calls.contact_id')
            ->join('schedule_calls', 'schedule_calls.call_id', '=', 'calls.id')
            ->where('calls.user_id', $user->id)
            ->where('calls.answer', Call::ANSWER_PROGRESS)
            ->where('contacts.enabled', 1)
            ->get();

        return view("contact.$user->role.follow_ups", compact('calls'));
    }

    private function type($type)
    {
        return in_array($type, User::ROLES) ? $type : 'company';
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $types = array_combine(Contact::TYPES, Contact::TYPES);

        if ($request->isMethod('post')) {
            $file = $request->file('file');
            $type = $request->input('type', Contact::COMPANY);

            if ($file->isFile() && $file->isReadable()) {
                $contactArr = $this->csvToArray($file);

                $skip = 0;
                for ($i = 0; $i < count($contactArr); $i++) {
                    if (!empty($contactArr[$i]['phone'])) {
                        $contact = Contact::where('phone', $contactArr[$i]['phone'])->first();

                        if (!$contact) {
                            $contact = Contact::firstOrNew($contactArr[$i]);
                            $contact->type = $type;
                            $contact->save();

                            DB::table('queues')->insert([
                                'contact_id' => $contact->id,
                                'type' => $type,
                                'enabled' => true,
                            ]);
                        }
                    } else {
                        $skip++;
                    }
                }
            }

            return redirect()->route("contact.import")->with('alert', [
                'class' => 'success',
                'message' => 'Contact imported successfully'
            ]);
        }

        // load the view and pass the contacts
        return view("contact.import.index", compact('types'));
    }

    /**
     * Update the contact for a CSV file..
     *
     * @param string $filename
     * @param string $delimiter
     * @return false|array
     */
    function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $header = null;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    public function importCSV($file, $type)
    {
        $contactArr = $this->csvToArray($file);

        for ($i = 0; $i < count($contactArr); $i++) {
            $contact = Contact::firstOrNew($contactArr[$i]);
            $contact->type = $type;
            $contact->save();

            DB::table('queues')->insert([
                'contact_id' => $contact->id,
                'type' => $type,
                'enabled' => true,
            ]);
        }

        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function email(Request $request)
    {
        $contact = Contact::find($request->input('contact'));
        $user = Auth::user();        
        if($user->role == 'closer'){
            $type = 'closer';
        }else{
            $type = $contact->type;
        }

        $emailtemplate = EmailTemplate::where('handle', 'sendemailbutton-'.$type)->first();
        $mail = \EmailTemplate::fetch('sendemailbutton-'.$type, ['Contactname'=>$contact->first_name . ' ' . $contact->last_name,
            'Callername' => $user->first_name.' '.$user->last_name,'Callerphone'=>$user->phone,'Position'=>$contact->position,
            'Affiliatelink'=>'<a href="' . $user->affiliate . '">Monikl.com</a>']); 
        
        $smtp = ($emailtemplate->smtp =='reqruited')?'reqruited_':'';
        $host = Setting::where('key', $smtp.'mail_host')->firstOrFail()->value;
        $port = Setting::where('key', $smtp.'mail_port')->firstOrFail()->value;
        $encryption = Setting::where('key', $smtp.'mail_encryption')->firstOrFail()->value;
        $username = Setting::where('key', $smtp.'mail_username')->firstOrFail()->value;
        $password = Setting::where('key', $smtp.'mail_password')->firstOrFail()->value;
        $app_email = Setting::where('key', 'app_'.$smtp.'email')->firstOrFail()->value;

        if (empty($host) || empty($port) || empty($username) || empty($password)) {
            return false;
        }

        $transport = new \Swift_SmtpTransport($host, (int)$port);
        $transport->setEncryption($encryption);
        $transport->setUsername($username);
        $transport->setPassword($password);

        $mailer = new \Swift_Mailer($transport);
        // Assign it to the Laravel Mailer
        Mail::setSwiftMailer($mailer);
        Mail::alwaysFrom($app_email, ucfirst(explode('.',substr($app_email, strpos($app_email, '@') + 1))[0]));
        Mail::alwaysReplyTo($app_email);
        try {
            $response = \Mail::to($contact->email)->send($mail);
            if(count(\Mail::failures()) == 0){
                return 1;
            }
        } catch (\Exception $e) {
            $response = $e->getMessage();
        }

        return $response;
    }
	
	public function getEventsJson(Request $request) {
        $events = Appointment::where('id', '>', $request->id)->get();

        $eventsJson = array();
        foreach ($events as $event) {
            $eventsJson[] = array(
                'id' => $event->id,
                'title' => $event->event_name,                
                'start' => $event->start_date,
				'end'=> $event->end_date,
				'allDay'=>false
            );
        }
        return response()->json($eventsJson);
    }
	
    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int $id
     * @return int
     */
    public function information(Request $request, $id)
    {
        $contact = Contact::find($id);

        $validate = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ];
        request()->validate($validate);

        $contact->update($request->all());

        return 1;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function deactivate_index(Request $request)
    {
        $contacts = Contact::where('enabled', 0)->get();

        // load the view and pass the contacts
        return view('contact.deactivate_index', compact('contacts'));
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function deactivate(Request $request)
    {
        $type = $request->get('type');
        $contacts = $request->get('contacts');
        $contacts = is_array($contacts) ? $contacts : [$contacts];

        for ($i = 0; $i < count($contacts); $i++) {
            $contact = Contact::find($contacts[$i]);
            $contact->enabled = 0;
            $contact->save();
        }

        return redirect()->route("contact.index", ['type' => $type])->with('alert', ['class' => 'success', 'message' => 'Contact(s) deactivated successfully']);
    }
	
	/**
     * Display the specified resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function downloadcsv(Request $request)
    {
        $type = $request->get('type');
        $contacts = $request->get('contacts');
        $contacts = is_array($contacts) ? $contacts : [$contacts];

        /*for ($i = 0; $i < count($contacts); $i++) {
            $contact = Contact::find($contacts[$i]);
            $contact->enabled = 0;
            $contact->save();
        }*/
		
        return redirect()->route("contact.index", ['type' => $type])->with('alert', ['class' => 'success', 'message' => 'CSV downloaded successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function activate(Request $request)
    {
        $type = $request->get('type');
        $contacts = $request->get('contacts');
        $contacts = is_array($contacts) ? $contacts : [$contacts];

        for ($i = 0; $i < count($contacts); $i++) {
            $user = Contact::find($contacts[$i]);
            $user->enabled = 1;
            $user->save();
        }

        return redirect()->route("contact.index", ['type' => $type])->with('alert', [
            'class' => 'success',
            'message' => 'Contact(s) activated successfully'
        ]);
    }
        
    private function getTimezoneNameByIndex($timezone){
        //America/Los_Angeles - PST - 1
        //America/Denver MST - 2
        //America/Chicago - CST - 3
        //America/New_York EST - 4

        if($timezone == 1){
            $timezonename = "America/Los_Angeles";
        }elseif($timezone == 2){
            $timezonename = "America/Denver";
        }elseif($timezone == 3){
            $timezonename = "America/Chicago";
        }else{
            $timezonename = "America/New_York";
        }
        
        return $timezonename;
    }
    
    private function getConvertedDateTime($datetime,$fromtimezone,$totimezone, $format){        
        $given = new \DateTime($datetime, new \DateTimeZone($fromtimezone));
        $given->setTimezone(new \DateTimeZone($totimezone));
        $output = $given->format($format); 
        return $output;
    }
    
    /** 
     * create time range
     *  
     * @param mixed $start start time, e.g., 7:30am or 7:30 
     * @param mixed $end   end time, e.g., 8:30pm or 20:30 
     * @param string $interval time intervals, 1 hour, 1 mins, 1 secs, etc.
     * @param string $format time format, e.g., 12 or 24
     */ 
    function create_time_range($start, $end, $interval = '30 mins', $format = '12') {
        $startTime = strtotime($start); 
        $endTime   = strtotime($end);
        $returnTimeFormat = ($format == '12')?'g:i:s A':'H:i';

        $current   = time(); 
        $addTime   = strtotime('+'.$interval, $current); 
        $diff      = $addTime - $current;

        $times = array(); 
        while ($startTime < $endTime) { 
            $times[] = date($returnTimeFormat, $startTime); 
            $startTime += $diff; 
        } 
        $times[] = date($returnTimeFormat, $startTime); 
        return $times; 
    }
}