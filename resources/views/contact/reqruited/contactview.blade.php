@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Show Contact</h2>
        </div>
        @if (\App\User::ADMIN == Auth::user()->role)
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('users.index',['type'=>'candidate']) }}"> Back</a>
        </div>
        @endif
    </div>
</div>
<hr>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-offset-2 col-md-8">
        <div id="here"></div>        
        <table class="table table-hover">
            <tbody>
                <tr>
                    <th width="20%">Contact name</th>
                    <td>{{$contact->first_name}} {{$contact->last_name}}</td>
                </tr>
                <tr>
                    <th width="20%">Company name</th>
                    <td>{{$contact->company_name}}</td>
                </tr>
                <tr>
                    <th width="20%">Title</th>
                    <td>{{$contact->title}}</td>
                </tr>                
                <tr>
                    <th width="20%">Email address</th>
                    <td>{{$contact->email}}</td>
                </tr>
                <tr>
                    <th width="20%">Phone number</th>
                    <td>{{$contact->phone}}</td>
                </tr>
                <tr>
                    <th width="20%">Note: </th>
                    <td>
                        @php
                            echo '<ui>';
                            foreach($contact->calls as $call){
                                if(!is_null($call->note)){
                                    echo '<li>'.$call->note.'</li>';
                                }
                            }
                            echo '</ul>';
                        @endphp              
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-xs-12">
        <a href="{{ URL::previous() }}" class="btn btn-default">Back</a>
    </div>
</div>
@endsection