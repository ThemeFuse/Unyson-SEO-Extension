<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * SEO Sitemap extension
 * Is sa a sub-extension of the SEO extension.
 */
class FW_Extension_Seo_Sitemap extends FW_Extension {

	/**
	 * Contains an array of all available search engines
	 * @var array
	 */
	private $serach_engies = array();

	/**
	 * Contains the list of the allowed custom post types
	 * @var array
	 */
	private $post_types = array();

	/**
	 * Contains the list of the allowed taxonomies
	 * @var array
	 */
	private $taxonomies = array();

	/**
	 * @var string
	 */
	private $sitemap_key = 'fw-seo-sitemap';

	/**
	 * @var string
	 */
	private $xsl_key = 'fw-seo-sitemap-xsl';

	/**
	 * @var string
	 */
	private $sitemap_pagination_key = 'fw-seo-sitemap-pagination';

	/**
	 * @var string
	 */
	private $sitemap_prefix = 'sitemap-';

	/**
	 * @var array
	 */
	private $url_settings = array(
		'home'       => array(
			'priority'  => 1,
			'frequency' => 'daily',
		),
		'posts'      => array(
			'priority'  => 0.6,
			'frequency' => 'daily',
			'type'      => array(
				'attachment' => array(
					'priority'  => 0.3,
					'frequency' => 'daily',
				)
			)
		),
		'taxonomies' => array(
			'priority'  => 0.4,
			'frequency' => 'weekly',
			'type'      => array(
				'post_tag' => array(
					'priority'  => 0.3,
					'frequency' => 'weekly',
				)
			)
		)
	);

	private $links_per_page = 20000;

	private $index_name = 'index';

	/**
	 * @internal
	 */
	public function _init() {
		$config             = $this->get_config( 'url_settings' );
		$this->url_settings = fw_ext_seo_sitemaps_array_merge_recursive(
			$this->url_settings,
			$config
		);

		$this->add_action();

		if ( is_admin() ) {
			$this->add_admin_actions();
			$this->add_admin_filters();
		}
	}

	/**
	 * Returns an array with allowed custom post types for this extension
	 * @return array
	 */
	public function get_allowed_post_types() {
		return $this->post_types;
	}

	/**
	 * Retuns an array with allows taxonomies for this extension
	 * @return array
	 */
	public function get_allowed_taxonomies() {
		return $this->taxonomies;
	}

	/**
	 * @return array
	 */
	public function get_search_engines() {
		return $this->serach_engies;
	}

	/**
	 * Returns an array with allowed custom post types for this extension, this doesn't include post types that was disabled from admin area
	 * @return array
	 */
	public function get_workable_custom_post_types() {
		$custom_post_types = array();
		$custom_posts      = $this->post_types;

		foreach ( $custom_posts as $custom_post ) {
			$id       = $this->get_name() . '-exclude-custom-post-' . $custom_post;
			$excluded = $this->get_settings_option( $id );

			if ( $excluded === true ) {
				continue;
			}

			array_push( $custom_post_types, $custom_post );
		}

		return $custom_post_types;
	}

	/**
	 * Returns an array with allowed taxonomies for this extension, this doesn't include post types that was disabled from admin area
	 * @return array
	 */
	public function get_workable_taxonomies() {
		$taxonomies_types = array();
		$taxonomies       = $this->taxonomies;

		foreach ( $taxonomies as $taxonomy ) {
			$id      = $this->get_name() . '-exclude-taxonomy-' . $taxonomy;
			$allowed = $this->get_settings_option( $id );

			if ( $allowed === true ) {
				continue;
			}

			array_push( $taxonomies_types, $taxonomy );
		}

		return $taxonomies_types;
	}

	/**
	 * Get sitemap.xml file URI
	 * @return string
	 */
	public function get_sitemap_uri() {
		return site_url( '/' ) . $this->sitemap_prefix . $this->index_name . '.xml';
	}

