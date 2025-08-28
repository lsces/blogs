<?php
/**
 * @version $Header$
 * @package blogs
 * @subpackage modules
 */

/**
 * required setup
 */
use Bitweaver\Blogs\BitBlog;
use Bitweaver\Users\RoleUser;
global $gQueryUserId, $gBitThemes, $module_rows, $module_params;

$listHash['max_records'] = $module_rows;
$listHash['sort_mode'] = 'activity_desc';
RoleUser::userCollection( $module_params, $listHash );
$listHash['is_active'] = true;

$blog = new BitBlog();
$ranking = $blog->getList( $listHash );
if( !empty( $ranking ) ) {
	$gBitSmarty->assign( 'modTopActiveBlogs', $ranking );
}