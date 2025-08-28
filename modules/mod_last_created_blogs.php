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
global $gQueryUserId, $module_rows, $module_params;

$listHash['max_records'] = $module_rows;
$listHash['sort_mode'] = 'created_desc';
RoleUser::userCollection( $module_params, $listHash );

$blog = new BitBlog();
$gBitSmarty->assign( 'modLastCreatedBlogs', $blog->getList( $listHash ) );