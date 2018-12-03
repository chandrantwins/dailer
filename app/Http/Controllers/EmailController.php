<?php

namespace App\Http\Controllers;

use App\Email;
use App\EmailTemplate;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $emails = EmailTemplate::all();

        return view('emails.index',compact('emails'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('emails.create');
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
            'subject'   => 'required|string|max:255',
            'content'  => 'required',
        ]);
        $request->handle = $request->handle.'-'.$request->type;
        if ($request->has('use_me') && $request->input('use_me') == 1) {
            EmailTemplate::where('type', $request->input('type'))->update(['use_me' => false]);
        }		
        EmailTemplate::create($request->all());

        return redirect()->route('emails.index')->with('alert', ['class' => 'success', 'message' => 'Email created successfully!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $email = EmailTemplate::find($id);

        return view('emails.show',compact('email'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $email = EmailTemplate::find($id);
        list($email->handle) = explode("-", $email->handle);
        return view('emails.edit',compact('email'));
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
            'subject'   => 'required|string|max:255',
            'content'  => 'required',
        ]);
        $requestData = $request->all();
        $requestData['handle'] = $request->handle.'-'.$request->type;        
        if ($request->has('use_me') && $request->input('use_me') == 1) {
            EmailTemplate::where('type', $request->input('type'))->update(['use_me' => false]);
        }
		
        EmailTemplate::find($id)->update($requestData);

        return redirect()->route('emails.index')->with('alert', ['class' => 'success', 'message' => 'Email updated successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        EmailTemplate::find($id)->delete();

        return redirect()->route('emails.index')->with('alert', ['class' => 'success', 'message' => 'Email deleted successfully!']);
    }
	
	public function sendmail(Request $request) {
		$data = array('name' => 'Jordan');
	
		Mail::send('emails.welcome', $data, function($message)
		{
			$message->to('zachary@morkil.com')->subject('Welcome email!');
		});
	}
}