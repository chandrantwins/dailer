<?php

use App\Setting;
use Illuminate\Database\Seeder;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mail_host = new Setting();
        $mail_host->key = 'mail_host';
        $mail_host->name = 'SMTP host';
        $mail_host->value = 'smtp.sendgrid.net';
        $mail_host->save();

        $mail_port = new Setting();
        $mail_port->key = 'mail_port';
        $mail_port->name = 'SMTP port';
        $mail_port->value = '465';
        $mail_port->save();

        $mail_encryption = new Setting();
        $mail_encryption->key = 'mail_encryption';
        $mail_encryption->name = 'SMTP encryption';
        $mail_encryption->value = 'ssl';
        $mail_encryption->save();

        $mail_username = new Setting();
        $mail_username->key = 'mail_username';
        $mail_username->name = 'SMTP username';
        $mail_username->value = 'Adastra123';
        $mail_username->save();

        $mail_password = new Setting();
        $mail_password->key = 'mail_password';
        $mail_password->name = 'SMTP password';
        $mail_password->value = 'Atlass!23';
        $mail_password->save();

        $app_email = new Setting();
        $app_email->key = 'app_email';
        $app_email->name = 'Application email';
        $app_email->value = 'info@monikl.com';
        $app_email->save();

        $app_recycled = new Setting();
        $app_recycled->key = 'app_recycled';
        $app_recycled->name = 'Number of days before recycled';
        $app_recycled->value = '7';
        $app_recycled->save();

        $payout_candidate = new Setting();
        $payout_candidate->key = 'payout_candidate';
        $payout_candidate->name = 'Payout candidate';
        $payout_candidate->value = '1';
        $payout_candidate->save();

        $payout_company = new Setting();
        $payout_company->key = 'payout_company';
        $payout_company->name = 'Payout company';
        $payout_company->value = '25';
        $payout_company->save();
    }
}
