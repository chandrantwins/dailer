<div class="row">
    <div class="col-xs-12 col-sm-8 col-md-6 form-group">
        <label>First name:</label>
        {!! Form::text('first_name', null, ['placeholder'=>'First name','class'=>'form-control']) !!}
    </div>
    <div class="col-xs-12 col-sm-8 col-md-6 form-group">
        <label>Last name:</label>
        {!! Form::text('last_name', null, ['placeholder'=>'Last name','class'=>'form-control']) !!}
    </div>
    <div class="col-xs-12 col-sm-8 col-md-6 form-group">
        <label>Role:</label>
        {!! Form::select('role',$roles,null,['placeholder'=>'Select user type','class'=>'form-control','required'=>true,'id'=>'roles']) !!}
    </div>
    <div class="col-xs-12 col-sm-8 col-md-6 form-group">
        <label>Affiliate link:</label>
        {!! Form::text('affiliate', null, ['placeholder'=>'Affiliate link','class'=>'form-control']) !!}
    </div>
    <div class="col-xs-12 col-sm-8 col-md-6 form-group">
        <label>Username:</label>
        {!! Form::text('username', null, ['placeholder'=>'Username','class'=>'form-control']) !!}
    </div>
    <div class="col-xs-12 col-sm-8 col-md-6 form-group">
        <label>Email:</label>
        {!! Form::email('email', null, ['placeholder'=>'Email address','class'=>'form-control']) !!}
    </div>
    <div class="col-xs-12 col-sm-8 col-md-6 form-group">
        <label>Password:</label>
        {!! Form::password('password', ['placeholder'=>'Password','class'=>'form-control']) !!}
    </div>
    <div class="col-xs-12 col-sm-8 col-md-6 form-group">
        <label>Confirm password:</label>
        {!! Form::password('password_confirmation', ['placeholder'=>'Confirm password','class'=>'form-control']) !!}
    </div>
    <div class="col-xs-12 col-sm-8 col-md-6 form-group">
        <label>Phone:</label>
        {!! Form::text('phone', null, ['placeholder'=>'Phone number','class'=>'form-control']) !!}
    </div>
	  
	<div class=" col-xs-12 col-sm-8 col-md-6 form-group" id='leaderrole'>
		<label>Leaders:</label>
                {!! Form::select('user_id',$leaders,null,['placeholder'=>'Select Leader','class'=>'form-control','required'=>true,'id'=>'user_id']) !!}
	</div>    
 
    <div class="col-xs-12 form-group">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>