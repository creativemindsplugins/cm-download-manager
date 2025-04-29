<?php

class CMDM_Category {

	const TAXONOMY = 'cmdm_category';

	const R_TERM_ID = 'term_id';

	static $singletons = array();
	static $cache = array();

	protected $term;


	/**
	 * Get the category instance.
	 *
	 * @param mixed $category Object or term_id
	 * @return CMDM_Category
	 */
	public static function getInstance($category) {
		if ( is_array($category) AND $category['taxonomy'] == self::TAXONOMY ) $category = (object)$category;
		else if ( is_numeric($category) ) {
			$category = get_term_by('term_id', $category, self::TAXONOMY);
		}
		else if ( is_scalar($category) ) {
			$category = get_term_by('slug', $category, self::TAXONOMY);
		}
		else if ( is_object($category) ) {
			if ( $category->taxonomy != self::TAXONOMY ) return;
		}

		if ( $category AND $category->taxonomy = self::TAXONOMY ) {
			if (!isset(self::$singletons[$category->term_id])) {
				self::$singletons[$category->term_id] = new self($category);
			}
			return self::$singletons[$category->term_id];
		}

	}

	public static function getGroupedByParent() {
		$result = array();
		$categories = array_map(array(__CLASS__, 'getInstance'), get_terms(CMDM_Category::TAXONOMY, array(
            'orderby'    => 'name',
            'hide_empty' => 0,
        )));
		foreach ($categories as $category) {
			$result[$category->getParentId()][$category->getId()] = $category;
		}
		return $result;
	}

	public static function getLevel($parentId = null) {
	    $args['hide_empty'] = false;
	    if($parentId){
            $args['parent'] = $parentId;
        }
		$terms = get_terms(self::TAXONOMY, $args);
		return array_map(array(__CLASS__, 'getInstance'), $terms);
	}

	protected function __construct($term) {
		$this->term = $term;
	}

	public function getPermalink() {
		return get_term_link($this->getId(), self::TAXONOMY);
	}

	/**
	 * Returns term ID.
	 *
	 * @return number
	 */
	public function getId() {
		return intval($this->term->term_id);
	}

	public function getTermTaxonomyId() {
		return intval($this->term->term_taxonomy_id);
	}

	public function getName() {
		return $this->term->name;
	}

	public function getSlug() {
		return $this->term->slug;
	}

	public function getSubcategories() {
		return array_map(array(__CLASS__, 'getInstance'),
			get_terms(self::TAXONOMY, array(
				'orderby'    => 'name',
				'hide_empty' => 0,
				'parent' => $this->getId(),
			)
		));
	}


	public function getPostsCount($onlyVisible = false, $recursive = false) {
        return count($this->getPostsIds($onlyVisible, $recursive));
	}


	public function getPosts($onlyVisible = false, $recursive = false, $limit = 0, $page = 1, $sort = null, $tag = null) {

		return array_map(array('CMDM_GroupDownloadPage', 'getInstance'),
			$this->getPostsIds($onlyVisible, $recursive, $limit, $page, $sort, $tag));
	}


	public function getPostsIdsQuery($onlyVisible = false, $recursive = false, $limit = 0, $page = 1, $sort = null, $tag = null, $author = null, $search = null, $date_filter = []) {
		$queryArgs = array('tax_query' => array(array(
			'taxonomy' => CMDM_Category::TAXONOMY,
			'field' => 'term_id',
			'terms' => array($this->getId()),
			'include_children' => $recursive,
		)));
        return CMDM_Category::getPostsIdsQueryFunction($onlyVisible, $recursive, $limit, $page, $sort, $tag, $author, $search, $queryArgs, $date_filter);
	}

	public static function getPostsIdsQueryFunction($onlyVisible, $recursive, $limit, $page, $sort, $tag, $author, $search, $queryArgs, $date_filter = []){
		$queryArgs['post_type'] = CMDM_GroupDownloadPage::POST_TYPE;
		$queryArgs['post_status'] = 'publish';
		$queryArgs['ignore_sticky_posts'] = true;
		$queryArgs['fields'] = 'ids';
		if ($author) {
			$queryArgs['author'] = $author;
		}
		if ($search) {
			$queryArgs['s'] = $search;
		}

		if (!empty($tag)) {
			if (!is_array($tag)) $tags = explode(',', $tag);
			else $tags = $tag;
            $relation = 'and';
			$queryArgs['tax_query']['relation'] = $relation;
			foreach ($tags as $tag) {
				$queryArgs['tax_query'][] = array(
					'taxonomy' => 'post_tag',
					'field' => (is_numeric($tag) ? 'term_id' : 'slug'),
					'terms' => array($tag),
                    'operator' => 'IN'
				);
			}
		}
		if ($limit > 0) {
			$queryArgs['posts_per_page'] = $limit;
			$queryArgs['paged'] = $page;
		} else {
			$queryArgs['nopaging'] = true;
		}
		if (!empty($atts['author'])) {
			$queryArgs['author'] = $atts['author'];
		}
		if ( !empty($atts['query']) ) {
			$queryArgs['s'] = $atts['query'];
		}
		if(isset($date_filter['monthnum'])) {
			$queryArgs['monthnum'] = $date_filter['monthnum'];
		}
		if(isset($date_filter['year'])) {
			$queryArgs['year'] = $date_filter['year'];
		}
		if ( $onlyVisible ) {
			add_action('pre_get_posts', array('CMDM_GroupDownloadPage', 'filterVisibility'), PHP_INT_MAX, 1);
		}

		$query = new WP_Query($queryArgs);
		remove_action('pre_get_posts', array('CMDM_GroupDownloadPage', 'filterVisibility'), PHP_INT_MAX);

		if (empty($sort)) {
			$sort = 'newest';
		}
		CMDM_GroupDownloadPage::registerCustomOrder($query, $sort);
		return $query;
	}

	public function getPostsIds($onlyVisible = false, $recursive = false, $limit = 0, $page = 1, $sort = null, $tag = null, $author = null) {
		$query = $this->getPostsIdsQuery($onlyVisible, $recursive, $limit, $page, $sort, $tag, $author);
		return $query->posts;
	}

	public function isVisible($userId = null) {
		$result = false;
		
		if ( is_null( $userId ) ) {
			$userId = get_current_user_id();
		}

		if (user_can($userId, 'manage_options')) {
			return true;
		}

		if ( $this->isPrivate() ) {
			if ( $userId === null || $userId === 0 ) {
				return false;
			}
			return  $this->isOwner($userId);
		}

		return true;
	}

	public function isPrivate() {
		$ownerId = $this->getOwner();
		return ( !empty($ownerId) ) ? true : false;
	}

	public function getOwner() {
		$ownerId = get_term_meta($this->getId(), '_cmdm_owner_id', true);
		return $ownerId;
	}

	public function isOwner( $userId ) {
		$is_owner = false;
		if ( $userId != null && $userId !== 0 ) {
			$ownerId = $this->getOwner();
			if ( (int) $userId == (int) $ownerId ) {
				$is_owner = true;
			}
		}
		return $is_owner;
	}

	public function getParentId() {
		return intval($this->term->parent);
	}

	public function getParentInstance() {
		if ($parentId = $this->getParentId()) {
			return self::getInstance($parentId);
		}
	}

	function canUpload($userId = null) {
		return true;
	}

}
