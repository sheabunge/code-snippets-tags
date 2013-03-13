<?php

/**
 * Plugin Name: Code Snippets Tags
 * Plugin URL: https://github.com/bungeshea/code-snippets-tags
 * Description: Adds support for adding tags to snippets to the Code Snippets WordPress plugin. Requires Code Snippets 1.7 or later
 * Author: Shea Bunge
 * Author URI: http://bungeshea.com
 * Version: 1.0
 * License: MIT
 * License URI: http://opensource.org/license/mit-license.php
 */

class Code_Snippets_Tags {

	/**
	 * The version number for this release of the plugin.
	 * This will later be used for upgrades and enqueueing files
	 *
	 * This should be set to the 'Plugin Version' value,
	 * as defined above in the plugin header
	 *
	 * @since Code Snippets Tags 1.0
	 * @access public
	 */
	public $version = 1.0;

	/**
	 * The constructor function for our class
	 *
	 * We don't do anything here except hook our init
	 * method to the appropriate hook. This will ensure that
	 * we only initialize after Code Snippets has loaded
	 *
	 * @since Code Snippets Tags 1.0
	 * @access private
	 */
	function __construct() {
		add_action( 'code_snippets_init', array( $this, 'init' ) );
	}

	/**
	 * The initializer function for our class
	 *
	 * Here we hook our methods to their actions
	 * and filters, and run the upgrade method
	 *
	 * @since Code Snippets Tags 1.0
	 * @access public
	 */
	public function init() {

		/* Administration */
		add_action( 'code_snippets_admin_single', array( $this, 'admin_single' ) );
		add_filter( 'code_snippets_list_table_columns', array( $this, 'add_table_column' ) );
		add_action( 'code_snippets_list_table_column_tags', array( $this, 'table_column' ), 10, 1 );
		add_action( 'code_snippets_list_table_filter_controls', array( $this, 'tags_dropdown' ) );
		add_action( 'code_snippets_list_table_prepare_items', array( $this, 'filter_snippets' ) );

		/* Serializing snippet data */
		add_filter( 'code_snippets_escape_snippet_data', array( $this, 'escape_snippet_data' ) );
		add_filter( 'code_snippets_unescape_snippet_data', array( $this, 'unescape_snippet_data' ) );

		/* Creating a snippet object */
		add_filter( 'code_snippets_build_default_snippet', array( $this, 'build_default_snippet' ) );
		add_filter( 'code_snippets_build_snippet_object', array( $this, 'build_snippet_object' ), 10, 2 );

		/* Scripts and styles */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		$this->upgrade();
	}

	/**
	 * Check if the currently installed plugin version is new or not
	 *
	 * @since Code Snippets Tags 1.0
	 * @access public
	 */
	public function upgrade() {

		$installed_version = get_site_option( 'code_snippets_tags_version' );

		if ( $this->version !== $installed_version ) {
			// first run of this version, record it in the database
			update_site_option( 'code_snippets_tags_version', $this->version );
			// add the database column
			$this->add_database_column();
		}
	}

	/**
	 * Add a column to the database
	 *
	 * @since Code Snippets Tags 1.0
	 * @access public
	 */
	public function add_database_column() {
		global $wpdb, $code_snippets;

		$code_snippets->create_tables();

		$sql = 'ALTER TABLE %s ADD COLUMN tags longtext AFTER code';

		$wpdb->query( sprintf( $sql, $wpdb->snippets ) );

		if ( is_multisite() )
			$wpdb->query( sprintf( $sql, $wpdb->ms_snippets ) );
	}

	/**
	 * Add a tags column to the snippets table
	 *
	 * @since Code Snippets Tags 1.0
	 * @access private
	 */
	function add_table_column( $columns ) {
		$columns['tags'] = __('Tags', 'code-snippets-tags');
		return $columns;
	}

	/**
	 * Output the content of the table column
	 * This function is used once for each row
	 *
	 * @since Code Snippets Tags 1.0
	 * @access private
	 */
	function table_column( $snippet ) {

		if ( ! empty( $snippet->tags ) ) {

			foreach ( $snippet->tags as $tag ) {
				$out[] = sprintf( '<a href="%s">%s</a>',
					add_query_arg( 'tag', esc_attr( $tag ) ),
					esc_html( $tag )
				);
			}
			echo join( ', ', $out );
		} else {
			echo '&#8212;';
		}
	}

	/**
	 * Filter the snippets based
	 * on the tag filter
	 *
	 * @since Code_Snippets_Tags 1.0
	 * @access public
	 */
	function filter_snippets() {
		global $snippets, $status;

		if ( isset( $_GET['tag'] ) ) {
			$status = 'search';
			$snippets['search'] = array_filter( $snippets['all'], array( $this, '_filter_snippets_callback' ) );
		}
	}

	function _filter_snippets_callback( $snippet ) {

		$tags = explode( ',', $_GET['tag'] );

		foreach ( $tags as $tag ) {
			if ( in_array( $tag, $snippet->tags ) ) {
				return true;
			}
		}
	}

	/**
	 * Display a dropdown of all of the used tags for filtering items
	 *
	 * @since Code Snippets Tags 1.0
	 * @access public
	 */
	public function tags_dropdown() {
		global $wpdb;

		$tags = $this->get_all_tags();
		$query = isset( $_GET['tag'] ) ? $_GET['tag'] : '';

		if ( ! count( $tags ) )
			return;

		echo '<select name="tag">';

		printf ( "<option %s value=''>%s</option>\n",
			selected( $query, '', false ),
			__('Show all tags', 'code-snippets-tags')
		);

		foreach ( $tags as $tag ) {

			printf( "<option %s value='%s'>%s</option>\n",
				selected( $query, $tag, false ),
				esc_attr( $tag ),
				$tag
			);
		}

		echo '</select>';
	}

