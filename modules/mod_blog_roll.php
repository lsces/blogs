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

global $moduleParams;

$listHash['max_records'] = $moduleParams->value['module_rows'];
$listHash['sort_mode'] = 'created_desc'; // ( !empty( $moduleParams['module_params']['sort_mode'] ) ) ? $moduleParams['module_params']['sort_mode'] : 'created_desc';
RoleUser::userCollection( $moduleParams->value['module_params'] ?? null, $listHash );

$blog = new BitBlog();
if( $modBlogs = $blog->getList( $listHash ) ) {
	foreach( array_keys( $modBlogs ) as $b ) {
		$modBlogs[$b]['post'] = $blog->getPost( [ 'blog_id' => $modBlogs[$b]['blog_id'] ] );
	}
	$gBitSmarty->assign( 'modBlogs', $modBlogs );
}

$moduleTitle = !empty( $moduleParams->value['title'] ) ? $moduleParams->value['title'] : 'Blog Roll';
$gBitSmarty->assign( 'moduleTitle', $moduleTitle );