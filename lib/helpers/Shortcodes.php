<?php

class CMDM_Shortcodes {

    public static function init() {

        add_action('init', [__CLASS__, 'add_rewrite_endpoint']);

        add_shortcode('cmdm-index', [__CLASS__, 'shortcode_index']);

        CMDM_MyDownloadsShortcode::init();
    }

    public static function add_rewrite_endpoint() {
        add_rewrite_endpoint(
            CMDM_GroupDownloadPage::getDownloadsPermalink()
            , EP_PERMALINK | EP_PAGES
        );
    }

    public static function shortcode_index($atts) {
        $atts = [];
        $displayOptionsDefaults = CMDM_Settings::getDisplayOptionsDefaults();

        $atts = CMDM_GroupDownloadPage::sanitize_array($atts, [
            'view' => ['string', CMDM_Settings::getOption(CMDM_Settings::OPTION_DEFAULT_VIEW)],
            'search' => ['bool', $displayOptionsDefaults['searchBar']],
            'header' => ['bool', $displayOptionsDefaults['header']],
            'showcategories' => ['bool', $displayOptionsDefaults['categories']],
            'tag' => ['string', null],
            'cat' => ['string', null],
            'sort' => ['string', null],
            'order' => ['string', null],
            'limit' => ['int', 10],
            'author' => ['string', null],
            'alldownloads' => ['bool', $displayOptionsDefaults['allDownloads']],
            'dashboardlinks' => ['bool', CMDM_Settings::getOption(CMDM_Settings::OPTION_INDEX_SHOW_DASHBOARD_LINKS)],
        ]);

        $queryArgs = [
            'post_type' => CMDM_GroupDownloadPage::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => $atts['limit'],
            'widget_limit' => $atts['limit'],
            'limit' => $atts['limit'],
            'category' => $atts['cat'] ?? '',
        ];

        if (!empty($atts['tag'])) {
            $queryArgs['tag'] = $atts['tag'];
        }

        if (!empty($atts['author'])) {
            if (!is_numeric($atts['author'])) {
                if ($user = get_user_by('slug', $atts['author'])) {
                    $atts['author'] = $user->ID;
                } else {
                    $atts['author'] = null;
                }
            }
            $queryArgs['author'] = $atts['author'];
        }

        if (empty($atts['sort'])) {
            $atts['sort'] = 'newest';
        }

        if (isset($_GET['cmdm_sort']) && !empty($_GET['cmdm_sort'])) {
            $atts['sort'] = sanitize_text_field($_GET['cmdm_sort']);
        }

        $order_args = CMDM_GroupDownloadPage::getQueryOrderArgs($atts['sort']);
        $queryArgs = array_merge($queryArgs, $order_args);

        self::includeCategoryCondition($atts, $queryArgs);

        $displayOptions = array_merge($displayOptionsDefaults, [
            'searchBar' => $atts['search'],
            'header' => $atts['header'],
            'categories' => $atts['showcategories'],
            'allDownloads' => $atts['alldownloads'],
            'dashboardLinks' => $atts['dashboardlinks'],
        ]);

        /* PROCESS GET PARAMS */
        $queryArgs['page'] = (int)($_GET['cmdm-page'] ?? 1);
        /**********************/

        $queryArgs['sort'] = $atts['sort'] ?? '';
        $query = CMDM_GroupDownloadPage::getDownloadsQuery($queryArgs);

        $view = $atts['view'];
        if (isset($_GET['cmdm_view']) && !empty($_GET['cmdm_view'])) {
            $view = sanitize_text_field($_GET['cmdm_view']);
        }

        $options = compact('displayOptions', 'view', 'queryArgs', 'atts');
        $options['allowAJAX'] = true;
        $options['widgetCacheId'] = md5(serialize($options));
        $_SESSION['cmdm']['widgetCache'][$options['widgetCacheId']] = $options;
        $options['query'] = $query;
        $options['currentCategory'] = self::getCurrentCategory($atts);

        CMDM_BaseController::loadScripts();
		
        $result = CMDM_BaseController::_loadView('cmdownload/widget/cmdm-index', $options);

        return '<div class="CMDM">' . $result . '</div>';
    }

    protected static function getCurrentCategory($atts) {
        if (isset($atts['cat'])) {
            if (is_numeric($atts['cat'])) $term = get_term($atts['cat'], CMDM_Category::TAXONOMY);
            else $term = get_term_by('slug', $atts['cat'], CMDM_Category::TAXONOMY);
            if (!empty($term)) return $term;
        }
    }

    protected static function includeCategoryCondition($atts, &$queryArgs) {
        if (!empty($atts['cat'])) { // there may be multiple categories separated by commas
            if (!is_array($atts['cat'])) $categories = explode(',', $atts['cat']);
            else $categories = $atts['cat'];
            $categories = array_filter($categories);
            $categoriesSlugs = [];
            foreach ($categories as $i => $cat) {
                if (!is_scalar($cat)) continue;
                if (preg_match('/^[0-9]+$/', $cat)) {
                    $category = get_term($cat, CMDM_Category::TAXONOMY);
                    $categoriesSlugs[] = $category->slug;
                    $catId = $cat;
                } else if ($category = get_term_by('slug', trim($cat), CMDM_Category::TAXONOMY)) {
                    $catId = $category->term_id;
                    $categoriesSlugs[] = $category->slug;
                } else {
                    $catId = false;
                }
                if ($catId) {
                    if (empty($queryArgs['tax_query'][0])) {
                        $queryArgs['tax_query'][0] = [
                            'taxonomy' => CMDM_Category::TAXONOMY,
                            'field' => 'term_id',
                            'terms' => [$catId],
                            'include_children' => true,
                        ];
                    } else {
                        $queryArgs['tax_query'][0]['terms'][] = $catId;
                    }
                }
            }
            $atts['cat'] = implode(',', $categoriesSlugs);
        }
    }

    public static function getQueryUrl($query, $atts = []) {
        $category = ($query->is_tax() ? $query->get_queried_object() : null);

        $page = $atts['page'] ?? 1;

        if ($category and is_scalar($category)) {
            if (is_numeric($category)) {
                $category = get_term($category, CMDM_Category::TAXONOMY);
            } else {
                $category = get_term_by('slug', $category, CMDM_Category::TAXONOMY);
            }
        }
        global $wp;
        $args = [];


        if ($page > 1) {
            $args['cmdm-page'] = $page;
        }

        $url = add_query_arg($args, trailingslashit(home_url($wp->request)));

        if (empty($search) and !empty($_GET['CMDsearch'])) {
            $search = sanitize_textarea_field($_GET['CMDsearch']);
        }

        if (!empty($search)) {
            $url = add_query_arg(urlencode_deep(['CMDsearch' => $search]), $url);
        }

        if (!empty($view)) {
            $url = add_query_arg(urlencode_deep(['view' => $view]), $url);
        }

        if (!empty($atts['view'])) {
            $url = add_query_arg(urlencode_deep(['cmdm_view' => $atts['view']]), $url);
        }

        return $url;
    }
}
