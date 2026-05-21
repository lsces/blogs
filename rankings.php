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
namespace Bitweaver\Blogs;

require_once '../kernel/includes/setup_inc.php';
use Bitweaver\KernelTools;
use Bitweaver\Blogs\BitBlogPost;

$gBitSystem->verifyPackage( 'blogs' );
$gBitSystem->verifyFeature( 'blog_rankings' );
$gBitSystem->verifyPermission( 'p_blogs_view' );

$rankingOptions = [
	[
		'output' => KernelTools::tra( 'Most Often Viewed' ),
		'value' => 'hits_desc',
	],
	[
		'output' => KernelTools::tra( 'Most Recently Modified' ),
		'value' => 'last_modified_desc',
	],
	[
		'output' => KernelTools::tra( 'Most Active Authors' ),
		'value' => 'top_authors',
	],
];
$gBitSmarty->assign( 'rankingOptions', $rankingOptions );

if( !empty( $_REQUEST['sort_mode'] ) ) {
	switch( $_REQUEST['sort_mode'] ) {
		case 'last_modified_desc':
			$gBitSmarty->assign( 'attribute', 'last_modified' );
			$_REQUEST['attribute'] = KernelTools::tra( 'Date of last modification' );
			break;
		case 'top_authors':
			$gBitSmarty->assign( 'attribute', 'ag_hits' );
			$_REQUEST['attribute'] = KernelTools::tra( 'Hits to items by this Author' );
			break;
		default:
			$gBitSmarty->assign( 'attribute', 'hits' );
			$_REQUEST['attribute'] = KernelTools::tra( 'Hits' );
			break;
	}
} else {
	$gBitSmarty->assign( 'attribute', 'hits' );
	$_REQUEST['attribute'] = KernelTools::tra( 'Hits' );
}

if( empty( $gContent ) ) {
	$gContent = new BitBlogPost();
}

$_REQUEST['title']             = KernelTools::tra( 'Blog Post Rankings' );
$_REQUEST['content_type_guid'] = BITBLOGPOST_CONTENT_TYPE_GUID;
$_REQUEST['max_records']       = !empty( $_REQUEST['max_records'] ) ? $_REQUEST['max_records'] : 10;
$rankList = $gContent->getContentRanking( $_REQUEST );
$gBitSmarty->assign( 'rankList', $rankList );

$gBitSystem->display( 'bitpackage:liberty/rankings.tpl', KernelTools::tra( "Blog Post Rankings" ) , [ 'display_mode' => 'display' ]);
