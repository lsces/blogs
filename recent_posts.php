<?php
/**
 * @version $Header$
 * 
 * @package blogs
 * @subpackage functions
 */

/**
 * Initial Setup
 */
namespace Bitweaver\Blogs;

require_once '../kernel/includes/setup_inc.php';

// Load blog list
require_once BLOGS_PKG_INCLUDE_PATH.'lookup_blog_inc.php';

// Is package installed and enabled
$gBitSystem->verifyPackage( 'blogs' );

// Now check permissions to access this page - global check only, no specific blog loaded here
$gBitUser->verifyPermission( 'p_blogs_view' );

// Display the template
$gDefaultCenter = 'bitpackage:blogs/center_list_blog_posts.tpl';
$gBitSmarty->assign( 'gDefaultCenter', $gDefaultCenter );

$gBitSystem->display( 'bitpackage:kernel/dynamic.tpl', 'List Blog Posts' , [ 'display_mode' => 'display' ]);
