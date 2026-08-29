<?php
/**
 * @package blogs
 */

global $gBitInstaller;

$gBitInstaller->registerPackageUpgrade(
	[
		'package'     => 'blogs',
		'version'     => '5.0.1',
		'description' => 'Widen publish_date/expire_date (blog_posts) and date_added (blogs_posts_map) from I4 to I8 — I4 is a 32-bit signed integer, max value 19 January 2038, and expire_date genuinely overflows past that (confirmed hit live, not theoretical). I8 matches the convention used everywhere else in this stack (liberty_content\'s created/last_modified/event_time, etc.) and has no such limit.',
	],
	[
		[ 'QUERY' => [
			'SQL92' => [
				"ALTER TABLE blog_posts ALTER COLUMN publish_date TYPE BIGINT",
				"ALTER TABLE blog_posts ALTER COLUMN expire_date TYPE BIGINT",
				"ALTER TABLE blogs_posts_map ALTER COLUMN date_added TYPE BIGINT",
			],
		]],
	]
);
