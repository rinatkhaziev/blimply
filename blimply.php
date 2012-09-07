<?php
/*
Plugin Name: Blimply
Plugin URI: http://doejo.com
Description: Blimply allows you to send push notifications to your mobile users utilizing Urban Airship API. 
Author: Rinat Khaziev, doejo
Version: 0.1
Author URI: http://doejo.com

GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

define( 'BLIMPLY_VERSION', '0.1' );
define( 'BLIMPLY_ROOT' , dirname( __FILE__ ) );
define( 'BLIMPLY_FILE_PATH' , BLIMPLY_ROOT . '/' . basename( __FILE__ ) );
define( 'BLIMPLY_URL' , plugins_url( '/', __FILE__ ) );
define( 'BLIMPLY_PREFIX' , 'blimply' );

// Bootstrap
// Try to include PEAR_Info to check if dependency package for Urban Airship API is installed
// This is a workaround to prevent possible Fatal error in urbanairhship.php due to missing required file
include_once 'PEAR/Info.php';

if (  ! class_exists( 'PEAR_Info' ) || ! PEAR_Info::packageInstalled( 'HTTP_Request' ) ) {
	// Include admin error notice that informs that the plugin won't be functional
	require_once( BLIMPLY_ROOT . '/lib/blimply-no-http-request.php' );
	return;
}
require_once( BLIMPLY_ROOT . '/lib/urban-airship/urbanairship.php' );
require_once( BLIMPLY_ROOT . '/lib/blimply-settings.php' );

class Blimply {
	
	protected $airships, $airship, $options, $tags;
	/**
	 * Instantiate
	 */
	function __construct() {
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
		add_action( 'save_post', array( $this, 'action_save_post' ) );
		add_action( 'add_meta_boxes', array( $this, 'post_meta_boxes' ) );
		add_action( 'update_option_blimply_options', array( $this, 'sync_airship_tags' ), 5, 2 );
		add_action( 'register_taxonomy', array( $this, 'after_register_taxonomy' ), 5, 3 );
		add_action( 'create_term', array( $this, 'action_create_term' ), 5, 3 );
		add_action( 'init', array( $this, 'l10n' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'dashboard_setup' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts_and_styles' ) );
	}
	
	function dashboard_setup() {
	if ( is_blog_admin() && current_user_can('edit_posts') )
		wp_add_dashboard_widget( 'dashboard_blimply', __( 'Send a Push notification' ), array( $this, 'dashboard_widget' ) );		
	}
	
	function l10n() {
		load_plugin_textdomain( 'blimply', false, dirname( plugin_basename( __FILE__ ) ) . '/lib/languages/' );
	}
	/**
	*
	* Set basic app properties 
	*
	*/
	function action_admin_init() {
		// @todo init only on post edit screens and in dashboard
		$this->options = get_option( 'blimply_options' );		
		$this->airships[ $this->options['blimply_name'] ] = new Airship( $this->options['blimply_app_key'], $this->options['blimply_app_secret'] );
		// Pass the reference to convenience var
		// We don't use multiple Airships yet.
		$this->airship = &$this->airships[ $this->options['blimply_name'] ];
		register_taxonomy( 'blimply_tags', array( 'post' ), array(
			'public' => false,
			'labels' => array(
				'name' => __( 'Urban Airship Tags', 'blimply' ),
				'singular_name' => __( 'Urban Airship Tags', 'blimply' ),
			),
			'show_in_nav_menus' => false,
			'show_ui' => false
			) );
		$this->tags = get_terms( 'blimply_tags', array( 'hide_empty' => 0 ) );
	}
	
	/**
	 * Register scripts and styles
	 *
	 */
	function register_scripts_and_styles() {
		global $pagenow;
		echo $pagenow;
		// Only load this on the proper page
		if ( ! in_array( $pagenow, array( 'post-new.php', 'post.php' ) ) )
			return;
		wp_enqueue_style( 'blimply-style', BLIMPLY_URL . '/lib/css/blimply.css' );
		wp_enqueue_script( 'blimply-js', BLIMPLY_URL . '/lib/js/blimply.js', array( 'jquery' )  );
	}	
	
	/**
	 * Sync our newly created tag with Urban Airship
	 *
	 *	@param int $term_id term_id
	 *	@param int $tt_id term_taxonomy_id
	 *	@param string $taxonomy
	 */
	function action_create_term( $term_id, $tt_id, $taxonomy ) {
		if ( 'blimply_tags' != $taxonomy )
			return;
		$tag = get_term( $term_id, $taxonomy );
		// Let's sync
		if ( ! is_wp_error( $tag ) ) { 
			try {
				$response = $this->airship->_request( BASE_URL . "/tags/{$tag->slug}", 'PUT', null );
			} catch ( Exception $e ) {
				// @todo do something with exception
			}
			if ( isset( $response[0] ) && $response[0] == 201 ) {
				// @todo process ok result
			} 
		}		
	}
	
	/**
	* Send a push notification if checkbox is checked
	*/
	function action_save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;
		if ( !wp_verify_nonce( $_POST['blimply_nonce'], BLIMPLY_FILE_PATH ) )
      		return;
      	if ( 1 == get_post_meta( $post->ID, 'blimply_push_sent', true ) )
      		return;

      	if ( 1 == $_POST['blimply_push'] ) {
			// @todo implement sending to tags if any specified
			$alert = !empty( $_POST['blimply_push_alert'] ) ? esc_attr( $_POST['blimply_push_alert'] ) : esc_attr( $_POST['post_title'] );
      		$broadcast_message = array( 'aps' => array( 'alert' => '' . $alert, 'badge' => '+1' ) );
      		$this->request( $this->airship, 'broadcast', $broadcast_message  );
      		update_post_meta( $post_id, 'blimply_push_sent', true );
      	}
	}

	/**
	* Register metabox for selected post types
	*
	* @todo implement ability to actually pick specific post types
	*/
	function post_meta_boxes() {
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		foreach ( $post_types as $post_type => $props )
			add_meta_box( BLIMPLY_PREFIX, __( 'Push Notification', 'blimply' ), array( $this, 'post_meta_box' ), $post_type, 'side' );		
	}

	/**
	* Render HTML
	*/
	function post_meta_box( $post ) {
		$is_push_sent = get_post_meta( $post->ID, 'blimply_push_sent', true );
		if ( 1 != $is_push_sent ) {
			echo '<div class="blimply-wrapper">';
			wp_nonce_field( BLIMPLY_FILE_PATH, 'blimply_nonce' );
			echo '<label for="blimply_push_alert">';
		    	_e( 'Push message', 'blimply' );
			echo '</label><br/> ';
			echo '<textarea id="blimply_push_alert" name="blimply_push_alert" class="bl_textarea">' . $post->post_title . '</textarea><br/>';
			echo '<strong>' . __( 'Send Push to following Urban Airship tags', 'blimply' ) . '</strong>';
			foreach ( $this->tags as $tag ) {
				echo '<input type="radio" name="blimply_push_tag" id="blimply_tag_' .$tag->term_id . '" />';
				echo '<label class="selectit" for="blimply_tag_' .$tag->term_id . '" style="margin-left: 4px">';
				echo $tag->name;
				echo '</label><br/>';				
			}
			echo '<br/><input type="hidden" id="" name="blimply_push" value="0" />';
			echo '<input type="checkbox" id="blimply_push" name="blimply_push" value="1" disabled="disabled" />';			
			echo '<label for="blimply_push">';
		    	_e( 'Check to confirm sending', 'blimply' );
			echo '</label> ';			
			echo '</div>';
			
		} else {
			_e( 'Push notification is already sent', 'blimply' );
		}
	}
		
	/**
	 * Wrapper to make a remote request to Urban Airship
	 *
	 * @param Airship $airship an instance of Airship passed by reference
	 * @param string $method
	 * @param mixed $args
	 * @param mixed $tokens
	 * @return mixed response or Exception or error
	 */
	function request( Airship &$airship, $method = '', $args = array(), $tokens = array() ) {
		
		if ( in_array( $method, array( 'register', 'deregister', 'feedback', 'push', 'broadcast' ) ) ) {
			try {
				$response = $airship->$method( $args, $tokens );
			} catch ( Exception $e ) {
				$exception_class = get_class( $e );
				if ( is_admin() ) {
					// @todo implement admin notification of misconfiguration
					//echo $exception_class;
				}
			}
			return $response;
		} else {
			// @todo illegal request
		}
	}
	
	function dashboard_widget() {
		global $post_ID;
	
		$drafts = false;
		if ( 'post' === strtolower( $_SERVER['REQUEST_METHOD'] ) && isset( $_POST['action'] ) && 0 === strpos( $_POST['action'], 'post-quickpress' ) && (int) $_POST['post_ID'] ) {
			$view = get_permalink( $_POST['post_ID'] );
			$edit = esc_url( get_edit_post_link( $_POST['post_ID'] ) );
			if ( 'post-quickpress-publish' == $_POST['action'] ) {
				if ( current_user_can('publish_posts') )
					printf( '<div class="updated"><p>' . __( 'Post published. <a href="%s">View post</a> | <a href="%s">Edit post</a>' ) . '</p></div>', esc_url( $view ), $edit );
				else
					printf( '<div class="updated"><p>' . __( 'Post submitted. <a href="%s">Preview post</a> | <a href="%s">Edit post</a>' ) . '</p></div>', esc_url( add_query_arg( 'preview', 1, $view ) ), $edit );
			} else {
				printf( '<div class="updated"><p>' . __( 'Draft saved. <a href="%s">Preview post</a> | <a href="%s">Edit post</a>' ) . '</p></div>', esc_url( add_query_arg( 'preview', 1, $view ) ), $edit );
				$drafts_query = new WP_Query( array(
					'post_type' => 'post',
					'post_status' => 'draft',
					'author' => $GLOBALS['current_user']->ID,
					'posts_per_page' => 1,
					'orderby' => 'modified',
					'order' => 'DESC'
				) );
	
				if ( $drafts_query->posts )
					$drafts =& $drafts_query->posts;
			}
			printf('<p class="textright">' . __('You can also try %s, easy blogging from anywhere on the Web.') . '</p>', '<a href="' . esc_url( admin_url( 'tools.php' ) ) . '">' . __('Press This') . '</a>' );
			$_REQUEST = array(); // hack for get_default_post_to_edit()
		}
	
		/* Check if a new auto-draft (= no new post_ID) is needed or if the old can be used */
		$last_post_id = (int) get_user_option( 'dashboard_quick_press_last_post_id' ); // Get the last post_ID
		if ( $last_post_id ) {
			$post = get_post( $last_post_id );
			if ( empty( $post ) || $post->post_status != 'auto-draft' ) { // auto-draft doesn't exists anymore
				$post = get_default_post_to_edit('post', true);
				update_user_option( (int) $GLOBALS['current_user']->ID, 'dashboard_quick_press_last_post_id', (int) $post->ID ); // Save post_ID
			} else {
				$post->post_title = ''; // Remove the auto draft title
			}
		} else {
			$post = get_default_post_to_edit('post', true);
			update_user_option( (int) $GLOBALS['current_user']->ID, 'dashboard_quick_press_last_post_id', (int) $post->ID ); // Save post_ID
		}
	
		$post_ID = (int) $post->ID;
	?>
	
		<form name="post" action="<?php echo esc_url( admin_url( 'post.php' ) ); ?>" method="post" id="quick-press">
			<h4 id="quick-post-title"><label for="title"><?php _e('Title') ?></label></h4>
			<div class="input-text-wrap">
				<input type="text" name="post_title" id="title" tabindex="1" autocomplete="off" value="<?php echo esc_attr( $post->post_title ); ?>" />
			</div>
	
			<?php if ( current_user_can( 'upload_files' ) ) : ?>
			<div id="wp-content-wrap" class="wp-editor-wrap hide-if-no-js wp-media-buttons">
				<?php do_action( 'media_buttons', 'content' ); ?>
			</div>
			<?php endif; ?>
	
			<h4 id="content-label"><label for="content"><?php _e('Content') ?></label></h4>
			<div class="textarea-wrap">
				<textarea name="content" id="content" class="mceEditor" rows="3" cols="15" tabindex="2"><?php echo esc_textarea( $post->post_content ); ?></textarea>
			</div>
	
			<script type="text/javascript">edCanvas = document.getElementById('content');edInsertContent = null;</script>
	
			<h4><label for="tags-input"><?php _e('Tags') ?></label></h4>
			<div class="input-text-wrap">
				<input type="text" name="tags_input" id="tags-input" tabindex="3" value="<?php echo get_tags_to_edit( $post->ID ); ?>" />
			</div>
	
			<p class="submit">
				<input type="hidden" name="action" id="quickpost-action" value="post-quickpress-save" />
				<input type="hidden" name="post_ID" value="<?php echo $post_ID; ?>" />
				<input type="hidden" name="post_type" value="post" />
				<?php wp_nonce_field('add-post'); ?>
				<?php submit_button( __( 'Save Draft' ), 'button', 'save', false, array( 'id' => 'save-post', 'tabindex'=> 4 ) ); ?>
				<input type="reset" value="<?php esc_attr_e( 'Reset' ); ?>" class="button" />
				<span id="publishing-action">
					<input type="submit" name="publish" id="publish" accesskey="p" tabindex="5" class="button-primary" value="<?php current_user_can('publish_posts') ? esc_attr_e('Publish') : esc_attr_e('Submit for Review'); ?>" />
					<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
				</span>
				<br class="clear" />
			</p>
	
		</form>
	
	<?php
		if ( $drafts )
			wp_dashboard_recent_drafts( $drafts );
		}
	
}

// define BLIMPLY_NOINIT constant somewhere in your theme to easily subclass Blimply
if ( ! defined( 'BLIMPLY_NOINIT' ) || defined( 'BLIMPLY_NOINIT' ) && BLIMPLY_NOINIT ) {
	global $blimply;
	$blimply = new Blimply;
}