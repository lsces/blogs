<?php
/**
 * @version $Header$
 * @version  $Revision$
 * @package blogs
 */

/**
 * required setup
 */
namespace Bitweaver\Blogs;

use Bitweaver\BitBase;
use Bitweaver\Liberty\LibertyContent;
use Bitweaver\Liberty\LibertyMime;

define( 'BITBLOG_CONTENT_TYPE_GUID', 'bitblog' );

/**
 * @package blogs
 */
class BitBlog extends LibertyMime {
	public $mBlogId;

	public function __construct( $pBlogId = 0, $pContentId = 0 ) {
		if ( is_array($pBlogId) ) {
			$firstEntry = current($pBlogId);
			$this->mBlogId = $this->verifyId( $firstEntry['blog_id'] );
			$this->mContentId = $this->verifyId( $firstEntry['blog_content_id'] );
		} else {
			$this->mBlogId = $this->verifyId( $pBlogId ) ? $pBlogId : 0;
		}

		parent::__construct();
		$this->registerContentType( BITBLOG_CONTENT_TYPE_GUID, [
			'content_type_guid' => BITBLOG_CONTENT_TYPE_GUID,
			'content_name' => 'Blog',
			'handler_class' => 'BitBlog',
			'handler_package' => 'blogs',
			'handler_file' => 'BitBlog.php',
			'maintainer_url' => 'https://www.bitweaver.org',
		] );
		$this->mContentId = $pContentId;
		$this->mContentTypeGuid = BITBLOG_CONTENT_TYPE_GUID;

		// Permission setup
		$this->mViewContentPerm  = 'p_blogs_view';
		$this->mUpdateContentPerm  = 'p_blogs_update';
		$this->mCreateContentPerm  = 'p_blogs_create';
		$this->mAdminContentPerm = 'p_blogs_admin';
	}

