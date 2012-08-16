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
	
	protected $applications = array();
	protected $airships;
	/**
	 * Instantiate
	 */
	function __construct() {

		//$broadcast_message = array('aps'=>array('alert'=>'hello to all'));
		//echo $this->request( $this->airships['test'], 'broadcast', $broadcast_message );
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
	}
	
	function action_admin_init() {
		// @todo init only on post edit screens and in dashboard
		$options = get_option( 'blimply_options' );
		//var_dump($options);
		//list( $name, $key, $secret ) = $options;
		//var_dump( $name );
		$this->airships[ $options['blimply_name'] ] = new Airship( $options['blimply_app_key'], $options['blimply_app_secret'] );
		//}		
	}
	
	function action_save_post() {
		
	}
		
	/**
	 * Wrapper to make a remote request to UrbanAirship
	 *
	 * @param Airship $airship an instance of Airship php
	 * @param string $method
	 */
	function request( Airship $airship, $method = '', $args = array(), $tokens = array() ) {
		try{
			$response = $airship->$method( $args, $tokens );
		} catch ( Exception $e ) {
			$exception_class =  get_class( $e );
			if ( is_admin() ) {
				// @todo implement admin notification of misconfiguration
				//echo $exception_class;
			}
		}
		return $response;
	}
	
}

// define BLIMPLY_NOINIT constant somewhere in your theme to easily subclass Blimply
if ( ! defined( 'BLIMPLY_NOINIT' ) || defined( 'BLIMPLY_NOINIT' ) && BLIMPLY_NOINIT ) {
	global $blimply;
	$blimply = new Blimply;
}