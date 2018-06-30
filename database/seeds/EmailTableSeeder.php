<?php

use Illuminate\Database\Seeder;
use App\Email;

class EmailTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $email_company = new Email();
        $email_company->type = 'company';
        $email_company->use_me = true;
        $email_company->subject = 'test';
        $email_company->message = '<p><strong>FYI:</strong>&nbsp;</p><p>You can use tagging system by copying the follow tags:</p><ul><li><strong>{caller}</strong>: Caller name</li><li><strong>{company}</strong>: Company name</li><li><strong>{contact}</strong>: Contact name</li><li><strong>{position}</strong>: Position</li><li><strong>{affiliate}</strong>: specific affiliate link for profile</li></ul>';
        $email_company->save();

        $email_candidate = new Email();
        $email_candidate->type = 'candidate';
        $email_candidate->use_me = true;
        $email_candidate->subject = 'test';
        $email_candidate->message = '<p><strong>FYI:</strong>&nbsp;</p><p>You can use tagging system by copying the follow tags:</p><ul><li><strong>{caller}</strong>: Caller name</li><li><strong>{candidate}</strong>: Company name</li><li><strong>{contact}</strong>: Contact name</li><li><strong>{position}</strong>: Position</li><li><strong>{affiliate}</strong>: specific affiliate link for profile</li></ul>';
        $email_candidate->save();
    }
}
