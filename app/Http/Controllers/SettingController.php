<?php

namespace App\Http\Controllers;

use App\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
	public function closerindex(Request $request){
		return view('setting/closerindex');
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
                if (("mail_" === substr($key,0,5)) || substr($key,0,4) === "app_" || substr($key,0,7) === "payout_") {
                    $setting = Setting::where('key',$key)->firstOrFail();
                    $setting->value = $value;
                    $setting->update();
                }
            }
        }

        $settings = Setting::where('key', 'like', 'app_%')
            ->orWhere('key', 'like', 'mail_%')
            ->orWhere('key', 'like', 'payout_%')
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
}
