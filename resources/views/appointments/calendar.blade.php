@extends('layouts.app')

@section('content')
<div id="here"></div>
<div class="row">
<h3> Appoinment Details</h3>
        @if(isset($calendar_details))		
                {!! $calendar_details->calendar() !!}                    
        @endif 		
</div>
@endsection