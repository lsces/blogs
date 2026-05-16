<?php
/**
 * @package blogs
 * @subpackage functions
 */

require_once '../kernel/includes/setup_inc.php';
use Bitweaver\KernelTools;

$gBitSystem->verifyPackage( 'blogs' );

include BLOGS_PKG_INCLUDE_PATH.'lookup_post_inc.php';

if( !$gContent->isValid() || empty( $gContent->mInfo ) ) {
	$gBitSystem->fatalError( KernelTools::tra( "Unknown post" ));
}

$gContent->verifyViewPermission();

$gBitSmarty->assign( 'postInfo', $gContent->mInfo );

if( !empty( $_REQUEST['rollback_preview'] )) {
	$gBitSmarty->assign( 'rollback_preview', $_REQUEST['rollback_preview'] );
}

$smartyContentRef = 'postInfo';
$rollbackPerm     = 'p_blogs_update';
include_once LIBERTY_PKG_INCLUDE_PATH.'content_history_inc.php';

$gBitSmarty->assign( 'page', $page = !empty( $_REQUEST['page'] ) ? $_REQUEST['page'] : 1 );
if( !empty( $_REQUEST['list_page'] )) {
	$gBitSmarty->assign( 'page', $page = !empty( $_REQUEST['list_page'] ) ? $_REQUEST['list_page'] : 1 );
}

$offset  = ( $page - 1 ) * $gBitSystem->getConfig( 'max_records' );
$history = $gContent->getHistory( null, null, $offset, $gBitSystem->getConfig( 'max_records' ) );
$gBitSmarty->assign( 'data', $history['data'] );
$gBitSmarty->assign( 'listInfo', $history['listInfo'] );

$gBitSmarty->assign( 'gContent', $gContent );
$gBitSystem->display( 'bitpackage:blogs/post_history.tpl', null, [ 'display_mode' => 'display' ]);