	public function get_num_user_blogs($user_id) {
		$ret = null;
		if ($user_id) {
			$sql = "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."blogs` WHERE `user_id` = ?";
			$ret = $this->mDb->getOne($sql, [ $user_id ]);
		}
		return $ret;
	}

	public static function getDisplayUrlFromHash( &$pParamHash ) {
		global $gBitSystem;
		$ret = null;

		if ( BitBase::verifyIdParameter( $pParamHash, 'blog_id' ) ) {
			if( $gBitSystem->isFeatureActive( 'pretty_urls_extended' ) ) {
				$ret = BLOGS_PKG_URL.'view/'.$pParamHash['blog_id'];
			} elseif( $gBitSystem->isFeatureActive( 'pretty_urls' ) ) {
				$ret = BLOGS_PKG_URL.$pParamHash['blog_id'];
			} else {
				$ret = BLOGS_PKG_URL.'view.php?blog_id='.$pParamHash['blog_id'];
			}
		} else {
			$ret = parent::getDisplayUrlFromHash( $pParamHash );
		}
		return $ret;
	}

	public function getDisplayUrl() {
		$ret = null;
		if( $this->isValid() ) {
			$hash = [ 'blog_id' => $this->mBlogId ];
			$ret = self::getDisplayUrlFromHash( $hash );
		}
		return $ret;
	}

	/**
	* Check if there is an article loaded
	* @return bool true on success, false on failure
	* @access public
	**/
	public function isValid() {
		return $this->verifyId( $this->mBlogId ) && $this->verifyId( $this->mContentId );
	}

	public function load() {
		if ( $this->getBlog( $this->mBlogId, $this->mContentId ) ) {
			$this->mContentId = $this->getField( 'content_id' );
			$this->mBlogId = $this->getField('blog_id');
		}
		return true;
	}

	/*shared*/
	public function getBlog( $pBlogId, $pContentId = null ) {
		global $gBitSystem;
		$ret = null;

		$lookupId = !empty( $pBlogId ) ? $pBlogId : $pContentId;
		$lookupColumn = !empty( $pBlogId ) ? 'blog_id' : 'content_id';

		$bindVars = [ (int) $lookupId ];
		$selectSql = ''; $joinSql = ''; $whereSql = '';
		$this->getServicesSql( 'content_load_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

		if ( BitBase::verifyId( $lookupId ) ) {
			$query = "
				SELECT b.*, lc.*, lch.`hits`, uu.`login`, uu.`login`, uu.`user_id`, uu.`real_name`,
					lfa.`file_name` as `avatar_file_name`, lfa.`mime_type` AS `avatar_mime_type`, laa.`attachment_id` AS `avatar_attachment_id`,
					lfp.`file_name` AS `image_file_name`, lfp.`mime_type` AS `image_mime_type`, lap.`attachment_id` AS `image_attachment_id`
					$selectSql
				FROM `".BIT_DB_PREFIX."blogs` b
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lc.`content_id` = b.`content_id`)
					INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (uu.`user_id` = lc.`user_id`)
					$joinSql
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON (lc.`content_id` = lch.`content_id`)
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments`	laa ON (uu.`user_id` = laa.`user_id` AND laa.`attachment_id` = uu.`avatar_attachment_id`)
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files`	    lfa ON lfa.`file_id`		   = laa.`foreign_id`
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments`  lap ON lap.`content_id`        = lc.`content_id` AND lap.`is_primary` = 'y'
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files`        lfp ON lfp.`file_id`           = lap.`foreign_id`
				WHERE b.`$lookupColumn`= ? $whereSql";

			if( $this->mInfo = $this->mDb->getRow($query,$bindVars) ) {
				$this->mContentId = $this->getField( 'content_id' );
				$this->mBlogId = $this->getField('blog_id');
				foreach( [ 'avatar', 'image' ] as $img ) {
					$this->mInfo[$img] = \Bitweaver\Liberty\liberty_fetch_thumbnails( [
						'source_file' => $this->getSourceFile( [ 'user_id' => $this->getField( 'user_id' ), 'package' => \Bitweaver\Liberty\liberty_mime_get_storage_sub_dir_name( [ 'type' => $this->getField( "{$img}_mime_type" ), 'name' => $this->getField( "{$img}_file_name" ) ] ), 'file_name' => basename( $this->mInfo["{$img}_file_name"] ?? '' ), 'sub_dir' => $this->getField( "{$img}_attachment_id" ) ] ),
					]);
				}
				parent::load();
				$this->mInfo['postscant'] = $this->getPostsCount( $this->mContentId );
			}
		}
		return count( $this->mInfo ) != 0;
	}

	public function verify( array &$pParamHash ): bool {
		global $gBitUser;

		$pParamHash['blog_store']['max_posts'] = !empty( $pParamHash['max_posts'] ) && is_numeric( $pParamHash['max_posts'] ) ? $pParamHash['max_posts'] : null;
		$pParamHash['blog_store']['use_title'] = isset( $pParamHash['use_title'] ) ? 'y' : 'n';
		$pParamHash['blog_store']['allow_comments'] = isset( $pParamHash['allow_comments'] ) ? 'y' : 'n';
		$pParamHash['blog_store']['use_find'] = isset( $pParamHash['use_find'] ) ? 'y' : 'n';

		// if we have an error we get them all by checking parent classes for additional errors
		if( count( $this->mErrors ) > 0 ){
			parent::verify( $pParamHash );
		}

		return count( $this->mErrors ) == 0;
	}

	public function store( array &$pParamHash ): bool {
		global $gBitSystem;
		$this->StartTrans();
		if( $this->verify( $pParamHash ) && parent::store( $pParamHash ) ) {
			$table = BIT_DB_PREFIX."blogs";
			if( $this->isValid() ) {
				$result = $this->mDb->associateUpdate( $table, $pParamHash['blog_store'], [ "blog_id" => $pParamHash['blog_id'] ] );
			} else {
				// DEPRECATED - this looks stupid -wjames5
				//$pParamHash['blog_store']['posts'] = 0;
				$pParamHash['blog_store']['content_id'] = $this->mContentId;
					// if pParamHash['blog_id'] is set, someone is requesting a particular blog_id. Use with caution!
					$pParamHash['blog_store']['blog_id'] = isset( $pParamHash['blog_id'] ) && is_numeric( $pParamHash['blog_id'] )
						? $pParamHash['blog_id'] : $this->mDb->GenID( 'blogs_blog_id_seq' );

				$this->mBlogId = $pParamHash['blog_store']['blog_id'];
				$result = $this->mDb->associateInsert( $table, $pParamHash['blog_store'] );
			}
			$this->CompleteTrans();
		}
		return count( $this->mErrors ) == 0;
	}

	public function expunge(): bool {
		if ( $this->isValid() ) {
			$this->StartTrans();

			// remove all references in blogs_posts_map where post_content_id = content_id
			$query_map = "DELETE FROM `".BIT_DB_PREFIX."blogs_posts_map` WHERE `blog_content_id` = ?";
			$result = $this->mDb->query( $query_map, [ $this->mContentId ] );

			$query = "DELETE from `".BIT_DB_PREFIX."blogs` where `content_id`=?";
			$result = $this->mDb->query( $query, [ (int) $this->mContentId ] );

			if( parent::expunge() ) {
				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
			}
			$this->CompleteTrans();
		}
		return true;
	}

	public function getPost( $pListHash=[] ) {
		$ret = null;
		$bindVars = [];

		$blogId = !empty( $pListHash['blog_id'] ) ? $pListHash['blog_id'] : $this->mBlogId;

		if ( BitBase::verifyId( $blogId ) ) {
			$this->prepGetList( $pListHash );
			$sql = "SELECT bp.`post_id`
					FROM `".BIT_DB_PREFIX."blog_posts` bp
						INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lc.`content_id`=bp.`content_id`)
						INNER JOIN `".BIT_DB_PREFIX."blogs_posts_map` bpm ON (bp.`content_id`=bpm.`post_content_id`)
						INNER JOIN `".BIT_DB_PREFIX."blogs` b on (bpm.`blog_content_id`=b.`content_id`)
					WHERE b.`blog_id` = ? ORDER BY ".$this->mDb->convertSortMode( $pListHash['sort_mode'] );
			if( $postId = $this->mDb->getOne($sql, [ $blogId ] ) ) {
				$blogPost = new BitBlogPost( $postId );
				$blogPost->load( null, $pListHash );
				$ret = $blogPost;
			}
		}
		return $ret;
	}

	// BLOG METHODS ////
	public function getList( &$pParamHash ) {
		global $gBitSystem;

		LibertyContent::prepGetList( $pParamHash );

		$selectSql = ''; $joinSql = ''; $whereSql = '';
		$bindVars = [];
//		array_push( $bindVars, $this->mContentTypeGuid );
		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

		// You can use a title or an array of blog_id
		if (!empty($pParamHash['find'])) {
			if (is_array($pParamHash['find'])) {
				$whereSql .= " AND b.`blog_id` IN ( ".implode( ',',array_fill( 0,count( $pParamHash['find'] ),'?' ) ).") ";
				$bindVars = array_merge($bindVars, $pParamHash['find']);
			}
			else {
				$findesc = '%' . strtoupper( $pParamHash['find'] ) . '%';
				$whereSql = " AND (UPPER(lc.`title`) like ? or UPPER(lc.`data`) like ?) ";
				$bindVars= [ $findesc, $findesc ];
			}
		}
		if( @$this->verifyId( $pParamHash['user_id'] ?? 0 ) ) {
			$whereSql .= " AND uu.`user_id` = ? ";
			$bindVars[] = $pParamHash['user_id'];
		}

		$this->getServicesSql( 'content_user_collection_function', $selectSql, $joinSql, $whereSql, $bindVars, $this, $pListHash );

		if( !empty( $pParamHash['is_active'] ) ) {
			$whereSql .= " AND b.`activity` IS NOT null";
		}

		if( !empty( $pParamHash['is_hit'] ) ) {
			$whereSql .= " AND lch.`hits` IS NOT null";
		}

		if( !empty( $pParamHash['content_perm_name'] ) ) {
			$this->getContentPermissionsSql( $pParamHash['content_perm_name'], $selectSql, $joinSql, $whereSql, $bindVars );
		}

		if( !empty( $whereSql ) ) {
			$whereSql = preg_replace( '/^[\s]*AND/', ' WHERE ', $whereSql );
		}

		$ret = [];

		// Return a data array, even if empty
		$pParamHash["data"] = [];

		# Get count of total number of items available
		$query_cant = "
			SELECT COUNT(b.`blog_id`)
				FROM `".BIT_DB_PREFIX."blogs` b
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lc.`content_id` = b.`content_id`)
				INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (uu.`user_id` = lc.`user_id`)
				$joinSql
			$whereSql";
		$pParamHash["cant"] = $this->mDb->getOne( $query_cant, $bindVars );

		# Check for offset out of range
		if ( $pParamHash['offset'] < 0 ) {
			$pParamHash['offset'] = 0;
			}
		elseif ( $pParamHash['offset']	> $pParamHash["cant"] ) {
			$lastPageNumber = ceil ( $pParamHash["cant"] / $pParamHash['max_records'] ) - 1;
			$pParamHash['offset'] = $pParamHash['max_records'] * $lastPageNumber;
			}

		$query = "
			SELECT b.`content_id` AS `hash_key`,
				b.`blog_id`, b.`is_public`, b.`max_posts`, b.`activity`, b.`use_find`, b.`use_title`,
				b.`add_date`, b.`add_poster`, b.`allow_comments`,
				uu.`login`,	uu.`real_name`, lc.*, lch.hits $selectSql
			FROM `".BIT_DB_PREFIX."blogs` b
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lc.`content_id` = b.`content_id`)
				INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (uu.`user_id` = lc.`user_id`)
				$joinSql
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON (lc.`content_id` = lch.`content_id`)
			$whereSql order by ".$this->mDb->convertSortmode($pParamHash['sort_mode']);

		$result = $this->mDb->query( $query, $bindVars, $pParamHash['max_records'], $pParamHash['offset'] );
		$ret = [];
		while ($res = $result->fetchRow()) {
			$blogContentId = $res['content_id'];
			$ret[$blogContentId] = $res;
			$ret[$blogContentId]['blog_url'] = $this->getDisplayUrlFromHash( $res );
			//get count of post in each blog
			$ret[$blogContentId]['postscant'] = $this->getPostsCount( $res['content_id'] );
			// deal with the parsing
			$parseHash['format_guid']   = $res['format_guid'];
			$parseHash['content_id']    = $res['content_id'];
			$parseHash['data'] 	= $res['data'];
			$ret[$blogContentId]['parsed'] = self::parseDataHash( $parseHash );
		}

		LibertyContent::postGetList( $pParamHash );

		return $ret;
	}

	public function getPostsCount($pBlogContentId){
		global $gBitSystem;
		$ret = null;
		if( @$this->verifyId( $pBlogContentId ) ) {
			$whereSql = 'bpm.`blog_content_id` = ?';
			$bindVars = [ (int) $pBlogContentId ];
			BitBlogPost::getDateRestrictions([], $whereSql, $bindVars);
			$query = "SELECT COUNT(*)
				FROM `".BIT_DB_PREFIX."blogs_posts_map` bpm
				INNER JOIN `".BIT_DB_PREFIX."blog_posts` bp ON (bpm.`post_content_id`=bp.`content_id`)
				WHERE $whereSql";

			$ret = $this->mDb->getOne( $query, $bindVars );
		} else {
			$this->mErrors['content_id'] = "Invalid blog content id.";
		}
		return $ret;
	}

	//This doesnt even appear to be used in blogs before this refactoring -wjames5
	public function viewerCanPostIntoBlog() {
		global $gBitUser;
		return $this->getField('user_id') == $gBitUser->mUserId || $gBitUser->isAdmin() || $this->getField('is_public') == 'y' ;
	}

	public function hasPostPermission() {
		$ret = false;
		if( $this->isValid() ) {
			// for now just check edit permission, however eventually we'll want to separate this notion so blog editors and posters can be distinguished
			$ret = $this->hasUpdatePermission();
		}
		return $ret;
	}

	public function viewerHasPermission($pPermName = null) {
		global $gBitUser;
		$ret = false;
		if ($gBitUser->mUserId && $pPermName) {
			$ret = $gBitUser->object_has_permission( $gBitUser->mUserId, $this->mInfo['blog_id'], $this->getContentType(), $pPermName );
		}
		return $ret;
	}

	public function getViewTemplate( $pAction ){
		$ret = null;
		switch ( $pAction ){
			case "view":
				$ret = "bitpackage:blogs/center_".$pAction."_blog_posts.tpl";
				break;
			case "list":
				$ret = "bitpackage:liberty/center_".$pAction."_generic.tpl";
				break;
		}
		return $ret;
	}

	/**
	 * getContentStatus
	 *
	 * @access public
	 * @return array|null of content_status_id, content_status_names the current
	 * user can use on this content.
	 */
	public function getAvailableContentStatuses( $pUserMinimum=-100, $pUserMaximum=100 ) {
		global $gBitUser;
		$ret = null;
		// return null for all but admins
		if( $gBitUser->hasPermission( 'p_liberty_edit_all_status' )) {
			$ret = LibertyMime::getAvailableContentStatuses();
		}
		return $ret;
	}
}

function blogs_module_display(&$pParamHash){
	global $gBitThemes, $gBitSmarty, $gBitSystem;
	if( $gBitThemes->isModuleLoaded( 'bitpackage:blogs/center_list_blog_posts.tpl', 'c' ) && $gBitSystem->isFeatureActive( 'blog_ajax_more' ) && $gBitThemes->isJavascriptEnabled() ) {
		$gBitSmarty->assign( 'ajax_more', true );
		$gBitThemes->loadAjax( 'mochikit', [ 'Iter.js', 'DOM.js', 'Style.js', 'Color.js', 'Position.js', 'Visual.js' ]);
	}
}