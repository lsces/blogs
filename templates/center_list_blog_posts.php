<?php
/**
 * @version $Header$
 * @package bitweaver
 */
use Bitweaver\Blogs\BitBlogPost;
use Bitweaver\Users\RoleUser;

global $moduleParams;

include_once BLOGS_PKG_INCLUDE_PATH.'lookup_post_inc.php';

$blogPost = new BitBlogPost();
if( empty( $gContent )) {
	$gBitSmarty->assign( 'gContent', $blogPost );
}

if( $gBitUser->hasPermission( 'p_blog_posts_read_future' ) || $gBitUser->isAdmin() ) {
	$futuresHash = [];
    $futuresHash['max_records'] = !empty( $_REQUEST['max_records'] ) ? $_REQUEST['max_records'] : $gBitSystem->getConfig( 'blog_posts_max_list' );
    $futuresHash['get_future'] = true;
	if( empty( $futuresHash['user_id'] )) {
		if( !empty( $gQueryUserId )) {
			$futuresHash['user_id'] = $gQueryUserId;
		} elseif ( isset($_REQUEST['user_id']) ) {
			$futuresHash['user_id'] = $_REQUEST['user_id'];
		}
	}
	// prevent anything lower than publicly visible be displayed in blog roll
	$futuresHash['enforce_status'] = true;
	$futuresHash['min_owner_status_id'] = 0;
    $futures = $blogPost->getFutureList( $futuresHash );
    $gBitSmarty->assign( 'futures', $futures['data'] ?? []);
} else {
    $_REQUEST['max_records'] = $gBitSystem->getConfig( 'blog_posts_max_list' );
}

// various options might be set in module_params
/*
$listHash = $module_params;
$listHash = array(
	'max_records'   => $module_rows,
	'parse_data'    => true,
	'load_comments' => true,
);
*/

$listHash = [];
if( !empty( $moduleParams->values )) {
	$listHash = array_merge( $_REQUEST, $moduleParams->values );
	$listHash['max_records'] = $moduleParams->values['module_rows'];
	//$listHash['parse_data'] = true;
	//$listHash['load_comments'] = true;
} else {
    $listHash = $_REQUEST;
}

RoleUser::userCollection( $_REQUEST, $listHash );

if ( empty( $listHash['sort_mode'] ) ){
	$listHash['sort_mode'] = 'sort_date_desc';
}

if( !$gBitUser->hasPermission( 'p_blogs_admin' )) {
	$listHash['content_perm_name'] = 'p_blogs_view';
}


$paginationPath = BLOGS_PKG_URL.'index.php';

/* if a blog_id is passed from the modle settings then
 * we want to push to the view page if the user looks for older posts
 * i.e. this is for pagination
 */

if ( !empty( $module_params ) && !empty( $module_params['blog_id'] ) ){
	$gBitSmarty->assign( 'blogId', $module_params['blog_id']  );
	$paginationPath = BLOGS_PKG_URL.'view.php';
}

// prevent anything lower than publicly visible be displayed in blog roll
$listHash['enforce_status'] = true;
$listHash['min_owner_status_id'] = 0;

/* I think this is right - usually we pass in $_REQUEST
 * but in this case I pass in the listHash because
 * this is in a module - change it if its a mistake wjames5
 */
$blogPost->invokeServices( 'content_list_function', $listHash );
$blogPosts = $blogPost->getList( $listHash );
$gBitSmarty->assign( 'paginationPath', $paginationPath );
$gBitSmarty->assign( 'gQueryUserId', $listHash['user_id'] ?? 0 );
$gBitSmarty->assign( 'blogPosts', $blogPosts );

$gBitSmarty->assign( 'listInfo', $listHash );
$gBitSmarty->assign( 'descriptionLength', $gBitSystem->getConfig( 'blog_posts_description_length', 500 ));
$gBitSmarty->assign( 'showDescriptionsOnly', true );
$gBitSmarty->assign( 'showBlogTitle', 'y' );
$gBitSmarty->assign( 'blogPostsFormat', empty( $module_params['format'] ) ? 'full' : $module_params['format'] );
// unfortunately, the following feature pulls module parameters in from other modules
//$gBitSmarty->assign( 'centerTitle', $moduleParams['title'] );