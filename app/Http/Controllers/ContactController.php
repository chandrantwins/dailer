<?php

namespace App\Http\Controllers;

use App\User;
use App\Contact;
use App\Call;
use App\Email;
use App\ScheduleCall;
use App\Setting;
use App\Events;
use Calendar;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            return view("contact.$contact->type.show", compact('contact', 'subject', 'message'));
        } else {
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
                            ->first()
                            ->contact_id;
                        $contact = Contact::find($contact_id);
                    }
                }
            }
			$events = Events::get();
			$event_list = [];
			$calendar_details = [];
			if(count($events) > 0){
				foreach($events as $key => $event){
						$event_list[] = Calendar::event(
								$event->event_name,
								false,
								new \DateTime($event->start_date),
								new \DateTime($event->end_date. '+1 day')
						);
				}
				$calendar_details = Calendar::addEvents($event_list)->setOptions(['defaultView' => 'agendaWeek'])->setCallbacks([
						'header'=> '{
							left: "prev,next today",
							center: "title",
							right: "agendaWeek,agendaDay"
						}',
						'allDaySlot'=> 'false',						
						'editable'=> 'true',
						'eventLimit'=> 'true', // allow "more" link when too many events
						'selectable'=> 'true',
						'selectHelper'=>'true',
						'navLinks'=> 'true', // can click day/week names to navigate views
						'select'=> 'function(start, end, jsEvent, view) {
							if($(".select_timezone").val() == ""){
								alert("Please select timezone");
							}else{								
								$("div[id^=\"eventmodal\"]").attr("id","eventmodal"+Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15));
								// Display the modal.
								// You could fill in the start and end fields based on the parameters
								$("div[id^=\"eventmodal\"]").modal("show");
								$("div[id^=\"eventmodal\"]").find("#start_date").val(moment(start).format("YYYY-MM-DD HH:MM:SS"));
								$("div[id^=\"eventmodal\"]").find("#end_date").val(moment(end).format("YYYY-MM-DD HH:MM:SS"));
							}
						}',
						'eventClick' => 'function(event) {                                
								console.log("You clicked on an event!");
								console.log(event);
								// Display the modal and set the values to the event values.
								$("div[id^=\"eventmodal\"]").modal("show");
								$("div[id^=\"eventmodal\"]").find("#event_name").val(event.title);
								$("div[id^=\"eventmodal\"]").find("#start_date").val(moment(event.start).format("YYYY-MM-DD HH:MM:SS"));
								$("div[id^=\"eventmodal\"]").find("#end_date").val(moment(event.end).format("YYYY-MM-DD HH:MM:SS"));                                
						}'
				]);
			}

            return view("contact.$user->role.show", compact('contact', 'subject', 'message','calendar_details'));
        }
    }

	public function calendar(Request $request)
    {
        $user = Auth::user();
		$id = $request->id;
        if (in_array($user->role, [User::ADMIN, User::SUBADMIN])) {
            $contact = Contact::find($id);
            return view("contact.$contact->type.show", compact('contact', 'subject', 'message'));
        } else {
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
                            ->first()
                            ->contact_id;
                        $contact = Contact::find($contact_id);
                    }
                }
            }
			$events = Events::get();
			$event_list = [];
			$calendar_details = [];
			if(count($events) > 0){
				foreach($events as $key => $event){
						$event_list[] = Calendar::event(
								$event->event_name,
								false,
								new \DateTime($event->start_date),
								new \DateTime($event->end_date. '+1 day')
						);
				}
				$calendar_details = Calendar::addEvents($event_list)->setOptions(['defaultView' => 'agendaWeek'])->setCallbacks([
						'header'=> '{
							left: "prev,next today",
							center: "title",
							right: "agendaWeek,agendaDay"
						}',
						'allDaySlot'=> 'false',						
						'editable'=> 'true',
						'eventLimit'=> 'true', // allow "more" link when too many events
						'selectable'=> 'true',
						'selectHelper'=>'true',
						'navLinks'=> 'true', // can click day/week names to navigate views
						'select'=> 'function(start, end, jsEvent, view) {
							if($(".select_timezone").val() == ""){
								alert("Please select timezone");
							}else{								
								$("div[id^=\"eventmodal\"]").attr("id","eventmodal"+Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15));
								// Display the modal.
								// You could fill in the start and end fields based on the parameters
								$("div[id^=\"eventmodal\"]").modal("show");
								$("div[id^=\"eventmodal\"]").find("#start_date").val(moment(start).format("YYYY-MM-DD HH:MM:SS"));
								$("div[id^=\"eventmodal\"]").find("#end_date").val(moment(end).format("YYYY-MM-DD HH:MM:SS"));
							}
						}',
						'eventClick' => 'function(event) {                                
								console.log("You clicked on an event!");
								console.log(event);
								// Display the modal and set the values to the event values.
								$("div[id^=\"eventmodal\"]").modal("show");
								$("div[id^=\"eventmodal\"]").find("#event_name").val(event.title);
								$("div[id^=\"eventmodal\"]").find("#start_date").val(moment(event.start).format("YYYY-MM-DD HH:MM:SS"));
								$("div[id^=\"eventmodal\"]").find("#end_date").val(moment(event.end).format("YYYY-MM-DD HH:MM:SS"));                                
						}'
				]);
			}

            return view("contact.$user->role.calendar", compact('contact', 'subject', 'message','calendar_details'));
        }
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
        $type = $contact->type;
        $questions = Call::QUESTION[$type];
        $app_recycled = DB::table('settings')->where('key', 'app_recycled')->first()->value * 24 * 3600;

        if ($request->isMethod('post')) {
            if ($request->has('answer')) {
                $call = Call::create($request->all());
                $call->contact()->associate($contact);
                $call->user()->associate($user);
                $call->save();

                ScheduleCall::join('calls', 'schedule_calls.call_id', '=', 'calls.id')
                    ->where('calls.contact_id', $contact->id)
                    ->where('calls.user_id', $user->id)
                    ->delete();

                $contact->update(['user_id' => null]);

                if (in_array($request->input('answer'), [Call::ANSWER_PROGRESS, Call::ANSWER_NOT_ANSWERED, Call::ANSWER_LEFT_MESSAGE])) {
                    if ($request->input('status') == Call::ANSWER_PROGRESS) {
                        $app_recycled = (new Carbon($request->input('date').' '.$request->input('time')))->diffInSeconds(Carbon::now());
                    }

                    $schedule = new ScheduleCall();
                    $schedule->remind_me_at = $app_recycled;
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
            $email = Email::where('use_me', true)->where('type', $user->role)->first();
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
        $host = Setting::where('key', 'mail_host')->firstOrFail()->value;
        $port = Setting::where('key', 'mail_port')->firstOrFail()->value;
        $encryption = Setting::where('key', 'mail_encryption')->firstOrFail()->value;
        $username = Setting::where('key', 'mail_username')->firstOrFail()->value;
        $password = Setting::where('key', 'mail_password')->firstOrFail()->value;
        $app_email = Setting::where('key', 'app_email')->firstOrFail()->value;

        if (empty($host) || empty($port) || empty($username) || empty($password)) {
            return false;
        }

        $contact = Contact::find($request->input('contact'));
        $user = Auth::user();
        $email = Email::where('use_me', true)->where('type', $contact->type)->first();

        $message_subject = $email->subject;
        $message_subject = str_replace('{caller}', $user->first_name . ' ' . $user->last_name, $message_subject);
        $message_subject = str_replace('{company}', $contact->company_name, $message_subject);
        $message_subject = str_replace('{contact}', $contact->first_name . ' ' . $contact->last_name, $message_subject);
        $message_subject = str_replace('{position}', $contact->position, $message_subject);
        $message_subject = str_replace('{affiliate}', '<a href="' . $user->affiliate . '">Monikl.com</a>', $message_subject);

        $message_body = $email->message;
        $message_body = str_replace('{caller}', $user->first_name . ' ' . $user->last_name, $message_body);
        $message_body = str_replace('{company}', $contact->company_name, $message_body);
        $message_body = str_replace('{contact}', $contact->first_name . ' ' . $contact->last_name, $message_body);
        $message_body = str_replace('{position}', $contact->position, $message_body);
        $message_body = str_replace('{affiliate}', '<a href="' . $user->affiliate . '">Monikl.com</a>', $message_body);

        $transport = new \Swift_SmtpTransport($host, (int)$port);
        $transport->setEncryption($encryption);
        $transport->setUsername($username);
        $transport->setPassword($password);
        // $transport->setAuthMode('login');

        $mailer = new \Swift_Mailer($transport);

        $message = new \Swift_Message();
        $message->setFrom($app_email);
        $message->setTo($request->input('email'));
        // $message->setTo('makraz.hamza@gmail.com');
        $message->setBody($message_body, 'text/html');
        $message->setSubject($message_subject);

        try {
            $response = $mailer->send($message);
        } catch (\Exception $e) {
            $response = $e->getMessage();
        }

        return $response;
    }
	
	public function getEventsJson(Request $request) {
        $events = Events::where('id', '>', $request->id)->get();

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
	
	public function eventinformation(Request $request){		
		$contact = Contact::find($request->id);

        $validate = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ];
        request()->validate($validate);

        $contact->update($request->all());
		
		$validator = Validator::make($request->all(),[
			'event_name'=>'required',
			'start_date'=>'required',
			'end_date'=>'required'
		]);
		
		if($validator->fails()){
			return response()->json([
				'success' => 'false',
				'errors'  => $validator->errors()->all(),
			], 400);			
		}
		
		$event = new Events;
		$event->event_name = $request->event_name;
		$event->start_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->start_date);
		$event->end_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->end_date);		
		
		if($event->save()){
			return response()->json(['success' => true, 'events' => Events::get()], 200);
		}
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
}