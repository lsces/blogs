<div class="modal fade" id="blogPostModal" tabindex="-1" role="dialog" aria-labelledby="blogPostModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
				<h4 class="modal-title" id="blogPostModalLabel"></h4>
			</div>
			<div class="modal-body">
				<p class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">{tr}Close{/tr}</button>
				<a href="#" class="btn btn-primary">{tr}View full post{/tr}</a>
			</div>
		</div>
	</div>
</div>
<script>
$('#blogPostModal').on('show.bs.modal', function(e) {
	var btn = $(e.relatedTarget);
	var modal = $(this);
	modal.find('.modal-title').text(btn.data('postTitle') || '');
	modal.find('.modal-footer a.btn-primary').attr('href', btn.data('postUrl') || '#');
	var body = modal.find('.modal-body');
	body.html('<p class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></p>');
	$.get(btn.data('fetchUrl'), function(html) {
		body.html(html);
	}).fail(function() {
		body.html('<p class="text-danger">{tr}Could not load post.{/tr}</p>');
	});
});
</script>