	/**
	 * @param string $name
	 * @param int $page
	 *
	 * @return string
	 */
	public function render( $name, $page ) {
		$sitemaps = $this->get_sitemaps();
		if ( ! $name || ! in_array( $name, $sitemaps ) ) {
			return '';
		}

		$sitemap = $this->build_sitemap( $name, $page );
		if ( empty( $sitemap ) ) {
			return '';
		}

		if ( $name == $this->index_name ) {
			return $this->render_view( 'index-sitemap', array( 'sitemaps' => $sitemap ) );
		}

		return $this->render_view( 'sitemap', array( 'sitemaps' => $sitemap ) );
	}

	/**
	 * Returns the xsl file url
	 *
	 * @return string
	 */
	public function xsl_url() {
		return site_url() . '/sitemap.xsl';
	}

	/**
	 * Returns the xsl file url for the xml index
	 *
	 * @return string
	 */
	public function index_xsl_url() {
		return site_url() . '/sitemap-index.xsl';
	}

	/**
	 * Pings to the search engines the presence of the sitemap
	 */
	public function ping_to_search_engines() {
		if ( ! (int) get_option( 'blog_public' ) ) {
			return;
		}

		$last_updated = (int) $this->get_db_data( 'update' );

		if ( $last_updated && ( ( $last_updated + 7200 ) > time() ) ) {
			return;
		}

		$search_engines = $this->get_config( 'search_engines' );

		if ( empty( $search_engines ) ) {
			return;
		}

		foreach ( $search_engines as $search_engine ) {
			if ( ! isset( $this->serach_engies[ $search_engine ] ) ) {
				continue;
			}

			wp_remote_post( $this->serach_engies[ $search_engine ]['url'] . $this->get_sitemap_uri() );
		}
		$this->set_db_data( 'update', time() );
	}

	public function config_custom_posts() {
		$custom_posts   = get_post_types( array( 'public' => true ) );
		$excluded_types = $this->get_config( 'excluded_post_types' );

		unset( $custom_posts['nav_menu_item'] );
		unset( $custom_posts['revision'] );

		foreach ( $excluded_types as $type ) {
			if ( isset( $custom_posts[ $type ] ) ) {
				unset( $custom_posts[ $type ] );
			}
		}

		return $custom_posts;
	}

	public function config_taxonomies() {
		$taxonomies = get_taxonomies();

		$excluded_types = $this->get_config( 'excluded_taxonomies' );

		unset( $taxonomies['nav_menu'] );
		unset( $taxonomies['link_category'] );
		unset( $taxonomies['post_format'] );

		foreach ( $excluded_types as $type ) {
			if ( isset( $taxonomies[ $type ] ) ) {
				unset( $taxonomies[ $type ] );
			}
		}

		return $taxonomies;
	}

	/**
	 * @internal
	 **/
	public function _action_add_rewrite_rules() {
		add_rewrite_tag( '%' . $this->sitemap_key . '%', '([^&]+)' );
		add_rewrite_tag( '%' . $this->sitemap_pagination_key . '%', '([^&]+)' );
		add_rewrite_tag( '%' . $this->xsl_key . '%', '([^&]+)' );
		add_rewrite_rule(
			'^sitemap\.xml?',
			'index.php?' . $this->sitemap_key . '=' . $this->index_name, 'top'
		);
		add_rewrite_rule(
			'^(sitemap(-index){0,1})\.xsl?',
			'index.php?' . $this->xsl_key . '=$matches[1]', 'top'
		);
		foreach ( $this->get_sitemaps() as $sitemap ) {
			add_rewrite_rule(
				'^' . $this->sitemap_prefix . '(' . $sitemap . ')' . '(-){0,1}([0-9]*){0,1}\.xml?',
				'index.php?' . $this->sitemap_key . '=$matches[1]'
				. '&' . $this->sitemap_pagination_key . '=$matches[3]',
				'top'
			);
		}

	}

	/**
	 * @internal
	 **/
	public function _action_init() {
		$this->define_search_engines();
		$this->set_custom_posts();
		$this->set_taxonomies();
	}

