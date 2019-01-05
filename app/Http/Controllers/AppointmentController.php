<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

use App\Http\Requests;
use App\Contact;
use App\User;
use App\Setting;
use App\Appointment;
use App\Call;
use Calendar;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    private $appointment;
    private $validInputConditions = array(
        //'name' => 'required',
        'phoneNumber' => 'required|min:5',
        'when' => 'required',
        'timezoneOffset' => 'required',
        'timezone' => 'required',
        'delta' => 'required|numeric'
    );

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
            $allAppointments = Appointment::orderBy('id', 'ASC')->where('assigned_to',Auth::user()->id)->get();
            $timezonesetting = Setting::select('value')->where('key','timezone_' . Auth::user()->id)->get();
            $timezone = $this->getTimezoneNameByIndex($timezonesetting[0]->value);            
            $event_list = [];
            foreach($allAppointments as $appoinment){
                $notificationdate = $this->getConvertedDateTime($appoinment->originalnotificationTime,'UTC',$timezone,'Y-m-d H:i:s');
                $event_list[] = Calendar::event(
                    $appoinment->name,
                    false,
                    new \DateTime($notificationdate),
                    new \DateTime($notificationdate.'+30 minutes'),
                    'calevent_'.$appoinment->id,
                    array('color'=>"#ff9f89",'className'=>'fc-custom','editable'=>false,'overlap'=>false,'url'=>'/closer/contactview/'.$appoinment->contact_id.'/'.$appoinment->id)
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
                        if($("#timezone").val() == ""){
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
        
        return response()->view('appointments.calendar', array('calendar_details' => $calendar_details));
    }

    /**
     * Display a list of appointments.
     *
     * @return Response
     */
    public function reqruitedindex()
    {
            $allAppointments = Appointment::orderBy('id', 'ASC')->where('assigned_to',Auth::user()->id)->get();
            $event_list = [];
            foreach($allAppointments as $appoinment){
                $event_list[] = Calendar::event(
                    $appoinment->name,
                    false,
                    new \DateTime($appoinment->originalnotificationTime),
                    new \DateTime($appoinment->originalnotificationTime.'+30 minutes'),
                    'calevent_'.$appoinment->id,
                    array('color'=>"#ff9f89",'className'=>'fc-custom','editable'=>false,'overlap'=>false,'url'=>'/contact/contactview/'.$appoinment->contact_id)
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
                        if($("#timezone").val() == ""){
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
        
        return response()->view('appointments.calendar', array('calendar_details' => $calendar_details));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $appointment = new Appointment;
        return \View::make('appointments.create', array('appointment' => $appointment));
    }

    public function information(Request $request){		
            $contact = Contact::find($request->id);
            $user = Auth::user();
            $validate = [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
            ];
            request()->validate($validate);

            $contact->update($request->all());

            $validator = Validator::make($request->all(),[
                    //'name'=>'required',
                    'when'=>'required'			
            ]);

            if($validator->fails()){
                    return response()->json([
                            'success' => 'false',
                            'errors'  => $validator->errors()->all(),
                    ], 400);			
            }
            $timezonename = $this->getTimezoneNameByIndex($request->input('timezoneindex'));
            $newAppointment = $this->appointmentFromRequest($request);
            // while creating time do this stuff this is related to selected timezone
            $format = 'Y-m-d H:i:s';
            $selectedDate = date($format, strtotime($request->input('when')));
            $dt = Carbon::parse($selectedDate);
            $dindex = $dt->dayOfWeekIso;
            $newAppointment->originalnotificationTime = $this->getConvertedDateTime($selectedDate,$timezonename,'UTC',$format);
            $newAppointment->created_by = $user->id; // company_id
            // assign closer to contact
            $closerusers = User::where('enabled', true)->where('role', USER::CLOSER)->inRandomOrder()->get();           
            $finalcloserids = [];
            foreach($closerusers as $closer){                
                $timezone = Setting::select('value')->where('key','timezone_' . $closer->id)->get();                
                if(isset($timezone[0]->value)){
                    //echo $newAppointment->originalnotificationTime.'ID'.$closer->id.'<br>';
                        // check whether this closer is available this hours and also check whether he is not already engaged with some other appointment
                        if($this->checkavailablity($newAppointment->originalnotificationTime,$closer->id,$dindex)){ 
                                //$appdate = $this->getDateByTimezone($request->input('when'), $timezone[0]->value , $request->input('timezoneindex'));
                                if(count(Appointment::where('originalnotificationTime',$newAppointment->originalnotificationTime)->where('assigned_to',$closer->id)->get()) == 0){                                    
                                    $finalcloserids[] = $closer->id;
                                }
                        }
                }
            }
            $finalcloser = User::where('enabled', true)->where('role', USER::CLOSER)->whereIn('id',$finalcloserids)->inRandomOrder()->first();
            if(isset($finalcloser)){
                $timezone = Setting::select('value')->where('key','timezone_' . $finalcloser->id)->get();
                //$appdate = $this->getDateByTimezone($request->input('when'), $timezone[0]->value , $request->input('timezoneindex'));
                $newAppointment->assigned_to = $finalcloser->id; // closer id - Automatically assigned by system
                $newAppointment->contact_id = $contact->id; // whom to call
                $newAppointment->timezone = $this->getTimezoneNameByIndex($timezone[0]->value);
                $newAppointment->when = $newAppointment->originalnotificationTime;
                
                if($newAppointment->save()){
                        // once appoinment created remove that company user from that contact link
                        $contact->update(['user_id' => null]);
                        // remove from queues also
                        DB::table('queues')->where('contact_id', $contact->id)->delete();                  
                        return response()->json(['success' => true, 'url' => route("contact.get", ['type' => $user->role])], 200);
                }
            }else{
                return response()->json(['success' => false,'errors'=>'Closer Not exist'], 400);
            }
	}

    public function closerinformation(Request $request){		
            $contact = Contact::find($request->id);
            $user = Auth::user();
            $validate = [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
            ];
            request()->validate($validate);

            $contact->update($request->all());

            $validator = Validator::make($request->all(),[
                    //'name'=>'required',
                    'when'=>'required'			
            ]);

            if($validator->fails()){
                    return response()->json([
                            'success' => 'false',
                            'errors'  => $validator->errors()->all(),
                    ], 400);			
            }            
            $format = 'Y-m-d H:i:s';
            $existingAppointment = Appointment::find($request->appointmentid);
            $selectedDate = date($format, strtotime($request->input('when')));
            $timezonename = $this->getTimezoneNameByIndex($request->input('timezoneindex'));
            $existingAppointment->originalnotificationTime = $this->getConvertedDateTime($selectedDate,$timezonename,'UTC',$format);
            $existingAppointment->notificationTime = $selectedDate;            
            if($existingAppointment->save()){
                    return response()->json(['success' => true, 'url' => route("appoinments")], 200);
            }
    }

    public function reqinformation(Request $request){		
            $contact = Contact::find($request->id);
            $user = Auth::user();
            $validate = [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
            ];
            request()->validate($validate);

            $contact->update($request->all());

            $validator = Validator::make($request->all(),[
                    //'name'=>'required',
                    'when'=>'required'			
            ]);

            if($validator->fails()){
                    return response()->json([
                            'success' => 'false',
                            'errors'  => $validator->errors()->all(),
                    ], 400);			
            }
            
            $newAppointment = $this->appointmentFromRequest($request);
            // while creating time do this stuff. this is self appointment
            $newAppointment->originalnotificationTime = $this->getLocalDatetimeFromUTC($request->input('when'),$request->input('timezoneOffset'));
            $newAppointment->created_by = $user->id; // company_id
            // Logged in user requser and contact should get appoinment
            $newAppointment->assigned_to = $user->id; // Automatically assigned to self
            $newAppointment->contact_id = $contact->id; // whom to call

            if($newAppointment->save()){
                    // once appoinment created remove that company user from that contact link
                    $contact->update(['user_id' => null]);
                    // remove from queues also
                    DB::table('queues')->where('contact_id', $contact->id)->delete();                  
                    return response()->json(['success' => true, 'url' => route("contact.get", ['type' => $user->role])], 200);
            }
	}
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $newAppointment = $this->appointmentFromRequest($request);		
        $newAppointment->save();
        return redirect()->route('appointments.index');
    }

    /**
     * Delete a resource in storage.
     *
     * @return Response
     */
    public function destroy($id) {
        Appointment::find($id)->delete();
        return redirect()->route('appointments.index');
    }

    public function edit($id) {
        $appointmentToEdit = Appointment::find($id);
        return \View::make('appointments.edit', array('appointment' => $appointmentToEdit));
    }

    public function update(Request $request, $id) {
        $updatedAppointment = $this->appointmentFromRequest($request);
        $existingAppointment = Appointment::find($id);

        $existingAppointment->name = $updatedAppointment->name;
        $existingAppointment->phoneNumber = $updatedAppointment->phoneNumber;
        $existingAppointment->timezoneOffset = $updatedAppointment->timezoneOffset;
        $existingAppointment->timezone = $updatedAppointment->timezone;
        $existingAppointment->when = $updatedAppointment->when;
        $existingAppointment->notificationTime = $updatedAppointment->notificationTime;

        $existingAppointment->save();
        return redirect()->route('appointments.index');
    }

     /**
     * Display a listing of the upcoming appointments.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function upcoming(Request $request)
    {
        $appointments = Appointment::orderBy('id', 'ASC')->whereBetween('originalnotificationTime', array(Carbon::now(), Carbon::now()->addWeek()))->get();
        $pagename = 'Upcoming';
        // load the view and pass the appointments
        return view('appointments.appointment', compact('appointments','pagename'));
    }

    /**
     * Display a listing of the finished appointments.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function finished(Request $request)
    {
        $status = [];
        $appointments = Appointment::orderBy('id', 'ASC')->whereDate('originalnotificationTime', '<', Carbon::now())->get();
        $pagename = 'Finished';
        foreach($appointments as $appointment){
            $call = Call::select('calls.answer')->orderBy('id','DESC')->limit(1)->where('contact_id',$appointment->contact_id)->where('user_id', $appointment->assigned_to)->get()->toArray();
            if(count($call) == 1){                
                $status[$appointment->contact_id.$appointment->assigned_to] = Call::QUESTION_SHORT['closer'][$call[0]['answer']];
            }            
        }
        // load the view and pass the appointments
        return view('appointments.appointmentstatus', compact('appointments','pagename','status'));
    }
	
	/**
     * JSON a listing of the resource.
     *
     * @return Response
     */
    public function calendarapi()
    {
            $get_timezone = 1 ; //GET TIMEZONE in REQUEST
			$allAppointments = Appointment::orderBy('id', 'ASC')->get(); //
            //$timezonesetting = Setting::select('value')->where('id',$get_timezone)->get();
            $timezone = $this->getTimezoneNameByIndex($get_timezone);            
            $event_list = [];
            foreach($allAppointments as $appoinment){
                $notificationdate = $this->getConvertedDateTime($appoinment->originalnotificationTime,'UTC',$timezone,'Y-m-d H:i:s');
				$event_list[] = array('title'=>$appoinment->name,'color'=>"#ff9f89",'className'=>'fc-custom','editable'=>false,'overlap'=>false,'start'=>new \DateTime($notificationdate),'end'=>new \DateTime($notificationdate.'+30 minutes'),'url'=>'/closer/contactview/'.$appoinment->contact_id.'/'.$appoinment->id);
				
            }
            $businessHours = [["start" => "08:00", "end" => "12:00", "dow" => [1,2,3,4,5],"className"=>"fc-nonbusiness"],["start" => "14:00", "end" => "17:00", "dow" => [1,2,3,4,5]]];
            //$calendar_details = Calendar::addEvents($event_list);          
			return response()->json(array('calendar_details' => $event_list));
    }

    private function appointmentFromRequest(Request $request) {		
        $this->validate($request, $this->validInputConditions);
        $newAppointment = new Appointment;

        $newAppointment->name = $request->input('name');
        $newAppointment->phoneNumber = $request->input('phoneNumber');
        $newAppointment->timezoneOffset = $request->input('timezoneOffset');
        $newAppointment->timezone = $request->input('timezone');
        $newAppointment->when = Carbon::parse($request->input('when'),$request->input('timezone'));
        $notificationTime = Carbon::parse($request->input('when'))->subMinutes($request->delta);
        $newAppointment->notificationTime = $notificationTime;

        return $newAppointment;
    }
	
    private function getLocalDatetimeFromUTC($dateTime, $offset){
        $hourOffset = -($offset / 60);
        $dateWithTimezone = new \DateTime($dateTime, new \DateTimeZone('UTC'));		
        $sign = $hourOffset < 0 ? '-' : '+';
        $timezone = new \DateTimeZone($sign . abs($hourOffset));
        $dateWithTimezone->setTimezone($timezone);
        return $dateWithTimezone->format('Y-m-d H:i:s');
    }

    private function checkavailablity($datetime,$closerid,$dindex){
        $dt = Carbon::parse($datetime);
        $workhours = Setting::select('value')->where('key', 'workhours_' . $closerid)->get();
        // identify here exactly ..closer available in this given time
        if(isset($workhours[0])){
            $exactday = json_decode($workhours[0]->value)[--$dindex];
            if($exactday->isActive == 'true'){  
                $hm = str_pad($dt->hour, 2, '0', STR_PAD_LEFT).':'.str_pad($dt->minute, 2, '0', STR_PAD_LEFT);

                $fromtill = array($exactday->timeFrom,$exactday->timeTill);
                //echo $datetime.'HM'.$hm;
                if($this->checktime($fromtill,$hm)){
                    return true;
                }
            }
        }
        return false;
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
}
