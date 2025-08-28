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

global $gQueryUserId, $moduleParams;
//$params = $moduleParams['module_params'];

$listHash['max_records'] = $moduleParams->value['module_rows'];
$listHash['sort_mode'] = 'last_modified_desc';
RoleUser::userCollection( $moduleParams->value, $listHash );

$blog = new BitBlog();
$ranking = $blog->getList( $listHash );

$gBitSmarty->assign( 'modLastModifiedBlogs', $ranking);