	/**
	 * @internal
	 **/
	public function _action_load_sitemap() {
		$name = get_query_var( $this->sitemap_key );
		$page = get_query_var( $this->sitemap_pagination_key );

		if ( ! $name ) {
			return;
		}

		$return = $this->render( $name, $page );
		if ( empty( $return ) ) {
			return;
		}

		$this->headers();
		echo $return;
		exit;
	}

	/**
	 * @internal
	 **/
	public function _action_load_xsl() {
		if (
			!is_admin()
			&&
			in_array( $name = get_query_var( $this->xsl_key ), array( 'sitemap', 'sitemap-index' ) )
		) {
			$this->render_view(
				$name == 'sitemap' ?  'sitemap-style' : 'index-sitemap-style',
				array(),
				false
			);
			exit;
		}
	}

	/**
	 * @internal
	 */
	public function _action_admin_add_static() {
		wp_enqueue_style(
			'fw-ext-' . $this->get_name() . '-admin-style',
			$this->get_uri( '/static/css/admin-style.css' ),
			array(),
			fw()->theme->manifest->get_version()
		);

		wp_enqueue_script(
			'fw-ext-' . $this->get_name() . '-admin-scripts',
			$this->get_uri( '/static/js/admin-scripts.js' ),
			array( 'jquery' ),
			fw()->theme->manifest->get_version(),
			true
		);
	}

	/**
	 * @param int $post_id
	 *
	 * @param WP_Post $post
	 *
	 * @internal
	 **/
	public function _action_admin_ping_to_search_engines( $post_id, $post ) {
		if ( in_array( $post->post_type, $this->post_types ) ) {
			$this->ping_to_search_engines();
		}
	}

	/**
	 * @internal
	 *
	 * Adds the extension settings tab in Framework in SEO extension
	 *
	 * @param $seo_options , holds the general options from extension config file
	 *
	 * @return array
	 * @internal
	 */
	public function _filter_admin_set_framework_sitemap_tab( $seo_options ) {
		$sitemap_options = fw_ext_seo_sitemap_get_settings_options();

		if ( is_array( $sitemap_options ) && ! empty( $sitemap_options ) ) {
			return array_merge( $seo_options, $sitemap_options );
		}

		return $seo_options;
	}

	private function headers() {
		header( 'Content-Type: application/xml; charset=utf-8' );
	}

	private function get_settings_option( $id ) {
		return fw_get_db_ext_settings_option( $this->get_parent()->get_name(), $id );
	}

	private function add_action() {
		add_action( 'init', array( $this, '_action_init' ), 10 );
		add_action( 'init', array( $this, '_action_add_rewrite_rules' ), 999 );
		add_action( 'wp', array( $this, '_action_load_sitemap' ) );
		add_action( 'wp', array( $this, '_action_load_xsl' ) );
	}

