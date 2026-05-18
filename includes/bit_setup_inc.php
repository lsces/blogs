<?php
/**
 * @package blogs
 */
namespace Bitweaver\Blogs;

use Bitweaver\KernelTools;
global $gBitSystem, $gBitUser, $gBitSmarty;

define( 'LIBERTY_SERVICE_BLOGS', 'blogs' );

$pRegisterHash = [
	'package_name' => 'blogs',
	'package_path' => dirname( dirname( __FILE__ ) ).'/',
	'homeable' => true,
];

// fix to quieten down VS Code which can't see the dynamic creation of these ...
define( 'BLOGS_PKG_NAME', $pRegisterHash['package_name'] );
define( 'BLOGS_PKG_URL', BIT_ROOT_URL . basename( $pRegisterHash['package_path'] ) . '/' );
define( 'BLOGS_PKG_PATH', BIT_ROOT_PATH . basename( $pRegisterHash['package_path'] ) . '/' );
define( 'BLOGS_PKG_INCLUDE_PATH', BIT_ROOT_PATH . basename( $pRegisterHash['package_path'] ) . '/includes/');
define( 'BLOGS_PKG_CLASS_PATH', BIT_ROOT_PATH . basename( $pRegisterHash['package_path'] ) . '/includes/classes/');
define( 'BLOGS_PKG_ADMIN_PATH', BIT_ROOT_PATH . basename( $pRegisterHash['package_path'] ) . '/admin/');

$gBitSystem->registerPackage( $pRegisterHash );

if( $gBitSystem->isPackageActive( 'blogs' ) ) {

	if( $gBitUser->hasPermission( 'p_blogs_view' ) ) {
		$menuHash = [
			'package_name'       => BLOGS_PKG_NAME,
			'index_url'          => BLOGS_PKG_URL.'index.php',
			'menu_template'      => 'bitpackage:blogs/menu_blogs.tpl',
			'admin_comments_url' => KERNEL_PKG_URL.'admin/index.php?page=blogs',
		];
		$gBitSystem->registerAppMenu( $menuHash );

		require_once BLOGS_PKG_CLASS_PATH.'LibertyBlogService.php';

		$gLibertySystem->registerService(
			LIBERTY_SERVICE_BLOGS,
			BLOGS_PKG_NAME,
			[
				'module_display_function'  => 'blogs_module_display',
				'search_extra_sql_function' => 'blogs_search_extra_sql',
			],
			[
				'description' => KernelTools::tra( 'A module display service which helps hook in javascript necessary when using the center module on other pages. This is a temporary fix to address a limitation of the rendering order in BitThemes and only applies to blog content.' ),
				'required' => true,
			],
		);
	}

	$gBitSystem->registerNotifyEvent( [ "blog_post" => KernelTools::tra("An entry is posted to a blog") ] );
}
