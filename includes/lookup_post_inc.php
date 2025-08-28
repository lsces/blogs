<?php
/**
 * @package blogs
 * @subpackage functions
 */

/**
 * Initial Setup
 */
global $gContent; 
use Bitweaver\Blogs\BitBlogPost;
use Bitweaver\BitBase;
require_once LIBERTY_PKG_INCLUDE_PATH.'lookup_content_inc.php';

if( empty( $gContent ) || !is_object( $gContent ) || !$gContent->isValid() ) {
	// if blog_id supplied, use that
    if( BitBase::verifyId( $_REQUEST['post_id'] ?? 0 ) ) {
		$gContent = new BitBlogPost( $_REQUEST['post_id'] );
		$gContent->load();
    } elseif( BitBase::verifyId( $_REQUEST['content_id'] ?? 0 ) ) {
		$gContent = new BitBlogPost( null, $_REQUEST['content_id'] );
		$gContent->load();
	} else {
		$gContent = new BitBlogPost();
	}

	$gBitSmarty->assign( 'gContent', $gContent );
}