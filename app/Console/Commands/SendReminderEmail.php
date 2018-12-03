<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use DB;
use App\Call;
use App\Setting;
use App\EmailTemplate;
use Illuminate\Support\Facades\Mail;

class SendReminderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emailreminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders using Email';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {        
        // reminder
        $schedules = DB::table('schedule_calls')->get();
        if (count($schedules) > 0) {
            foreach ($schedules as $schedule) {
                $call = DB::table('calls')->find($schedule->call_id);                
                $contact = DB::table('contacts')->find($call->contact_id);
                if ($call->answer == Call::ANSWER_PROGRESS && !empty($schedule->timezone)) { // when its followup
                    // Here identify whether 2 hours before or 15mins before                    
                    $nowinTz = Carbon::now($schedule->timezone);
                    $remindmeat =Carbon::parse($schedule->remind_me_at);
                    echo $schedule->id.'>>'.$remindmeat->diffInMinutes($nowinTz).'::';
                    if($remindmeat->diffInMinutes($nowinTz) == 5){ // if difference is 5                        
                        $user = DB::table('users')->find($call->user_id);
                        $emailtemplate = EmailTemplate::where('handle', 'sendemailbutton-'.$user->role)->first();
                        $mail = \EmailTemplate::fetch('followupreminder-'.$user->role, ['Contactname'=>$contact->first_name . ' ' . $contact->last_name,
                            'Callername' => $user->first_name.' '.$user->last_name]);

                        $smtp = ($emailtemplate->smtp =='reqruited')?'reqruited_':'';
                        $host = Setting::where('key', $smtp.'mail_host')->firstOrFail()->value;
                        $port = Setting::where('key', $smtp.'mail_port')->firstOrFail()->value;
                        $encryption = Setting::where('key', $smtp.'mail_encryption')->firstOrFail()->value;
                        $username = Setting::where('key', $smtp.'mail_username')->firstOrFail()->value;
                        $password = Setting::where('key', $smtp.'mail_password')->firstOrFail()->value;
                        $app_email = Setting::where('key', 'app_email')->firstOrFail()->value;

                        if (empty($host) || empty($port) || empty($username) || empty($password)) {
                            echo " | error on email setting |\n";                        
                        }else{
                            $transport = new \Swift_SmtpTransport($host, (int)$port);
                            $transport->setEncryption($encryption);
                            $transport->setUsername($username);
                            $transport->setPassword($password);

                            $mailer = new \Swift_Mailer($transport);
                            // Assign it to the Laravel Mailer
                            Mail::setSwiftMailer($mailer);

                            try {
                                $response = \Mail::to($user->email)->send($mail);
                                if(count(\Mail::failures()) == 0){
                                    echo "\n | followup email sent to $user->first_name $user->last_name | " . Carbon::now($schedule->timezone) . " |\n";
                                }
                            } catch (\Exception $e) {
                                $response = $e->getMessage();
                                echo "\n | followup email not sent to $user->first_name $user->last_name ** $response | " . Carbon::now($schedule->timezone) . " |\n";
                            }
                        }
                    }
                }
            }
        }else {
            echo "\n | no followup call in the queue |" . Carbon::now() . " |\n";
        }
    }
}
