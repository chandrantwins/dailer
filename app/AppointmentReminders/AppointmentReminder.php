<?php

namespace App\AppointmentReminders;

use Carbon\Carbon;
use App\EmailTemplate;
use App\Setting;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;
use DB;

class AppointmentReminder
{
    /**
     * Construct a new AppointmentReminder
     *
     * @param Illuminate\Support\Collection $twilioClient The client to use to query the API
     */
    function __construct()
    {
        $this->appointments = DB::table('appointments')->get();
        $twilioConfig = config('services.twilio');
        $accountSid = $twilioConfig['twilio_account_sid'];
        $authToken = $twilioConfig['twilio_auth_token'];
        $this->sendingNumber = $twilioConfig['twilio_number'];

        $this->twilioClient = new Client($accountSid, $authToken);
    }

    /**
     * Send reminders for each appointment
     *
     * @return void
     */
    public function sendReminders()
    {
        if (count($this->appointments) > 0) {
            foreach($this->appointments as $appointment){
                $this->_remindAbout($appointment);
            }
        }
    }

    /**
     * Sends a message for an appointment
     *
     * @param Appointment $appointment The appointment to remind
     *
     * @return void
     */
    private function _remindAbout($appointment)
    {        
        $time = Carbon::parse($appointment->when, 'UTC')
              ->subMinutes($appointment->timezoneOffset)
              ->format('g:i a');
        $user = DB::table('users')->find($appointment->created_by);
        $contact = DB::table('contacts')->find($appointment->contact_id);
        echo "\n | Inside reminder About |"; 
        if (!empty($appointment->timezone)) {
            // Here identify whether 2 hours before or 15mins before                    
            $nowinTz = Carbon::now($appointment->timezone);
            $remindmeat =Carbon::parse($appointment->originalnotificationTime,$appointment->timezone);
            echo $nowinTz.'--'.$appointment->id.'>>'.$remindmeat->diffInMinutes($nowinTz,false).'::'.$remindmeat;
            $minutes = $remindmeat->diffInMinutes($nowinTz,false);
            if($minutes == -120 || $minutes == -15){ // before 2 hours and 15mins                
                $time = ($minutes == -15)?'15 Minutes':'2 Hours';
                $useremailmessage = \EmailTemplate::fetch('appointmentuseremail-'.$contact->type, ['Contactname'=>$contact->first_name . ' ' . $contact->last_name,
                    'Callername' => $user->first_name.' '.$user->last_name,'Callerphone'=>$user->phone, 'time'=>$time,'Contactphone'=>$appointment->phoneNumber]); 

                $usersmsmessage = \EmailTemplate::fetch('appointmentusersms-'.$contact->type, ['Contactname'=>$contact->first_name . ' ' . $contact->last_name,
                    'Callername' => $user->first_name.' '.$user->last_name,'Callerphone'=>$user->phone, 'time'=>$time,'Contactphone'=>$appointment->phoneNumber]); 

                $contactemailmessage = \EmailTemplate::fetch('appointmentcontactemail-'.$contact->type, ['Contactname'=>$contact->first_name . ' ' . $contact->last_name,
                    'Callername' => $user->first_name.' '.$user->last_name,'Callerphone'=>$user->phone, 'time'=>$time,'Contactphone'=>$appointment->phoneNumber]); 

                $contactsmsmessage = \EmailTemplate::fetch('appointmentcontactsms-'.$contact->type, ['Contactname'=>$contact->first_name . ' ' . $contact->last_name,
                    'Callername' => $user->first_name.' '.$user->last_name,'Callerphone'=>$user->phone, 'time'=>$time,'Contactphone'=>$appointment->phoneNumber]); 

                $emailtemplate = EmailTemplate::where('handle', 'sendemailbutton-'.$user->role)->first();
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
                        $response = \Mail::to($user->email)->send($useremailmessage);
                        $response = \Mail::to($contact->email)->send($contactemailmessage);
                        if(count(\Mail::failures()) == 0){
                            echo "\n | Appointment email sent to $user->first_name $user->last_name | " . Carbon::now($appointment->timezone) . " |\n";
                        }
                    } catch (\Exception $e) {
                        $response = $e->getMessage();
                        echo "\n | Appointment email not sent to $user->first_name $user->last_name ** $response | " . Carbon::now($appointment->timezone) . " |\n";
                    }
                }
                //$message = "Hello $recipientName, this is a reminder that you have an appointment at $time!";
                $this->_sendMessage($appointment->phoneNumber, $contactsmsmessage->render());
                if(!is_null($user->phone))
                    $this->_sendMessage($user->phone, $usersmsmessage->render());
            }        
        }        
    }

    /**
     * Sends a single message using the app's global configuration
     *
     * @param string $number  The number to message
     * @param string $content The content of the message
     *
     * @return void
     */
    private function _sendMessage($number, $content)
    {
        $this->twilioClient->messages->create(
            $number,
            array(
                "from" => $this->sendingNumber,
                "body" => $this->replacetag(html_entity_decode($content))
            )
        );
    }
    
    private function replacetag($string){
        $tags = array("<p>", "</p>");        
        return str_replace($tags, "", $string);    
    }
    
    private function compileEchos($value){
        $value = preg_replace ('/\{\{\{\s*(.+?)\s*\}\}\}/s', '<?php echo $1; ?>', $value);
        return preg_replace ('/\{\{\s*(.+?)\s*\}\}/s', "<?php echo htmlentities($1, ENT_QUOTES, 'UTF-8', false); ?>", $value);
    }
}