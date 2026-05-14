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
require_once '../kernel/includes/setup_inc.php';

$gBitSystem->verifyPackage( 'blogs' );

include_once BLOGS_PKG_INCLUDE_PATH.'lookup_post_inc.php';

if( !$gContent->isValid() ) {
	$gBitSystem->setHttpStatus( 404 );
	$gBitSystem->fatalError( "The blog post you requested could not be found." );
}

$gContent->verifyViewPermission();

$now = $gBitSystem->getUTCTime();
$view = false;

if ( $gContent->hasAdminPermission()  || ( $gContent->hasUserPermission( 'p_blog_posts_read_future' ) && $gContent->hasUserPermission( 'p_blog_posts_read_expired' ) ) ){
	$view = true;
}elseif ( $gContent->mInfo['publish_date'] == $gContent->mInfo['expire_date'] ) {
	$view = true;
}elseif ( $gContent->mInfo['publish_date'] > $now && $gContent->hasUserPermission( 'p_blog_posts_read_future' ) ){
	$view = true;
}elseif ( $gContent->mInfo['expire_date'] < $now && $gContent->hasUserPermission( 'p_blog_posts_read_expired' ) ){
	$view = true;
}elseif ( ( $gContent->mInfo['publish_date'] <= $now ) && ( $gContent->mInfo['expire_date'] > $now || $gContent->mInfo['expire_date'] <= $gContent->mInfo['publish_date'] ) ){
	$view = true;
}

if ($view == true){
	include_once BLOGS_PKG_INCLUDE_PATH.'display_bitblogpost_inc.php';
}else{
	$gBitSystem->setHttpStatus( 404 );
	$gBitSystem->fatalError( "The blog post you requested could not be found." );
}

if( $gContent->isValid() ) {
	$gContent->addHit();
}
