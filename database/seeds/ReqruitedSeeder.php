<?php
use App\Setting;
use Illuminate\Database\Seeder;

class ReqruitedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reqruited_closer = new Setting();
        $reqruited_closer->key = 'payout_reqruited';
        $reqruited_closer->name = 'Payout Reqruited';
        $reqruited_closer->value = '60';
        $reqruited_closer->save();
    }
}
