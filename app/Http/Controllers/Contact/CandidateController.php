<?php

namespace App\Http\Controllers\Contact;

use App\Contact;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contacts = Contact::where('type', Contact::CANDIDATE)->get();

        // load the view and pass the contacts
        return view("contact.candidate.index",compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("contact.candidate.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate([
            'name'      => 'required|string|max:255',
            'phone'   => 'required|string|max:255',
        ]);

        $request->merge(['type'=>Contact::CANDIDATE]);
        Contact::create($request->all());

        return redirect()->route("contact.candidate.index")->with('alert',['class'=>'success','message'=>'Contact created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $contact = Contact::find($id);

        return view('contact.candidate.show',compact('contact'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function question($id)
    {
        $type = Contact::CANDIDATE;
        $contact = Contact::find($id);

        return view('contact.candidate.question',compact('contact','type'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $contact = Contact::find($id);

        return view('contact.candidate.edit',compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        request()->validate([
            'name'      => 'required|string|max:255',
            'phone'   => 'required|string|max:255',
        ]);

        $request->merge(['type'=>Contact::CANDIDATE]);

        Contact::find($id)->update($request->all());

        return redirect()->route('contact.candidate.index')->with('alert',['class'=>'success','message'=>'Contact updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Contact::find($id)->delete();

        return redirect()->route('contact.candidate.index')->with('alert',['class'=>'success','message'=>'Contact deleted successfully']);
    }
}