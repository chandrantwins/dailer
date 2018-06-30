<?php

namespace App\Http\Controllers;

use App\Call;
use App\Setting;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $data = [
            'candidate' => $this->getStatistic('candidate', Carbon::now()->subWeek(), Carbon::now()),
            'company' => $this->getStatistic('company', Carbon::now()->subWeek(), Carbon::now())
        ];

        if ($user->role == User::ADMIN) {
            $data['successful'] = Call::where('calls.answer', Call::ANSWER_SUCCESSFULLY)
                ->orderBy('calls.updated_at')
                ->get();
            $data['blacklist'] = Call::whereIn('calls.answer', [Call::ANSWER_UNSUCCESSFUL, Call::ANSWER_ASKED_REMOVED, Call::ANSWER_WRONG_NUMBER])
                ->orderBy('calls.updated_at')
                ->get();
        } elseif ($user->role == User::SUBADMIN) {
            $data['successful'] = Call::where('calls.answer', Call::ANSWER_SUCCESSFULLY)
                ->join('contacts', 'calls.contact_id', '=', 'contacts.id')
                ->join('users', 'users.id', '=', 'contacts.user_id')
                ->where('users.user_id', $user->id)
                ->orderBy('calls.updated_at')
                ->get();
            $data['blacklist'] = Call::whereIn('calls.answer', [Call::ANSWER_UNSUCCESSFUL, Call::ANSWER_ASKED_REMOVED, Call::ANSWER_WRONG_NUMBER])
                ->join('contacts', 'calls.contact_id', '=', 'contacts.id')
                ->join('users', 'users.id', '=', 'contacts.user_id')
                ->where('users.user_id', $user->id)
                ->orderBy('calls.updated_at')
                ->get();
        } else {
            $data['payout_' . $user->role] = Setting::where('key', 'payout_' . $user->role)->firstOrFail()->value;
            $data['successful'] = Call::where('calls.answer', Call::ANSWER_SUCCESSFULLY)
                ->where('calls.user_id', $user->id)
                ->orderBy('updated_at')
                ->get();
            $data['blacklist'] = Call::whereIn('calls.answer', [Call::ANSWER_UNSUCCESSFUL, Call::ANSWER_ASKED_REMOVED, Call::ANSWER_WRONG_NUMBER])
                ->where('calls.user_id', $user->id)
                ->orderBy('calls.updated_at')
                ->get();
        }

        return view('dashboard', compact('data'));
    }

    public function getStatistic($type = 'candidate', $startDate, $endDate)
    {
        $user = Auth::user();
        $successful = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
            ->where('contacts.type', $type)
            ->where('calls.answer', Call::ANSWER_SUCCESSFULLY)
            ->where('calls.created_at', '>', $startDate)
            ->where('calls.created_at', '<', $endDate)
            ->orderBy('calls.updated_at');
        $in_progress = Call::join('contacts', 'calls.contact_id', '=', 'contacts.id')
            //->join('schedule_calls', 'schedule_calls.call_id', '=', 'calls.id')
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

        if ($user->role == User::ADMIN) {
            $data = [
                'successful' => $successful->get()->count(),
                'in_progress' => $in_progress->get()->count(),
                'answered' => $answered->get()->count(),
                'wrong_numbers' => $wrong_numbers->get()->count(),
                'blacklist' => $blacklist->get()->count(),
                'total' => $total->get()->count(),
            ];
        } elseif ($user->role == User::SUBADMIN) {
            $data = [
                'successful' => $successful->join('users', 'users.id', '=', 'calls.user_id')
                    ->where('users.user_id', $user->id)
                    ->get()
                    ->count(),
                'in_progress' => $in_progress->join('users', 'users.id', '=', 'calls.user_id')
                    ->where('users.user_id', $user->id)
                    ->get()
                    ->count(),
                'answered' => $answered->join('users', 'users.id', '=', 'calls.user_id')
                    ->where('users.user_id', $user->id)
                    ->get()
                    ->count(),
                'wrong_numbers' => $wrong_numbers->join('users', 'users.id', '=', 'calls.user_id')
                    ->where('users.user_id', $user->id)
                    ->get()
                    ->count(),
                'blacklist' => $blacklist->join('users', 'users.id', '=', 'calls.user_id')
                    ->where('users.user_id', $user->id)
                    ->get()
                    ->count(),
                'total' => $total->join('users', 'users.id', '=', 'calls.user_id')
                    ->where('users.user_id', $user->id)
                    ->get()
                    ->count()
            ];
        } else {
            $data = [
                'successful' => $successful->where('calls.user_id', $user->id)
                    ->get()
                    ->count(),
                'in_progress' => $in_progress->where('calls.user_id', $user->id)
                    ->get()
                    ->count(),
                'answered' => $answered->where('calls.user_id', $user->id)
                    ->get()
                    ->count(),
                'wrong_numbers' => $wrong_numbers->where('calls.user_id', $user->id)
                    ->get()
                    ->count(),
                'blacklist' => $blacklist->where('calls.user_id', $user->id)
                    ->get()
                    ->count(),
                'total' => $total->where('calls.user_id', $user->id)
                    ->get()
                    ->count()
            ];
        }

        return $data;
    }

    /**
     * Show the application dashboard.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard(Request $request)
    {
        $type = $request->query('type');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        if ('both' == $type) {
            $data1 = $this->getStatistic('candidate', $startDate, $endDate);
            $data2 = $this->getStatistic('company', $startDate, $endDate);
            $data = [
                'successful' => $data1['successful'] + $data2['successful'],
                'in_progress' => $data1['in_progress'] + $data2['in_progress'],
                'answered' => $data1['answered'] + $data2['answered'],
                'wrong_numbers' => $data1['wrong_numbers'] + $data2['wrong_numbers'],
                'blacklist' => $data1['blacklist'] + $data2['blacklist'],
                'total' => $data1['total'] + $data2['total'],
            ];
        } else {
            $data = $this->getStatistic($type, $startDate, $endDate);
        }

        return $data;
    }
}