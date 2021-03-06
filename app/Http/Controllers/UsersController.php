<?php

namespace App\Http\Controllers;

use App\User;
use App\Contact;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\EmailTemplate;
use App\Mail\WelcomeCandidateMail;
use App\Mail\WelcomeCompanyMail;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Auth::User()->role == User::ADMIN) {
            $users = User::where('enabled', true)
                ->get();
        } elseif (Auth::User()->role == User::SUBADMIN) {
            $users = User::where('enabled', true)
                ->where('user_id', Auth::User()->id)
                ->get();
        }

        // load the view and pass the users
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = User::ROLES;
        $leaders = User::where('enabled', true)->where('role', USER::SUBADMIN)->get()->pluck('full_name', 'id');
        return view('users.create', ['roles'=>$roles,'leaders'=>$leaders]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];

        $data = $request->all();

        if (!empty($data['affiliate'])) {
            $rules['affiliate'] = 'required|string|max:255|unique:users';
        }
        request()->validate($rules);

        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        
        if(isset($data['user_id'])){
            $user->user_id = $data['user_id'];
        }else{
            $user->user_id = Auth::User()->id;
        }
        $user->save();
        // leader data
        $leader = User::find($user->user_id);
        $emailtemplate = EmailTemplate::where('handle', 'Onboarding-'.$user->role)->first();
        $mail = \EmailTemplate::fetch('Onboarding-'.$user->role, ['Leadername'=>$leader->first_name.' '.$leader->last_name,
            'Callername' => $user->first_name.' '.$user->last_name,'Emailaddress'=>$user->email,'Password'=>$request->password]); 
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
            if($user->role != 'subadmin' || $user->role != 'admin')
                $response = \Mail::to($user->email)->cc([$leader->email])->send($mail);
            else
                $response = \Mail::to($user->email)->send($mail);
            if(count(\Mail::failures()) == 0){
                return redirect()->route('users.index')->with('alert', [
                    'class' => 'success',
                    'message' => 'User created successfully'
                ]);
            }
        } catch (\Exception $e) {
            $response = $e->getMessage();
        }        
        return redirect()->route('users.index')->with('alert', [
            'class' => 'error',
            'message' => $response
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $roles = User::ROLES;        
        $leaders = User::where('enabled', true)->where('role', USER::SUBADMIN)->get()->pluck('full_name', 'id');;
        return view('users.edit', compact('user', 'roles', 'leaders'));
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
        if (empty($request->password)) {
            request()->validate([
                'username' => 'required',
                'email' => 'email|required',
            ]);

            $user = User::find($id);
            $request->merge(['password' => $user->password]);
            $request->merge(['password_confirmation' => $user->password]);
        } else {
            request()->validate([
                'username' => 'required',
                'email' => 'email|required',
                'password' => 'confirmed',
            ]);

            $request->merge(['password' => bcrypt($request->all()['password'])]);
        }

        User::find($id)->update($request->all());

        return redirect()->route('users.index')->with('alert', ['class' => 'success', 'message' => 'User updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user_destroy = User::find($id);

        $users = User::find($id)->users()->get();

        foreach ($users as $user) {
            $user->user_id = 1;
            $user->save();
        }

        if (0 < $user_destroy->calls->count()) {
            return redirect()->route('users.index')->with('alert', [
                'class' => 'danger',
                'message' => 'User didn\'t deleted, Has calls'
            ]);
        } else {
            $contacts = Contact::where('user_id', $user_destroy->id)
                ->orWhere('user_id', 1)
                ->get();

            foreach ($contacts as $contact) {
                DB::table('queues')
                    ->where('contact_id', $contact->id)
                    ->where('user_id', $user_destroy->id)
                    ->update([
                        'enabled' => true,
                        'user_id' => null
                    ]);
            }

            $user_destroy->delete();

            return redirect()->route('users.index')->with('alert', [
                'class' => 'success',
                'message' => 'User deleted successfully'
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function activate(Request $request)
    {
        $users = $request->get('users');
        $users = is_array($users) ? $users : [$users];

        for ($i = 0; $i < count($users); $i++) {
            $user = User::find($users[$i]);
            $user->enabled = 1;
            $user->save();
        }

        return redirect()->route('users.index')->with('alert', ['class' => 'success', 'message' => 'User activated successfully']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function deactivate_index(Request $request)
    {
        if (Auth::User()->role == User::ADMIN) {
            $users = User::where('enabled', 0)
                ->get();
        } elseif (Auth::User()->role == User::SUBADMIN) {
            $users = User::where('enabled', 0)
                ->where('user_id', Auth::User()->id)
                ->get();
        }

        // load the view and pass the users
        return view('users.deactivate_index', compact('users'));
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function deactivate(Request $request)
    {
        $users = $request->get('users');
        $users = is_array($users) ? $users : [$users];

        for ($i = 0; $i < count($users); $i++) {
            $user = User::find($users[$i]);
            $user->user_id = 1;
            $user->enabled = false;
            $user->save();
            if ($user->role == User::SUBADMIN) {
                $members = $user->users();

                foreach ($members as $member) {
                    $member->user_id = 1;
                    $member->save();
                }
            }

            DB::table('queues')
                ->where('user_id', $user->id)
                ->update([
                    'enabled' => true,
                    'user_id' => null
                ]);
        }

        return redirect()->route('users.index')->with('alert', [
            'class' => 'success',
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function leaders(Request $request)
    {
        $users = User::where('enabled', 1)->get();
        $leaders = $users->where('role', USER::SUBADMIN);
        $members = $users->whereIn('role', [USER::COMPANY, USER::CANDIDATE]);

        // load the view and pass the users
        return view('users.leaders', compact('leaders', 'members'));
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return array
     */
    public function leadersData(Request $request)
    {
        $user_id = $request->query('user');
        $users_raw = User::find($user_id)->users()->get();

        $users = [];
        foreach ($users_raw as $user) {
            $users[] = [
                $user->first_name . ' ' . $user->last_name,
                $user->username,
                $user->email,
                '<a class="btn btn-danger" href="' . route('users.leaders.memberRevert', ['user' => $user->id]) . '">Remove</a>'
            ];
        }

        return $users;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function leadersSave(Request $request)
    {
        $members = $request->get('members');
        $leader = User::find($request->get('leader'));

        for ($i = 0; $i < count($members); $i++) {
            $user = User::find($members[$i]);
            $user->user_id = $leader->id;
            $user->save();
        }

        // load the view and pass the users
        return 1;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function memberRevert(Request $request)
    {
        $member = User::find($request->get('user'));
        $member->user_id = 1;
        $member->save();

        return redirect()->route('users.leaders')->with('alert', ['class' => 'success', 'message' => 'User removed successfully']);
    }
    
    public function dealflow(Request $request){
        
        return view('users.dealflow', compact('users'));
    }
    
    public function todaystatus(Request $request){
        return view('users.todaystatus', compact('users'));
    }
}