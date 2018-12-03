<?php
use App\Setting;
use Illuminate\Database\Seeder;

class ReqruitedEmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reqruited_closer = new Setting();
        $reqruited_closer->key = 'app_reqruited_email';
        $reqruited_closer->name = 'Reqruited Email';
        $reqruited_closer->value = 'info@reqruited.com';
        $reqruited_closer->save();
    }
}
