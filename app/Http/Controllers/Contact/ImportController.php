<?php

namespace App\Http\Controllers\Contact;

use App\Contact;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $types = array_combine(Contact::TYPES, Contact::TYPES);

        if ($request->isMethod('post')) {
            $file = $request->file('file');
            $type = $request->input('type',Contact::COMPANY);

            if ($file->isFile() && $file->isReadable()) {
                $r = $this->importCSV($file->getPathname(),$type);
            }
//            request()->validate(['file'=>'required|file|mimes:csv']);
//            dd($request->isMethod('post'));

            return redirect()->route("contact.import")->with('alert', ['class' => 'success', 'message' => 'Contact imported successfully']);
        }

        // load the view and pass the contacts
        return view("contact.import.index", compact('types'));
    }

    /**
     * Update the contact for a CSV file..
     *
     * @param string $filename
     * @param string $delimiter
     * @return false|array
     */
    function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $header = null;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    public function importCSV($file,$type)
    {
        $contactArr = $this->csvToArray($file);

        for ($i = 0; $i < count($contactArr); $i++) {
            $contact = Contact::firstOrNew($contactArr[$i]);
            $contact->type = $type;
            $contact->save();

            $now = Carbon::now();
            DB::table('assign_log')->insert([
                'user_id' => $user->id,
                'contact_id' => $contact->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return true;
    }
}