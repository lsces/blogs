{strip}
{if !empty($packageMenuTitle)}<a class="dropdown-toggle" data-toggle="dropdown" href="#"> {tr}{$packageMenuTitle}{/tr} <b class="caret"></b></a>{/if}
<ul class="{$packageMenuClass}">
	{if $gBitUser->hasPermission( 'p_blogs_view' )}
		{if $gBitSystem->isFeatureActive( 'blog_home' )}
			<li><a class="item" href="{$smarty.const.BLOGS_PKG_URL}index.php">{biticon ipackage="icons" iname="go-home" iexplain="Home Blog" ilocation=menu}</a></li>
		{/if}
		<li><a class="item" href="{$smarty.const.BLOGS_PKG_URL}recent_posts.php">{biticon ipackage="icons" iname="dialog-information" iexplain="Recent Posts" ilocation=menu}</a></li>
		<li><a class="item" href="{$smarty.const.BLOGS_PKG_URL}list_blogs.php?sort_mode=last_modified_desc">{biticon ipackage="icons" iname="view-list-text" iexplain="List Blogs" ilocation=menu}</a></li>
	{/if}
	{if $gBitUser->hasPermission( 'p_blogs_create' )}
		<li><a class="item" href="{$smarty.const.BLOGS_PKG_URL}edit.php">{biticon ipackage="icons" iname="folder"   iexplain="Create a Blog" ilocation=menu}</a></li>
	{/if}
	{if $gBitUser->hasPermission( 'p_blogs_post' )}
		<li><a class="item" href="{$smarty.const.BLOGS_PKG_URL}post.php">{biticon ipackage="icons" iname="view-list-text" iexplain="Write Blog Post" ilocation=menu}</a></li>
	{/if}
	{if $gBitSystem->isFeatureActive( 'blog_rankings' ) && $gBitUser->hasPermission( 'p_blogs_view' )}
		<li><a class="item" href="{$smarty.const.BLOGS_PKG_URL}rankings.php">{biticon ipackage="icons" iname="view-sort-ascending" iexplain="Blog Post Rankings" ilocation=menu}</a></li>
	{/if}
</ul>
{/strip}
