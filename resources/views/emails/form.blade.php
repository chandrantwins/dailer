<style type="text/css">
    .ck-editor__editable {
        height: 150px;
    }
</style>
<div class="row">
    <div class="col-xs-12 col-sm-8 col-md-offset-1 col-md-6">
        <div class="form-group">
            <label>Type:</label>
            {!! Form::select('type',['company'=>'Company','candidate'=>'Candidate','closer'=>'Closer','reqruited'=>'Reqruited'],null,['placeholder'=>'Select type','class'=>'form-control','required'=>true]) !!}
        </div>
        <div class="form-group">
            <label>Layout:</label>
            {!! Form::select('layout_id',['1'=>'layout1','2'=>'layout2'],null,['class'=>'form-control','required'=>true]) !!}
        </div> 
         <div class="form-group">
        {!! Form::hidden('use_me', 1, array('id' => 'use_me')) !!}
         </div>
        <div class="form-group">
            <label>SMTP:</label>
            {!! Form::select('smtp',['reqruited'=>'Reqruited','mail'=>'Mail'],null,['placeholder'=>'Select SMTP','class'=>'form-control','required'=>true]) !!}
        </div>
        <div class="form-group">
            <label>Action:</label>
            {!! Form::select('handle',['Onboarding'=>'Onboarding','followupreminder'=>'Followup','sendemailbutton'=>'Sendemailbutton',
            'appointmentusersms'=>'Appointmentusersms','appointmentuseremail'=>'Appointmentuseremail','appointmentcontactsms'=>'Appointmentcontactsms','appointmentcontactemail'=>'Appointmentcontactemail'],null,['placeholder'=>'Select type','class'=>'form-control','required'=>true]) !!}            
        </div>
        <div class="form-group">
            <label>Subject:</label>
            {!! Form::text('subject',null,['placeholder'=>'Subject','class'=>'form-control','required'=>true]) !!}
        </div>
        <div class="form-group">
            <label>Message:</label>
            {!! Form::textarea('content',null,['id'=>'content','placeholder'=>'Message','class'=>'form-control']) !!}
        </div>
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4 col-md-4" style="border-left: gray solid 1px;">
        <strong>FYI:</strong>
        <hr>
        <p>You can use tagging system by copying the follow tags:</p>
        <ul>
            <li><strong>{Callername}</strong>:  Caller name</li>
            <li><strong>{Leadername}</strong>:  Leader name</li>
            <li><strong>{Contactname}</strong>:  Contact name</li>
            <li><strong>{Callerphone}</strong>:  Caller phone</li>
            <li><strong>{Emailaddress}</strong>:  Emailaddress</li>
            <li><strong>{Password}</strong>:  Password</li>
            <li><strong>{Position}</strong>: Position</li>
            <li><strong>{Affiliatelink}</strong>: specific affiliate link for profile</li>
        </ul>
    </div>
</div>
<script src="https://cdn.ckeditor.com/ckeditor5/1.0.0-alpha.2/classic/ckeditor.js"></script>
<script type="text/javascript">
    ClassicEditor.create(document.querySelector('#content')).then(editor => {
    }).catch( error => {
        console.error(error);
    });
</script>