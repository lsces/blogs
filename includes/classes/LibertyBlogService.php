<?php
/**
 * Liberty service functions for the blogs package.
 * Functions must be in Bitweaver\Liberty namespace to be resolved by LibertyContent::getServicesSql.
 *
 * @package blogs
 */
namespace Bitweaver\Liberty;

function blogs_search_extra_sql( $pObject = null, $pParamHash = null ): array {
	return [
		'select_sql' => ", blog_lc.`title` AS `blog_title`",
		'join_sql'   => " LEFT JOIN `" . BIT_DB_PREFIX . "blogs_posts_map` bpm"
		              . "  ON ( bpm.`post_content_id` = lc.`content_id` AND lc.`content_type_guid` = 'bitblogpost' )"
		              . " LEFT JOIN `" . BIT_DB_PREFIX . "liberty_content` blog_lc"
		              . "  ON ( blog_lc.`content_id` = bpm.`blog_content_id` ) ",
	];
}
