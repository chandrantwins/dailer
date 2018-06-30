<?php

namespace App\Http\Controllers\Contact;

use App\Contact;
use App\Http\Controllers\Controller;
use App\Question;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contacts = Contact::where('type', Contact::COMPANY)->get();

        // load the view and pass the contacts
        return view("contact.company.index", compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("contact.company.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate([
            'contact_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
        ]);

        $request->merge(['type' => Contact::COMPANY]);
        Contact::create($request->all());

        return redirect()->route("contact.company.index")->with('alert', ['class' => 'success', 'message' => 'Contact created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $contact = Contact::find($id);

        return view('contact.company.show', compact('contact'));
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function question(Request $request, $id)
    {
        $type = Contact::COMPANY;
        $contact = Contact::find($id);
        $questions = Question::ANSWER[$type];

        if ($request->isMethod('post')) {
            if ($request->has('answer')) {
                $contact->questions()->create($request->all());

                return redirect()->route('contact.company.index')->with('alert', ['class' => 'success', 'message' => 'Answer saved successfully']);
            } else {
                return view('contact.company.question', compact('contact', 'type', 'questions'))->with('alert', ['class' => 'danger', 'message' => 'Choose an answer please!!']);
            }
        }

        return view('contact.company.question', compact('contact', 'type', 'questions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $contact = Contact::find($id);

        return view('contact.company.edit', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        request()->validate([
            'contact_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
        ]);

        $request->merge(['type' => Contact::COMPANY]);

        Contact::find($id)->update($request->all());

        return redirect()->route('contact.company.index')->with('alert', ['class' => 'success', 'message' => 'Contact updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Contact::find($id)->delete();

        return redirect()->route('contact.company.index')->with('alert', ['class' => 'success', 'message' => 'Contact deleted successfully']);
    }
}