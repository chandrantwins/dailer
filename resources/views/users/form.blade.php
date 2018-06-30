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
        {!! Form::select('role',$roles,null,['placeholder'=>'Select user type','class'=>'form-control','required'=>true]) !!}
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
	  
	<div class=" col-xs-12 col-sm-8 col-md-6 form-group">
		<label>Leaders:</label>
		<select class="select2 form-control" name="leader" id="leader">
			<option value=""></option>
			@foreach($leaders as $leader)
			<option value="<?= $leader->id ?>"><?= $leader->first_name.' '.$leader->last_name ?></option>
			@endforeach
		</select>
	</div>    
 
    <div class="col-xs-12 form-group">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>