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


class Blimply {
	
	protected $applications = array();
	/**
	 * Instantiate
	 */
	function __construct() {
		$this->applications[] = array( 'key' => 'SYk74m98TOiUhvH29b5l_Q', 'secret' => 'sBPXJ92QQ6OY_lYjzb93ZA' );
	}
	
	function action_init() {
		
	}
	
	function action_save_post() {
		
	}
	
	function admin_menu() {
		
	}
	
	function ua_request() {
		
	}
}

// define BLIMPLY_NOINIT costant somewhere in your theme to easily subclass Blimply
if ( ! defined( 'BLIMPLY_NOINIT' ) ) {
	global $blimply;
	$blimply = new Blimply;
}