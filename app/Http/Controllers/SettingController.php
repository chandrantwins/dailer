<?php

namespace App\Http\Controllers;

use App\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function closerlist(Request $request){
        $timezones = Setting::TIMEZONES;
        $closerid = Auth::User()->id;
        if ($request->isMethod('post')) {
            $workinghours = $request->get('workhours_' . $closerid);
            $selectedTimezone = $this->getTimezoneNameByIndex($request->get('timezone_' . $closerid));
            //print_r($workinghours);
            foreach ($workinghours as $k=>$hours){
                if($k < 5){ // Monday to friday only
                    if($hours['isActive'] == 'true'){
                        // convert time SelectedTimezone to UTC
                        $todayFromdate = date('Y-m-d').' '.$hours['timeFrom'].':00';
                        $workinghours[$k]['timeFrom'] = $this->getConvertedDateTime($todayFromdate, $selectedTimezone,'UTC', 'H:i');
                        $todayTodate = date('Y-m-d').' '.$hours['timeTill'].':00';
                        $workinghours[$k]['timeTill'] = $this->getConvertedDateTime($todayTodate, $selectedTimezone,'UTC', 'H:i');
                    }
                }
            }
            
            $timezone = Setting::updateOrCreate(['key' => 'timezone_'.$closerid,'name'=>'Timezone '.$closerid], ['value' => $request->get('timezone_' . $closerid)]);
            $workhours = Setting::updateOrCreate(['key' => 'workhours_'.$closerid,'name'=>'Workhours '.$closerid], ['value' => json_encode($workinghours)]);            
        }else{
            $timezone = Setting::select('value')->where('key','timezone_' . $closerid)->get();
            if(isset($timezone[0])){
                $timezone = $timezone[0]->value;
                $selectedTimezone = $this->getTimezoneNameByIndex($timezone);
            }
            $workhours = Setting::select('value')->where('key', 'workhours_' . $closerid)->get();            
            if(isset($workhours[0])){
                $workinghours = json_decode($workhours[0]->value);                
                foreach ($workinghours as $k=>$hours){
                    if($k < 5){ // Monday to friday only                                            
                        if($hours->isActive == 'true'){
                            // convert time SelectedTimezone to UTC
                            $todayFromdate = date('Y-m-d').' '.$hours->timeFrom.':00';
                            $workinghours[$k]->timeFrom = $this->getConvertedDateTime($todayFromdate, 'UTC',$selectedTimezone, 'H:i');
                            $todayTodate = date('Y-m-d').' '.$hours->timeTill.':00';
                            $workinghours[$k]->timeTill = $this->getConvertedDateTime($todayTodate, 'UTC',$selectedTimezone, 'H:i');
                        }
                    }
                }
                $trueval = str_replace('"true"', 'true',json_encode($workinghours));
                $workhours = str_replace('"false"', 'false',$trueval);
            }
        }
        return view('setting/closerindex',['timezones'=>$timezones,'closerid'=>$closerid,'timezone'=>$timezone,'workhours'=>$workhours]);
    }
	
    /**
     * Show the application dashboard.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            foreach ($request->all() as $key=>$value) {
                if (("mail_" === substr($key,0,5)) || substr($key,0,4) === "app_" || substr($key,0,7) === "payout_" || substr($key,0,9) === "reqruited") {
                    $setting = Setting::where('key',$key)->firstOrFail();
                    $setting->value = $value;
                    $setting->update();
                }
            }
        }

        $settings = Setting::where('key', 'like', 'app_%')
            ->orWhere('key', 'like', 'mail_%')
            ->orWhere('key', 'like', 'payout_%')
            ->orWhere('key', 'like', 'reqruited_%')
            ->get();
        $encryptions = Setting::ENCRYPTION;

        return view('setting/index',compact('settings','encryptions'));
    }

    /**
     * Test SMTP configuration.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function testSMTP(Request $request)
    {
        $host = Setting::where('key', 'mail_host')->firstOrFail()->value;
        $port = Setting::where('key', 'mail_port')->firstOrFail()->value;
        $encryption = Setting::where('key', 'mail_encryption')->firstOrFail()->value;
        $username = Setting::where('key', 'mail_username')->firstOrFail()->value;
        $password = Setting::where('key', 'mail_password')->firstOrFail()->value;

        if (empty($host) || empty($port) || empty($username) || empty($password)) {
            return redirect()->route("setting.index")->with('alert', ['class' => 'danger', 'message' => 'Enter SMTP config!!']);
        }

        try{
            $transport = new \Swift_SmtpTransport("$host",(int)$port,"$encryption");
            $transport->setUsername("$username");
            $transport->setPassword("$password");
            $transport->setAuthMode('login');
            $mailer = new \Swift_Mailer($transport);
            $mailer->getTransport()->start();

            return redirect()->route("setting.index")->with('alert', ['class' => 'success', 'message' => 'SMTP Connected!']);
        } catch (\Exception $e) {

            return redirect()->route("setting.index")->with('alert', ['class' => 'danger', 'message' => $e->getMessage()]);
        }
    }
    
    private function getConvertedDateTime($datetime,$fromtimezone,$totimezone, $format){        
        $given = new \DateTime($datetime, new \DateTimeZone($fromtimezone));
        $given->setTimezone(new \DateTimeZone($totimezone));
        $output = $given->format($format); 
        return $output;
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
}