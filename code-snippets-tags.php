<?php

/**
 * Plugin Name: Code Snippets Tags
 * Plugin URL: https://github.com/bungeshea/code-snippets-tags
 * Description: Adds support for adding tags to snippets to the Code Snippets WordPress plugin. Requires Code Snippets 1.7 or later
 * Author: Shea Bunge
 * Author URI: http://bungeshea.com
 * Version: 0.1
 * License: MIT
 * License URI: http://opensource.org/license/mit-license.php
 */

class Code_Snippets_Tags {

	static $version = 0.1;

	static function init() {

		/* Administration */
		add_action( 'code_snippets_admin_single', array( __CLASS__, 'admin_single' ) );
		add_filter( 'code_snippets_list_table_columns', array( __CLASS__, 'add_table_column' ) );
		add_action( 'code_snippets_list_table_column_tags', array( __CLASS__, 'table_column' ), 10, 1 );

		/* Seralizing snippet data */
		add_filter( 'code_snippets_escape_snippet_data', array( __CLASS__, 'escape_snippet_data' ) );
		add_filter( 'code_snippets_unescape_snippet_data', array( __CLASS__, 'unescape_snippet_data' ) );

		/* Creating a snippet object */
		add_filter( 'code_snippets_build_default_snippet', array( __CLASS__, 'build_default_snippet' ) );
		add_filter( 'code_snippets_build_snippet_object', array( __CLASS__, 'build_snippet_object' ), 10, 2 );

		/* Scripts and styles */
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		self::upgrade();
	}

	/**
	 * Check if the currently installed plugin version is new or not
	 */
	static function upgrade() {

		$installed_version = get_site_option( 'code_snippets_tags_version' );

		if ( self::$version !== $installed_version ) {
			// first run of this version, record it in the database
			update_site_option( 'code_snippets_tags_version', self::$version );
			// add the database column
			self::add_database_column();
		}
	}

	/**
	 * Add a column to the database
	 */
	static function add_database_column() {
		global $wpdb, $code_snippets;

		$code_snippets->create_tables();

		$sql = 'ALTER TABLE %s ADD COLUMN tags longtext AFTER code';

		$wpdb->query( sprintf( $sql, $wpdb->snippets ) );

		if ( is_multisite() )
			$wpdb->query( sprintf( $sql, $wpdb->ms_snippets ) );
	}

	static function add_table_column( $columns ) {
		$columns['tags'] = __('Tags', 'code-snippets-tags');
		return $columns;
	}

	static function table_column( $snippet ) {

		if ( ! empty( $snippet->tags ) ) {
			echo join( ', ', $snippet->tags );
		} else {
			echo '&#8212;';
		}
	}

	static function convert_tags( $tags ) {

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

	static function escape_snippet_data( $snippet ) {
		$snippet->tags = self::convert_tags( $snippet->tags );
		$snippet->tags = maybe_serialize( $snippet->tags );
		return $snippet;
	}

	static function unescape_snippet_data( $snippet ) {
		$snippet->tags = maybe_unserialize( $snippet->tags );
		$snippet->tags = self::convert_tags( $snippet->tags );
		return $snippet;
	}

	static function build_default_snippet( $snippet ) {
		$snippet->tags = array();
		return $snippet;
	}

	static function build_snippet_object( $snippet, $data ) {

		if ( isset( $data['tags'] ) )
			$snippet->tags = $data['tags'];

		elseif ( isset( $data['snippet_tags'] ) )
			$snippet->tags = $data['snippet_tags'];

		return $snippet;
	}

	static function enqueue_scripts() {
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

	static function admin_single( $snippet ) {
	?>
		<label for="snippet_tags" style="cursor: auto;">
			<h3><?php esc_html_e('Tags', 'code-snippets'); ?>
			<span style="font-weight: normal;"><?php esc_html_e('(Optional)', 'code-snippets'); ?></span></h3>
		</label>

		<input type="text" id="snippet_tags" name="snippet_tags" style="width: 100%;" placeholder="Enter a list of tags; separated by commas" value="<?php echo implode( ', ', $snippet->tags ); ?>" />

		<script type="text/javascript">jQuery('#snippet_tags').tagit();</script>

	<?php
	}
}

add_action( 'code_snippets_init', array( 'Code_Snippets_Tags', 'init' ) );