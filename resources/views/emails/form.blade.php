<style type="text/css">
    .ck-editor__editable {
        height: 150px;
    }
</style>
<div class="row">
    <div class="col-xs-12 col-sm-8 col-md-offset-1 col-md-6">
        <div class="form-group">
            <label>Type:</label>
            {!! Form::select('type',['company'=>'Company','candidate'=>'Candidate'],null,['placeholder'=>'Select type','class'=>'form-control','required'=>true]) !!}
        </div>
        <div class="form-group">
            <label>Use me:</label>
            {!! Form::select('use_me',['0'=>'No','1'=>'Yes'],null,['class'=>'form-control','required'=>true]) !!}
        </div>
        <div class="form-group">
            <label>Subject:</label>
            {!! Form::text('subject',null,['placeholder'=>'Subject','class'=>'form-control','required'=>true]) !!}
        </div>
        <div class="form-group">
            <label>Message:</label>
            {!! Form::textarea('message',null,['id'=>'message','placeholder'=>'Message','class'=>'form-control']) !!}
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
            <li><strong>{caller}</strong>:  Caller name</li>
            <li><strong>{company}</strong>:  Company name</li>
            <li><strong>{contact}</strong>:  Contact name</li>
            <li><strong>{position}</strong>: Position</li>
            <li><strong>{affiliate}</strong>: specific affiliate link for profile</li>
        </ul>
    </div>
</div>
<script src="https://cdn.ckeditor.com/ckeditor5/1.0.0-alpha.2/classic/ckeditor.js"></script>
<script type="text/javascript">
    ClassicEditor.create(document.querySelector('#message')).then(editor => {
    }).catch( error => {
        console.error(error);
    });
</script>