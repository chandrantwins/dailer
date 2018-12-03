<?php

use App\Setting;
use Illuminate\Database\Seeder;

class SettingSMTPSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * >php artisan db:seed --class=SettingSMTPSeeder
     */
    public function run()
    {
        $mail_host = new Setting();
        $mail_host->key = 'reqruited_mail_host';
        $mail_host->name = 'Reqruited SMTP host';
        $mail_host->value = 'smtp.gmail.com';
        $mail_host->save();

        $mail_port = new Setting();
        $mail_port->key = 'reqruited_mail_port';
        $mail_port->name = 'Reqruited SMTP port';
        $mail_port->value = '587';
        $mail_port->save();

        $mail_encryption = new Setting();
        $mail_encryption->key = 'reqruited_mail_encryption';
        $mail_encryption->name = 'Reqruited SMTP encryption';
        $mail_encryption->value = 'tls';
        $mail_encryption->save();

        $mail_username = new Setting();
        $mail_username->key = 'reqruited_mail_username';
        $mail_username->name = 'Reqruited SMTP username';
        $mail_username->value = 'rxr99a@gmail.com';
        $mail_username->save();

        $mail_password = new Setting();
        $mail_password->key = 'reqruited_mail_password';
        $mail_password->name = 'Reqruited SMTP password';
        $mail_password->value = '!Jericho@';
        $mail_password->save();
    }
}
