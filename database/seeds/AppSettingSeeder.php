<?php

use App\Setting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mail_host = new Setting();
        $mail_host->key = 'app_invoicedapikey';
        $mail_host->name = 'Api Key';
        $mail_host->value = 'AznnWEqy9s2cj3sRq7lROSjNq5PTwf7N';
        $mail_host->save();

        $mail_port = new Setting();
        $mail_port->key = 'app_apisandbox';
        $mail_port->name = 'API sandobx (yes or no)';
        $mail_port->value = 'yes';
        $mail_port->save();
    }
}
