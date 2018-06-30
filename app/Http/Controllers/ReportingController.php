<?php

namespace App\Http\Controllers;

use App\User;
use App\Call;
use App\Contact;
use App\ScheduleCall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    /**
     * Show the reporting index.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Auth::User()->role == User::ADMIN) {
            $successful = Call::where('answer', 0)->orderBy('updated_at')->get();
            $unsuccessful = Call::where('answer', 2)->orderBy('updated_at')->get();
            $asked_removed = Call::where('answer', 3)->orderBy('updated_at')->get();
            $wrong_numbers = Call::where('answer', 6)->orderBy('updated_at')->get();
            $unanswered = Call::where('answer', 4)->orderBy('updated_at')->get();
            $gatekeeper = Call::where('answer', 5)->orderBy('updated_at')->get();

            $blacklist = Call::whereIn('answer', [2, 3, 6])->get();
        } elseif (Auth::User()->role == User::SUBADMIN) {
            $successful = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
                ->join('users', 'users.id', '=', 'contacts.user_id')
                ->where('users.user_id', Auth::User()->id)
                ->where('answer', 0)
                ->orderBy('calls.updated_at')
                ->get();
            $unsuccessful = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
                ->join('users', 'users.id', '=', 'contacts.user_id')
                ->where('users.user_id', Auth::User()->id)
                ->where('answer', 2)
                ->orderBy('calls.updated_at')
                ->get();
            $asked_removed = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
                ->join('users', 'users.id', '=', 'contacts.user_id')
                ->where('users.user_id', Auth::User()->id)
                ->where('answer', 3)
                ->orderBy('calls.updated_at')
                ->get();
            $wrong_numbers = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
                ->join('users', 'users.id', '=', 'contacts.user_id')
                ->where('users.user_id', Auth::User()->id)
                ->where('answer', 6)
                ->orderBy('calls.updated_at')
                ->get();
            $unanswered = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
                ->join('users', 'users.id', '=', 'contacts.user_id')
                ->where('users.user_id', Auth::User()->id)
                ->where('answer', 4)
                ->orderBy('calls.updated_at')
                ->get();
            $gatekeeper = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
                ->join('users', 'users.id', '=', 'contacts.user_id')
                ->where('users.user_id', Auth::User()->id)
                ->where('answer', 5)
                ->orderBy('calls.updated_at')
                ->get();
            $blacklist = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
                ->join('users', 'users.id', '=', 'contacts.user_id')
                ->where('users.user_id', Auth::User()->id)
                ->whereIn('answer', [2, 3, 6])
                ->orderBy('calls.updated_at')
                ->get();
        }

//  Each caller by total dials per week.
//  View blacklisted list.
//  View list of each call by candidates.

        return view('reporting/index', compact('successful', 'unsuccessful', 'asked_removed', 'wrong_numbers', 'unanswered', 'gatekeeper', 'whitelist', 'blacklist'));
    }

    /**
     * Show the application dashboard.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function blacklist(Request $request)
    {
        $type = [
            'candidate' => [5],
            'company' => [2, 3, 7]
        ];
        $answer_is = Call::QUESTION_SHORT;
        $calls = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
            ->where(function ($query) use ($type) {
                $query->where('contacts.type', 'candidate')
                    ->whereIn('calls.answer', $type['candidate']);
            })
            ->orWhere(function ($query) use ($type) {
                $query->where('contacts.type', 'company')
                    ->whereIn('calls.answer', $type['company']);
            })
            ->orderBy('calls.updated_at');

        if (Auth::User()->role == User::SUBADMIN) {
            $calls = $calls->join('users', 'users.id', '=', 'calls.user_id')
                ->where('users.user_id', Auth::User()->id);
        }
        $blacklist = $calls->get();

        return view('reporting/blacklist', compact('answer_is', 'blacklist'));
    }

    /**
     * Show the application dashboard.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function callsAll(Request $request)
    {
        $calls_raw = [];
        if (Auth::User()->role == User::ADMIN) {
            $calls_raw = Call::get();
        } elseif (Auth::User()->role == User::SUBADMIN) {
            $calls_raw = Call::join('users', 'calls.user_id', '=', 'users.id')
                ->select('calls.*')
                ->where('users.user_id', Auth::User()->id)
                ->get();
        }

        $answer_is = Call::QUESTION_SHORT;
        $calls = [];
        foreach ($calls_raw as $call) {
            $contact = $call->contact()->first();

            $calls[] = [
                $call->user->username,
                $contact->first_name . ' ' . $contact->last_name,
                ($contact->type == Contact::COMPANY) ? $contact->company_name : '',
                $contact->phone,
                $contact->email,
                $call->updated_at->toDateTimeString(),
                $call->note,
                $answer_is[$contact->type][$call->answer],
                '<a class="btn btn-primary" href="' . route('reporting.calls.edit', ['call' => $call->id]) . '">Edit</a>'
            ];
        }

        //highlight_string("<?php\n".var_export($calls,true));die();

        return view('reporting.all_calls', compact('calls'));
    }

    /**
     * Show the application dashboard.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function calls(Request $request)
    {
        if (Auth::User()->role == User::ADMIN) {
            $callers = User::whereIn("role", [User::COMPANY, User::CANDIDATE])->pluck('username', 'id');
        } elseif (Auth::User()->role == User::SUBADMIN) {
            $callers = User::whereIn("role", [User::COMPANY, User::CANDIDATE])
                ->where('user_id', Auth::User()->id)
                ->pluck('username', 'id');
        }

        return view('reporting.calls', compact('callers'));
    }


    /**
     * Show the application dashboard.
     *
     * @param Request $request
     *
     * @return array
     */
    public function callsData(Request $request)
    {
        $user_id = $request->query('user');
        $calls_raw = Call::where('user_id', $user_id)
            ->get();
        $answer_is = Call::QUESTION_SHORT;

        $calls = [];
        foreach ($calls_raw as $call) {
            $contact = $call->contact()->first();
            $calls[] = [
                $call->user->username,
                $contact->first_name . ' ' . $contact->last_name,
                ($contact->type == Contact::COMPANY) ? $contact->company_name : '',
                $contact->email,
                $contact->phone,
                $call->updated_at->toDateTimeString(),
                $call->note,
                $answer_is[$contact->type][$call->answer],
                '<a class="btn btn-primary" href="' . route('reporting.calls.edit', ['call' => $call->id]) . '">Edit</a>'
            ];
        }

        return $calls;
    }

    /**
     * Show the application dashboard.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function edit_call(Request $request)
    {
        $call = Call::find($request->query('call'));
        $contact = Contact::find($call->contact_id);
        $type = $contact->type;
        if ($request->isMethod('post')) {
            $call->answer = $request->input('status');
            $call->save();

            $app_recycled = DB::table('settings')->where('key', 'app_recycled')->first()->value * 24 * 3600;
            ScheduleCall::join('calls', 'schedule_calls.call_id', '=', 'calls.id')
                ->where('calls.contact_id', $contact->id)
                ->where('calls.user_id', $contact->user_id)
                ->delete();

            if (in_array($request->input('status'), [Call::ANSWER_PROGRESS, Call::ANSWER_NOT_ANSWERED, Call::ANSWER_LEFT_MESSAGE])) {
                if ($request->input('status') == Call::ANSWER_PROGRESS) {
                    $app_recycled = $request->input('hour') * 3600 + $request->input('minute') * 60 + $request->input('second');
                }

                $schedule = new ScheduleCall();
                $schedule->remind_me_at = $app_recycled;
                $schedule->call()->associate($call);
                $schedule->save();

                $queues = DB::table('queues')
                    ->where('contact_id', $contact->id)
                    ->get();

                if ($queues->count() == 0) {
                    DB::table('queues')->insert([
                        'contact_id' => $contact->id,
                        'type' => $type,
                        'enabled' => false,
                    ]);
                }
            } else {
                DB::table('queues')
                    ->where('contact_id', $contact->id)
                    ->delete();
            }

            return redirect()->route("reporting.calls")->with('alert', [
                'class' => 'success',
                'message' => 'Change saved successfully'
            ]);
        } else {
            $status = Call::QUESTION_SHORT[$type];
        }

        return view("call.$type.edit", compact('contact', 'call', 'status'));
    }

    /**
     * Show the application dashboard.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function statistics(Request $request)
    {
        if (Auth::User()->role == User::ADMIN) {
            $callers = User::whereIn("role", [User::COMPANY, User::CANDIDATE])->pluck('username', 'id');
        } elseif (Auth::User()->role == User::SUBADMIN) {
            $callers = User::whereIn("role", [User::COMPANY, User::CANDIDATE])
                ->where('user_id', Auth::User()->id)
                ->pluck('username', 'id');
        }

        return view('reporting.statistics', compact('callers'));
    }


    /**
     * Show the application dashboard.
     *
     * @param Request $request
     *
     * @return array
     */
    public function statisticsData(Request $request)
    {
        $user_id = $request->query('caller');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $user = User::find($user_id);
        $answer_is = Call::QUESTION_SHORT;
        $colors = [
            '#f56954',
            '#dbc3c8',
            '#FF0000',
            '#f39c12',
            '#f56495',
            '#00c0ef',
            '#3c8dbc',
            '#c12dbc',
        ];

        $calls = [];

        foreach ($answer_is[$user->role] as $key => $value) {
            if (!(Contact::COMPANY == $user->role && Call::ANSWER_PROGRESS == $key)) {
                $count = Call::where('answer', $key)
                        ->where('user_id', $user_id)
                        ->where('calls.created_at', '>', $startDate)
                        ->where('calls.created_at', '<', $endDate)
                        ->get()->count();
                if ($count > 0) {
                    $calls[] = [
                        'value' => $count,
                        'color' => $colors[$key],
                        'highlight' => $colors[$key],
                        'label' => $value
                    ];
                }
            }
        }

        if (Contact::COMPANY == $user->role) {
            $count = ScheduleCall::join('calls', 'schedule_calls.call_id', '=', 'calls.id')
                ->where('user_id', $user->id)
                ->where('calls.created_at', '>', $startDate)
                ->where('calls.created_at', '<', $endDate)                    
                ->get()
                ->count();
            $calls[] = [
                'value' => $count,
                'color' => $colors[Call::ANSWER_PROGRESS],
                'highlight' => $colors[Call::ANSWER_PROGRESS],
                'label' => $answer_is[$user->role][Call::ANSWER_PROGRESS]
            ];
        }

        if (empty($calls)) {
            $calls[] = [
                'value' => 1,
                'color' => '#d2d6de',
                'highlight' => '#d2d6de',
                'label' => 'Empty'
            ];
        }
        $staticdata = $this->getStatistic('candidate',$user->id,$startDate,$endDate);
        return response()->json([
            'calls' => $calls,
            'name' => $user->first_name.' '.$user->last_name,
            'email' => $user->email,
            'total_calls'=> $staticdata['total'],
            'successful'=> $staticdata['successful'],
            'followup'=> $staticdata['in_progress'],
            'left_message'=> 0,
            'unsuccessful'=> 0,
            'gatekeeper'=> 0
        ]);
    }
    
    public function getStatistic($type = 'candidate',$userid, $startDate, $endDate)
    {
        $successful = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
            ->where('contacts.type', $type)
            ->where('calls.answer', Call::ANSWER_SUCCESSFULLY)
            ->where('calls.created_at', '>', $startDate)
            ->where('calls.created_at', '<', $endDate)
            ->orderBy('calls.updated_at');
        $in_progress = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
            ->where('contacts.type', $type)
            ->where('calls.answer', Call::ANSWER_PROGRESS)
            ->where('calls.created_at', '>', $startDate)
            ->where('calls.created_at', '<', $endDate)
            ->orderBy('calls.updated_at');
        $answered = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
            ->where('contacts.type', $type)
            ->whereIn('calls.answer', [Call::ANSWER_SUCCESSFULLY, Call::ANSWER_PROGRESS, Call::ANSWER_UNSUCCESSFUL, Call::ANSWER_ASKED_REMOVED, Call::ANSWER_WRONG_NUMBER])
            ->where('calls.created_at', '>', $startDate)
            ->where('calls.created_at', '<', $endDate)
            ->orderBy('calls.updated_at');
        $wrong_numbers = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
            ->where('contacts.type', $type)
            ->where('calls.answer', Call::ANSWER_WRONG_NUMBER)
            ->where('calls.created_at', '>', $startDate)
            ->where('calls.created_at', '<', $endDate)
            ->orderBy('calls.updated_at');
        $blacklist = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
            ->where('contacts.type', $type)
            ->whereIn('calls.answer', [Call::ANSWER_UNSUCCESSFUL, Call::ANSWER_ASKED_REMOVED, Call::ANSWER_WRONG_NUMBER])
            ->where('calls.created_at', '>', $startDate)
            ->where('calls.created_at', '<', $endDate)
            ->orderBy('calls.updated_at');
        $total = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
            ->where('contacts.type', $type)
            ->where('calls.created_at', '>', $startDate)
            ->where('calls.created_at', '<', $endDate)
            ->orderBy('calls.updated_at');

        $data = [
            'successful' => $successful->where('calls.user_id', $userid)
                ->get()
                ->count(),
            'in_progress' => $in_progress->where('calls.user_id', $userid)
                ->get()
                ->count(),
            'answered' => $answered->where('calls.user_id', $userid)
                ->get()
                ->count(),
            'wrong_numbers' => $wrong_numbers->where('calls.user_id', $userid)
                ->get()
                ->count(),
            'blacklist' => $blacklist->where('calls.user_id', $userid)
                ->get()
                ->count(),
            'total' => $total->where('calls.user_id', $userid)
                ->get()
                ->count()
        ];

        return $data;
    }    
}