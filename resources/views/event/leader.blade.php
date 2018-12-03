@extends('layouts.app')

@section('content')
	<div class="panel panel-primary">
		<div class="panel-heading"> Event Details</div>
		<div class="panel-body">                    
			{!! $calendar_details->calendar() !!}
		</div>
                <!-- Modal -->
	<div class="modal fade" id="closerModal" tabindex="-1" role="dialog" aria-labelledby="closerModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="closerModalLabel">Details</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body" id="closer_modal">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>	        
				</div>
			</div>
		</div>
	</div>
	</div>
<script type="text/javascript">
	function showModal(x) {
		console.log(x);
		var html = '<strong>' + x.title + '</strong>';
		var aux = new Date(x.start.toString());
		html = html + '<br><strong>Start Date: </strong>' + ((aux.getDate() < 10? '0' : '') + aux.getDate()) + '/' + ((aux.getMonth() < 9? '0' : '') + (aux.getMonth() + 1)) + '/' + aux.getFullYear();		
		var aux = new Date(x.end.toString());
		html = html + '<br><strong>End Date: </strong>' + ((aux.getDate() < 10? '0' : '') + aux.getDate()) + '/' + ((aux.getMonth() < 9? '0' : '') + (aux.getMonth() + 1)) + '/' + aux.getFullYear();
		$("#closer_modal").html(html);
		$("#closerModal").modal('show');
		// change the day's background color just for fun
		$(this).css('background-color', 'red');
	}
</script>	
@endsection