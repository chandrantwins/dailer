<div class="row">
    {!! Form::hidden('type', 'company') !!}
    <div class="col-xs-12 col-md-6">
        <div class="row">
            <div class="col-xs-12 form-group">
                <label>Company name:</label>
                {!! Form::text('company_name', null, ['placeholder'=>'Company name','class'=>'form-control']) !!}
            </div>
            <div class="col-xs-12 form-group">
                <label>First name:</label>
                {!! Form::text('first_name',null,['placeholder'=>'First name','class'=>'form-control']) !!}
            </div>
            <div class="col-xs-12 form-group">
                <label>Last name:</label>
                {!! Form::text('last_name',null,['placeholder'=>'Last name','class'=>'form-control']) !!}
            </div>
            <div class="col-xs-12 form-group">
                <label>Title:</label>
                {!! Form::text('title', null, ['placeholder'=>'Title','class'=>'form-control']) !!}
            </div>
            <div class="col-xs-12 form-group">
                <label>Position:</label>
                {!! Form::text('position', null, ['placeholder'=>'Position','class'=>'form-control']) !!}
            </div>
            <div class="col-xs-12 form-group">
                <label>City position is in:</label>
                {!! Form::text('city_position', null, ['placeholder'=>'City position is in','class'=>'form-control']) !!}
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-6">
        <div class="row">
            <div class="col-xs-12 form-group">
                <label>Phone number:</label>
                {!! Form::text('phone',null,['placeholder'=>'Phone number','class'=>'form-control']) !!}
            </div>
            <div class="col-xs-12 form-group">
                <label>Email address:</label>
                {!! Form::email('email',null,['placeholder'=>'Email address','class'=>'form-control']) !!}
            </div>
            <div class="col-xs-12 form-group">
                <label>Note:</label>
                {!! Form::textarea('note', null, ['placeholder'=>'Note','class'=>'form-control']) !!}
            </div>
        </div>
    </div>
    <div class="col-xs-12 form-group text-center">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>