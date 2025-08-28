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

//$params = $gBitThemes->getModuleParameters('bitpackage:blogs/mod_top_visited_blogs.tpl', $gQueryUserId);

$listHash['max_records'] = $moduleParams->value['module_rows'] ?? 10;
$listHash['sort_mode'] = 'hits_desc';

//produces White Screen Of Death:
//$listHash['is_hit'] = true;

RoleUser::userCollection( $_REQUEST, $listHash );

$blog = new BitBlog();
$ranking = $blog->getList( $listHash );

$gBitSmarty->assign( 'modTopVisitedBlogs', $ranking);
$gBitSmarty->assign( 'bulletSrc', $params['bullet'] ?? '');