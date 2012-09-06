<?php
/*
Plugin Name: Blimply
Plugin URI: http://doejo.com
Description: Blimply is a simple plugin that will allow you to send push notifications to your mobile users utilizing Urban Airship API. 
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
		add_action( 'register_taxonomy', array( $this, 'after_register_taxonomy' ), 5, 3  );
		add_action( 'create_term', array( $this, 'action_create_term' ), 5, 3 );
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
			wp_nonce_field( BLIMPLY_FILE_PATH, 'blimply_nonce' );
			echo '<label for="blimply_push">';
		    	_e( 'Send push notification', 'blimply' );
			echo '</label> ';
			echo '<input type="hidden" id="blimply_push" name="blimply_push" value="0" />';
			echo '<input type="checkbox" id="blimply_push" name="blimply_push" value="1" />';
			echo '<br/><label for="blimply_push_alert">';
		    	_e( 'Push message', 'blimply' );
			echo '</label><br/> ';
			echo '<textarea id="blimply_push_alert" name="blimply_push_alert">' . $post->post_title . '</textarea><br/>';
			echo '<strong>' . __( 'Send Push to following Urban Airship tags', 'blimply' ) . '</strong><br/>';
			foreach ( $this->tags as $tag ) {
				echo '<input type="checkbox" name="blimply_push_tag[]" id="blimply_tag_' .$tag->term_id . '" />';
				echo '<label class="selectit" for="blimply_tag_' .$tag->term_id . '">';
				echo $tag->name;
				echo '</label><br/>';				
			}
			
			
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
	
}

// define BLIMPLY_NOINIT constant somewhere in your theme to easily subclass Blimply
if ( ! defined( 'BLIMPLY_NOINIT' ) || defined( 'BLIMPLY_NOINIT' ) && BLIMPLY_NOINIT ) {
	global $blimply;
	$blimply = new Blimply;
}