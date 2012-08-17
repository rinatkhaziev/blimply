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
	
	protected $airships, $options;
	/**
	 * Instantiate
	 */
	function __construct() {
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
		add_action( 'save_post', array( $this, 'action_save_post' ) );
		add_action( 'add_meta_boxes', array( $this, 'post_meta_boxes' ) );
	}
	
	function action_admin_init() {
		// @todo init only on post edit screens and in dashboard
		$this->options = get_option( 'blimply_options' );
		$this->airships[ $this->options['blimply_name'] ] = new Airship( $this->options['blimply_app_key'], $this->options['blimply_app_secret'] );


		// $broadcast_message = array( 'aps' => array( 'alert' => 'hello to all', 'badge' => '+1' ) );
		// echo $this->request( $this->airships[ $this->options['blimply_name'] ], 'broadcast', $broadcast_message );
	
	}
	
	function action_save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;
		if ( !wp_verify_nonce( $_POST['blimply_nonce'], plugin_basename( __FILE__ ) ) )
      		return;

      	if ( 1 == $_POST['blimply_push'] ) {
      		$broadcast_message = array( 'aps' => array( 'alert' => 'push from published post: ' . $_POST['post_title'], 'badge' => '+1' ) );
      		$this->request( $this->airships[ $this->options['blimply_name'] ], 'broadcast', $broadcast_message  );
      		update_post_meta( $post_id, 'blimply_push_sent', true );
      	}
	}

	function post_meta_boxes() {
		$post_types = get_post_types( array( 'public' => true ), 'objects');
		foreach ( $post_types as $post_type => $props )
			add_meta_box( BLIMPLY_PREFIX, __( 'Push Notification', 'blimply' ), array( $this, 'post_meta_box' ), $post_type, 'side' );		

	}

	function post_meta_box( $post ) {
		$is_push_sent = get_post_meta( $post->ID, 'blimply_push_sent', true );
		if ( 1 != $is_push_sent ) {
			wp_nonce_field( plugin_basename( __FILE__ ), 'blimply_nonce' );
			echo '<label for="blimply_push">';
		    	_e("Send push notification", 'blimply' );
			echo '</label> ';
			echo '<input type="hidden" id="blimply_push" name="blimply_push" value="0" />';
			echo '<input type="checkbox" id="blimply_push" name="blimply_push" value="1" />';
		} else {
			_e( 'Push notification is already sent', 'blimply' );
		}
	}
		
	/**
	 * Wrapper to make a remote request to Urban Airship
	 *
	 * @param Airship $airship an instance of Airship php
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
				$exception_class =  get_class( $e );
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