	private function add_admin_actions() {

		add_action( 'save_post', array( $this, '_action_admin_ping_to_search_engines' ), 10, 2 );

		if ( fw_current_screen_match( array(
			'only' => array( 'id' => 'toplevel_page_fw-extensions' )
		) ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, '_action_admin_add_static' ) );
		}
	}

	private function add_admin_filters() {
		add_filter( 'fw_ext_seo_settings_options', array( $this, '_filter_admin_set_framework_sitemap_tab' ) );
	}

	private function build_sitemap( $name, $page = 0 ) {
		$page = (int) $page;
		$page = -- $page > - 1 ? $page : 0;

		if ( $name == $this->index_name ) {
			return $this->build_index();
		}

		if ( $this->is_post( $name ) ) {
			return $this->build_posts( $name, $page );
		}

		if ( $this->is_tax( $name ) ) {
			return $this->build_taxes( $name );
		}

		return array();
	}

	protected function build_index() {
		$sitemaps = array();
		foreach ( $this->count_posts() as $post ) {
			$sitemaps[] = array(
				'url' => $this->create_url( $post )
			);
		}

		foreach ( $this->count_taxonomies() as $tax ) {
			$sitemaps[] = array(
				'url' => $this->create_url( $tax )
			);
		}

		return $sitemaps;
	}

	protected function build_posts( $name, $page ) {
		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;

		$posts = $wpdb->get_results(
			"SELECT $wpdb->posts.ID ID, $wpdb->posts.post_modified_gmt modified " .
			"FROM $wpdb->posts " .
			"WHERE $wpdb->posts.post_type = '$name' " .
			"AND $wpdb->posts.post_status = 'publish' " .
			"LIMIT $this->links_per_page OFFSET " . ( $this->links_per_page * $page )
			,
			ARRAY_A
		);

		foreach ( $posts as &$post ) {
			$post['url']       = get_permalink( $post['ID'] );
			$post['modified']  = date(
				apply_filters( 'fw_ext_seo_sitemap_date_format', 'Y-m-d' ),
				strtotime( $post['modified'] )
			);
			$post['frequency'] = $this->post_type_frequency( $name );

			$post['priority'] = $this->post_type_priority( $name );
			unset( $post['ID'] );
		}

		if ( $name == 'page' ) {
			array_unshift( $posts, array(
				'url'       => site_url(),
				'priority'  => $this->url_settings['home']['priority'],
				'frequency' => $this->url_settings['home']['frequency'],
			) );
		}

		return $posts;
	}

	protected function build_taxes( $taxonomy ) {
		global $wpdb;

		$items = array();
		$terms = get_terms( $taxonomy, array(
			'hide_empty'   => true,
			'hierarchical' => false
		) );

		foreach ( $terms as $term ) {
			$item              = array();
			$sql               = $wpdb->prepare( "SELECT MAX(p.post_modified_gmt) AS modified
					FROM	$wpdb->posts AS p
					INNER JOIN $wpdb->term_relationships AS term_rel
					ON		term_rel.object_id = p.ID
					INNER JOIN $wpdb->term_taxonomy AS term_tax
					ON		term_tax.term_taxonomy_id = term_rel.term_taxonomy_id
					AND		term_tax.taxonomy = '%s'
					AND		term_tax.term_id = %d
					WHERE	p.post_status IN ('publish','inherit')", $taxonomy, $term->term_id );
			$item['modified']  = date( apply_filters( 'fw_ext_seo_sitemap_date_format', 'Y-m-d' ),
				strtotime( $wpdb->get_var( $sql ) ) );
			$item['url']       = get_term_link( $term, $taxonomy );
			$item['priority']  = $this->taxonomy_priority( $taxonomy );
			$item['frequency'] = $this->taxonomy_frequency( $taxonomy );

			unset( $item['id'] );

			$items[] = $item;
		}

		return $items;
	}

	protected function count_posts() {
		$counts = array();

		foreach ( $this->post_types as $post_type ) {
			$post_count = wp_count_posts( $post_type );
			$count      = (int) $post_count->publish;

			if ( $count == 0 ) {
				continue;
			}

			$ratio = $count / $this->links_per_page;

			if ( $ratio <= 1 ) {
				$counts[] = $post_type;
				continue;
			}

			if ( $ratio < round( $ratio ) ) {
				$ratio ++;
			}

			for ( $i = 0; $i < $ratio; $i ++ ) {
				$counts[] = $post_type . '-' . ( $i + 1 );
			}
			unset( $i );
		}

		return $counts;
	}

	protected function post_type_frequency( $name ) {
		return (
			isset( $this->url_settings['posts']['type'][ $name ] )
			&&
			isset( $this->url_settings['posts']['type'][ $name ]['frequency'] )
		)
			? $this->url_settings['posts']['type'][ $name ]['frequency']
			: $this->url_settings['posts']['frequency'];
	}

	protected function post_type_priority( $name ) {
		return (
			isset( $this->url_settings['posts']['type'][ $name ] )
			&&
			isset( $this->url_settings['posts']['type'][ $name ]['priority'] )
		)
			? $this->url_settings['posts']['type'][ $name ]['priority']
			: $this->url_settings['posts']['priority'];
	}

	protected function taxonomy_frequency( $name ) {
		return (
			isset( $this->url_settings['taxonomies']['type'][ $name ] )
			&&
			isset( $this->url_settings['taxonomies']['type'][ $name ]['frequency'] )
		)
			? $this->url_settings['taxonomies']['type'][ $name ]['frequency']
			: $this->url_settings['taxonomies']['frequency'];
	}

	protected function taxonomy_priority( $name ) {
		return (
			isset( $this->url_settings['taxonomies']['type'][ $name ] )
			&&
			isset( $this->url_settings['taxonomies']['type'][ $name ]['priority'] )
		)
			? $this->url_settings['taxonomies']['type'][ $name ]['priority']
			: $this->url_settings['taxonomies']['priority'];
	}

	protected function count_taxonomies() {
		$taxes = $this->get_allowed_taxonomies();

		foreach ( $taxes as $key => $tax ) {
			if ( ! wp_count_terms( $tax, array( 'hide_empty' => true ) ) ) {
				unset ( $taxes[ $key ] );
			}
		}

		return $taxes;
	}

	protected function create_url( $suffix ) {
		return site_url( '/' ) . $this->sitemap_prefix . $suffix . '.xml';
	}

	private function define_search_engines() {
		$this->serach_engies = array(
			'google' => array(
				'name' => __( 'Google', 'fw' ),
				'url'  => 'http://www.google.com/webmasters/tools/ping?sitemap='
			),
			'bing'   => array(
				'name' => __( 'Bing', 'fw' ),
				'url'  => 'http://www.bing.com/webmaster/ping.aspx?sitemap='
			)
		);
	}

	/**
	 * Defines the allowed custom post types for this extension
	 */
	private function set_custom_posts() {
		$custom_posts = $this->config_custom_posts();

		foreach ( $custom_posts as $type ) {
			if ( $this->get_settings_option( $this->get_name() . '-exclude-custom-post-' . $type ) ) {
				unset( $custom_posts[ $type ] );
			}
		}

		$this->post_types = $custom_posts;
	}

	/**
	 * Defines the allowed taxonomies for this extension
	 */
	private function set_taxonomies() {
		$taxonomies = $this->config_taxonomies();
		foreach ( $taxonomies as $type ) {
			if ( $this->get_settings_option( $this->get_name() . '-exclude-taxonomy-' . $type ) ) {
				unset( $taxonomies[ $type ] );
			}
		}

		$this->taxonomies = $taxonomies;
	}

	private function get_sitemaps() {
		return array_merge(
			array( $this->index_name => $this->index_name ),
			$this->get_allowed_post_types(),
			$this->get_allowed_taxonomies()
		);
	}

	private function is_post( $name ) {
		return isset( $this->post_types[ $name ] );
	}

	private function is_tax( $name ) {
		return isset( $this->taxonomies[ $name ] );
	}

	public function is_excluded_post_type( $post_type ) {
		return in_array( $post_type, $this->post_types ) && $this->get_settings_option(
			$this->get_name() .
			'exclude-custom-post-' . $post_type
		);
	}

	public function is_excluded_tax( $tax ) {
		return in_array( $tax, $this->taxonomies ) && $this->get_settings_option(
			$this->get_name() .
			'exclude-taxonomy-' . $tax
		);
	}

	/**
	 * Deprecated
	 */

	/**
	 * Update sitemap.xml file
	 *
	 * @deprecated since version 1.2.0
	 */
	public function update_sitemap() {
		return true;
	}

	/**
	 * Deletes the xml sitemap
	 * Returns true if the sitemap was deleted successfully
	 *
	 * @deprecated since version 1.2.0
	 *
	 * @return bool
	 */
	public function delete_sitemap() {
		return true;
	}
}