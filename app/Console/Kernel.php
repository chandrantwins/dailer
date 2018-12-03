<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Call;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        '\App\Console\Commands\SendReminders',
        '\App\Console\Commands\SendReminderEmail',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
		$schedule->command('reminders:send')->everyMinute();
                $schedule->command('emailreminders:send')->everyMinute();
        $schedule->call(function () {
            echo "*** Start checking calls in progress | " . Carbon::now() . " |\n";
            // reminder
            $schedules = DB::table('schedule_calls')->get();

            if (count($schedules) > 0) {
                $app_recycled = DB::table('settings')->where('key', 'app_recycled')->first()->value;
                $host = DB::table('settings')->where('key', 'mail_host')->first()->value;
                $port = DB::table('settings')->where('key', 'mail_port')->first()->value;
                $encryption = DB::table('settings')->where('key', 'mail_encryption')->first()->value;
                $username = DB::table('settings')->where('key', 'mail_username')->first()->value;
                $password = DB::table('settings')->where('key', 'mail_password')->first()->value;
                $app_email = DB::table('settings')->where('key', 'app_email')->first()->value;

                $transport = new \Swift_SmtpTransport($host, $port);
                $transport->setEncryption($encryption);
                $transport->setUsername($username);
                $transport->setPassword($password);


                if (empty($app_recycled) || empty($host) || empty($port) || empty($username) || empty($password)) {
                    echo " | error on email setting |\n";
                } else {
                    foreach ($schedules as $schedule) {
                        $date_call = (new Carbon($schedule->updated_at))->addSeconds($schedule->remind_me_at);
                        $call = DB::table('calls')->find($schedule->call_id);
                        $contact = DB::table('contacts')->find($call->contact_id);
                        $user = DB::table('users')->find($call->user_id);

                        $mailer = new \Swift_Mailer($transport);
                        $message = new \Swift_Message();
                        $message->setFrom($app_email);

                        if ($call->answer == Call::ANSWER_PROGRESS) {
                            // before 10 min
                            if ($date_call->gte(Carbon::now()) && $date_call->diffInSeconds(Carbon::now()) < 600 && !$schedule->email_sent) {
                                $message->setTo($user->email);
                                // $message->setTo('makraz.hamza@gmail.com');
                                $message->setSubject('Reminder follow-ups');
                                $message->setBody("you need to call $contact->first_name $contact->last_name in 10 minutes", 'text/html');

                                try {
                                    $response = $mailer->send($message);
                                    if ($response == 1) {
                                        $schedule = DB::table('schedule_calls')
                                            ->where('id', $schedule->id)
                                            ->update(['email_sent' => true]);
                                        echo "\n | reminder email sent to $user->id | " . Carbon::now() . " |\n";
                                    } else {
                                        echo "\n | email not sent to $user->id ** $response | " . Carbon::now() . " |\n";
                                    }
                                } catch (\Exception $e) {
                                    $response = $e->getMessage();
                                    echo "\n | email not sent to $user->id ** $response | " . Carbon::now() . " |\n";
                                }
                                // after 24 h
                            } elseif ($date_call->lt(Carbon::now()) && $date_call->diffInSeconds(Carbon::now()) >= 86400) {
                                DB::table('queues')
                                    ->where('contact_id', $contact->id)
                                    ->where('user_id', $user->id)
                                    ->update(['enabled' => true]);
                                echo "\n | re-assign this $contact->id contact to another caller |" . Carbon::now() . " |\n";
                            } else {
                                echo "\n | no action on the schedule row $schedule->id |" . Carbon::now() . " |\n";
                            }
                        } elseif ($call->answer == Call::ANSWER_LEFT_MESSAGE) {
                            if ($date_call->lt(Carbon::now()) && $date_call->diffInSeconds(Carbon::now()) >= 0) {
                                DB::table('queues')
                                    ->where('contact_id', $contact->id)
                                    ->where('user_id', $user->id)
                                    ->update(['enabled' => true]);
                            }
                        }
                    }
                }
            } else {
                echo "\n | no call in the queue |" . Carbon::now() . " |\n";
            }

            echo "\n End checking calls in progress |" . Carbon::now() . " | ***\n";
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
