<?php
/**
 * @version $Header$
 *
 * @package blogs
 * @subpackage functions
 */

// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.

/**
 * required setup
 */
namespace Bitweaver;

require_once '../kernel/includes/setup_inc.php';
use Bitweaver\Blogs\BitBlog;

$gBitSystem->verifyPackage( 'blogs' );

require_once BLOGS_PKG_INCLUDE_PATH.'lookup_post_inc.php';

if ( isset( $_REQUEST["blog_id"] ) && $_REQUEST["blog_id"] > 0 ) {
	#setup so we know what the default target blog is in the template
	$gBlog = new BitBlog($_REQUEST["blog_id"]);
	$gBlog->load();
	$gBitSmarty->assign('default_target_blog_content_id',$gBlog->mContentId );
} elseif ( !empty( $gContent->mInfo['blogs'] ) ) {
	$gBlog = new BitBlog($gContent->mInfo['blogs']);
	$_REQUEST["blog_id"] = 0;
} else {
	$gBlog = new BitBlog();
	$_REQUEST["blog_id"] = 0;
}

//must be owner or admin to edit an existing post
if( $gContent->isValid() ) {
	$gContent->verifyUpdatePermission();
} else {
	$gContent->verifyCreatePermission();
}

// nuke post if requested
if( !empty( $_REQUEST['action'] ) ) {
	if( $_REQUEST['action'] == 'remove' && $gContent->isValid() ) {
		if( isset( $_REQUEST["confirm"] ) ) {
			$gBitUser->verifyTicket();
			$redirect = !empty( $gContent->mInfo['blogs'] ) ? BLOGS_PKG_URL.'view.php?content_id='.key( $gContent->mInfo['blogs'] ) : BLOGS_PKG_URL;
			if( $gContent->expunge() ) {
				KernelTools::bit_redirect( $redirect );
			} else {
				$feedback['error'] = $gContent->mErrors;
			}
		}
		$gBitSystem->setBrowserTitle( 'Confirm removal of '.$gContent->getTitle() );
		$formHash['remove'] = true;
		$formHash['action'] = 'remove';
		$formHash['post_id'] = $_REQUEST['post_id'];
		$msgHash = [
			'label' => KernelTools::tra('Remove Blog Post'),
			'confirm_item' => $gContent->getTitle(),
			'warning' => KernelTools::tra( 'This will remove the above blog post.' ),
			'error' => KernelTools::tra( 'This cannot be undone!' ),
		];
		$gBitSystem->confirmDialog( $formHash, $msgHash );
	}
}

if (isset($_REQUEST['remove_image'])) {
	$gContent->expungeAttachment( $_REQUEST['remove_image'] );
}

if( isset( $_REQUEST['format_guid'] ) && !isset( $gContent->mInfo['format_guid'] ) ) {
	$formInfo['format_guid'] = $gContent->mInfo['format_guid'] = $_REQUEST['format_guid'];
}

if (isset($_REQUEST["preview"])) {
	$post = $gContent->preparePreview( $_REQUEST );
	$gBitSmarty->assign( 'preview', true );
	$gContent->invokeServices( 'content_preview_function' );
	$gBitSmarty->assign( 'post_info', $post );
	/* minor hack to accomodate the view_blog_post.tpl
	 * this can eventually be removed with a change to the tpl to use post_info['parsed_data'] 
	 * but requires clean up in a few places.
	 */
	$gBitSmarty->assign('parsed_data', $post['parsed_data']);
} elseif (isset($_REQUEST['save_post']) || isset($_REQUEST['save_post_exit'])) {
	// Editing page needs general ticket verification
//	$gBitUser->verifyTicket();

	// preserve a copy of the request data because if store fails we need to reprocess
	$requestCopy = $_REQUEST;

	if( $gContent->store( $_REQUEST ) ) {
		$postid = $gContent->mPostId;
		$gBitSmarty->assign('post_id', $gContent->mPostId);

		if (isset($_REQUEST['save_post_exit'])) {
			header ("location: ".BLOGS_PKG_URL."view_post.php?post_id=$postid");
			die;
		}

		$parsed_data = $gContent->getParsedData();

		$gBitSmarty->assign( 'title', $gContent->getTitle() );
		$gBitSmarty->assign( 'trackbacks_to', explode(',', $gContent->getField('trackbacks_to')) );
		$gBitSmarty->assign( 'parsed_data', $parsed_data );
	} else {
		$post = $gContent->preparePreview( $requestCopy );
		$gContent->invokeServices( 'content_preview_function' );
		$gBitSmarty->assign( 'post_info', $post );
		$gBitSmarty->assign('parsed_data', $post['parsed_data']);
	}
} elseif( !empty( $_REQUEST['edit'] ) ) {
} else {
	$gContent->invokeServices( 'content_edit_function' );
	if( $gContent->isValid() && $gContent->getContentStatus() == -5 && $gContent->getField('publish_date') < $gBitSystem->getUTCTime() ){
		/* if we are working with a draft and a future publish date is not set 
		 * then we automatically move the publish date up to NOW to help users from publishing in the past.
		 * if they set it backward and preview or save the back date will be preserved.
		 */
		$gContent->mInfo['publish_date'] = $gBitSystem->getUTCTime();
	}
	$gBitSmarty->assign('post_info', $gContent->mInfo);
}

// Get List of available blogs
$listHash = [];
$listHash['sort_mode'] = 'title_asc';
$listHash['max_records'] = BIT_MAX_RECORDS;
if( !$gBitUser->hasPermission( 'p_blogs_admin' )) {
	$listHash['user_id'] = $gBitUser->mUserId;
	$listHash['content_perm_name'] = 'p_blogs_post';
}
$blogs = $gBlog->getList( $listHash );
$availableBlogs = [];
foreach( array_keys( $blogs ) as $blogContentId ) {
	$availableBlogs[$blogContentId] = $blogs[$blogContentId]['title'];
}
$gBitSmarty->assign( 'availableBlogs', $availableBlogs );

$gBitSmarty->assign('blogs', $blogs);
if (isset($_REQUEST['blog_content_id'])) {
	$gBitSmarty->assign('blog_content_id', $_REQUEST['blog_content_id'] );
}

$gBitSmarty->assign( 'errors', $gContent->mErrors );

$gBitSmarty->assign( 'textarea_label', KernelTools::tra('Post Content') );

// tweak title displayed for better usuability in browser history
$gBitSystem->display( 'bitpackage:blogs/blog_post.tpl', $gContent->isValid() ? KernelTools::tra( "Edit Blog Post" ).": ".$gContent->getTitle() : KernelTools::tra( "Create Blog Post" ) , [ 'display_mode' => 'edit' ] );
