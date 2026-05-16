{* $Header$ *}
{if !( $smarty.request.home|default:true && $gBitSystem->isFeatureActive('blog_hide_empty_usr_list') ) }
<div class="floaticon">{bithelp}</div>

<div class="display blogs">
	<div class="header">
		<h2>{tr}Recent Blog Posts{/tr}</h2>
	</div>

	<div class="body">
		{if ($gBitUser->hasPermission( 'p_blog_posts_read_future' ) || $gBitUser->isAdmin() ) && $futures}
			<h3>{tr}Upcoming Blog Posts{/tr}</h3>
			<ul>
				{foreach from=$futures item=future}
					<li>{$future.display_link} <small>[ {tr}By:{/tr} {displayname hash=$future} | {tr}To be published{/tr}: {$future.publish_date|bit_long_datetime} ]</small></li>
				{/foreach}
			</ul>
			{if $blogPostsFormat == 'list'}
				<h3>{tr}Published Blog Posts{/tr}</h3>
			{/if}
		{/if}

		{if $blogPostsFormat == 'list'}
			{include file="bitpackage:blogs/list_posts.tpl"}
		{else}
			{foreach from=$blogPosts item=aPost}
				{include file="bitpackage:blogs/blog_list_post.tpl"}
			{foreachelse}
				<div class="norecords">{tr}No records found{/tr}</div>
			{/foreach}
		{/if}
	</div><!-- end .body -->

    {pagination url="`$paginationPath`" user_id="`$gQueryUserId`" blog_id="`$blogId`"}	

	{*minifind sort_mode=$sort_mode*}
</div>

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
{/if}
