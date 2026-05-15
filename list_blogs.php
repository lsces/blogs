<?php
/**
 * @version $Header$
 * @package blogs
 * @subpackage functions
 */

// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.

/**
 * required setup
 */
require_once '../kernel/includes/setup_inc.php';
use Bitweaver\KernelTools;

$gBitSystem->verifyPackage( 'blogs' );

require_once BLOGS_PKG_INCLUDE_PATH.'lookup_blog_inc.php';

$gBitSystem->verifyPermission( 'p_blogs_view' );

$gBitSystem->setBrowserTitle(KernelTools::tra("View All Blogs"));

if( $gContent->isValid() && isset($_REQUEST["remove"])) {
	$gBitSystem->setBrowserTitle(KernelTools::tra("Delete Blog"));

	// Check if has edit perm of this blog
	$gContent->verifyUpdatePermission();
	if( !empty( $_REQUEST['cancel'] ) ) {
		// user cancelled - just continue on, doing nothing
	} elseif( empty( $_REQUEST['confirm'] ) ) {
		$formHash['remove'] = $_REQUEST["remove"];
		$formHash['blog_id'] = $gContent->mBlogId;
		$gBitSystem->confirmDialog( $formHash,
			[
				'warning' => KernelTools::tra('Are you sure you want to delete this blog?') . ' ' . $gContent->getTitle(),
				'error' => KernelTools::tra('This cannot be undone!'),
			],
		);
	} else {
		$gContent->expunge();
	}
}

// Get a list of last changes to the Wiki database
$blogsList = $gContent->getList( $_REQUEST );
$gBitSmarty->assign( 'listInfo', $_REQUEST['listInfo'] );
$gBitSmarty->assign( 'blogsList', $blogsList );

// Display the template
$gBitSystem->display( 'bitpackage:blogs/list_blogs.tpl', null, [ 'display_mode' => 'list' ]);