	/**
	 * Gets all of the used tags from the database
	 *
	 * @since Code Snippets Tags 1.0
	 * @access public
	 */
	public function get_all_tags() {
		global $wpdb, $code_snippets;

		// grab all tags from the database
		$tags = array();
		$table = $code_snippets->get_table_name();
		$all_tags = $wpdb->get_col( "SELECT tags FROM $table" );

		// merge all tags into a single array
		foreach ( $all_tags as $snippet_tags ) {
			$snippet_tags = maybe_unserialize( $snippet_tags );
			$snippet_tags = $this->convert_tags( $snippet_tags );
			$tags = array_merge( $snippet_tags, $tags );
		}

		// remove dupicate tags
		return array_values( array_unique( $tags, SORT_REGULAR ) );
	}

	/**
	 * Make sure that the tags are a valid array
	 *
	 * @since Code Snippets Tags 1.0
	 * @access public
	 */
	public function convert_tags( $tags ) {

		/* if there are no tags set, create a default empty array */
		if ( empty( $tags ) ) {
			$tags = array();
		}

		/* if the tags are set as a string, convert them to an array */
		elseif ( is_string( $tags ) ) {
			$tags = str_replace( ', ', ',', $tags );
			$tags = explode( ',', $tags );
		}

		/* if we still don't have an array, just convert whatever we do have into one */
		if ( ! is_array( $tags ) ) {
			$tags = (array) $tags;
		}

		return $tags;
	}

	/**
	 * Escape the tag data for insertion into the database
	 *
	 * @since Code Snippets Tags 1.0
	 * @access private
	 */
	function escape_snippet_data( $snippet ) {
		$snippet->tags = $this->convert_tags( $snippet->tags );
		$snippet->tags = maybe_serialize( $snippet->tags );
		return $snippet;
	}

	/**
	 * Unescape the tag data after retrieval from the database,
	 * ready for use
	 *
	 * @since Code Snippets Tags 1.0
	 * @access private
	 */
	function unescape_snippet_data( $snippet ) {
		$snippet->tags = maybe_unserialize( $snippet->tags );
		$snippet->tags = $this->convert_tags( $snippet->tags );
		return $snippet;
	}

	/**
	 * Create an empty array for the tags
	 * when building an empty snippet object
	 *
	 * @since Code Snippets Tags 1.0
	 * @access private
	 */
	function build_default_snippet( $snippet ) {
		$snippet->tags = array();
		return $snippet;
	}

	/**
	 * Convert snippet array keys into a
	 * valid snippet object
	 *
	 * @since Code Snippets Tags 1.0
	 * @access private
	 */
	function build_snippet_object( $snippet, $data ) {

		if ( isset( $data['tags'] ) )
			$snippet->tags = $data['tags'];

		elseif ( isset( $data['snippet_tags'] ) )
			$snippet->tags = $data['snippet_tags'];

		return $snippet;
	}

	/**
	 * Enqueue the tag-it scripts and styles
	 * on the edit/add new snippet page
	 *
	 * @since Code Snippets Tags 1.0
	 * @access private
	 */
	function enqueue_scripts() {
		global $code_snippets;

		if ( get_current_screen()->id !== $code_snippets->admin_single )
			return;

		$tagit_version = '2.0';

		wp_register_script(
			'tag-it',
			plugins_url( 'assets/tag-it.min.js', __FILE__ ),
			array(
				'jquery-ui-core',
				'jquery-ui-widget',
				'jquery-ui-position',
				'jquery-ui-autocomplete',
				'jquery-effects-blind',
				'jquery-effects-highlight',
			),
			$tagit_version
		);

		wp_register_style(
			'tagit',
			plugins_url( 'assets/jquery.tagit.css', __FILE__ ),
			false,
			$tagit_version
		);

		wp_register_style(
			'tagit-zendesk-ui',
			plugins_url( 'assets/tagit.ui-zendesk.css', __FILE__ ),
			array( 'tagit' ),
			$tagit_version
		);

		wp_enqueue_style( 'tagit' );
		wp_enqueue_style( 'tagit-zendesk-ui' );
		wp_enqueue_script( 'tag-it' );
	}

	/**
	 * Output the interface for editing snippet tags
	 *
	 * @since Code Snippets Tags 1.0
	 * @access public
	 */
	public function admin_single( $snippet ) {
	?>
		<label for="snippet_tags" style="cursor: auto;">
			<h3><?php esc_html_e('Tags', 'code-snippets'); ?>
			<span style="font-weight: normal;"><?php esc_html_e('(Optional)', 'code-snippets'); ?></span></h3>
		</label>

		<input type="text" id="snippet_tags" name="snippet_tags" style="width: 100%;" placeholder="Enter a list of tags; separated by commas" value="<?php echo implode( ', ', $snippet->tags ); ?>" />

		<script type="text/javascript">
		jQuery('#snippet_tags').tagit({
			availableTags: ['<?php echo implode( "','", $this->get_all_tags() ); ?>'],
			allowSpaces: true,
			removeConfirmation: true
		});
		</script>

	<?php
	}
}

/**
 * Create an instance of the class
 * as part of the $code_snippets global
 * variable
 *
 * @since Code Snippets Tags 1.0
 */
global $code_snippets;
$code_snippets->tags = new Code_Snippets_Tags;