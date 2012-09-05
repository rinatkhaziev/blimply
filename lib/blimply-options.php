<?php
/**
 * Define our settings sections
 *
 * array key=$id, array value=$title in: add_settings_section( $id, $title, $callback, $page );
 * @return array
 */
function blimply_options_page_sections() {	
	$sections = array();
	// $sections[$id] 				= __($title, 'blimply');
	$sections['urban_airship'] 		= __('Urban Airship API Settings', 'blimply');
	return $sections;	
}

/**
 * Define our form fields (settings) 
 *
 * @return array
 */
function blimply_options_page_fields() {
	$options[] = array(
		"section" => "urban_airship",
		"id"      => BLIMPLY_PREFIX . "_name",
		"title"   => __( 'Urban Airship Application Slug!', 'blimply' ),
		"desc"    => __( 'Something like my-test-app.', 'blimply' ),
		"type"    => "text",
		"std"     => __('my-blimply','blimply'),
		"class"   => "nohtml"
	);
	$options[] = array(
		"section" => "urban_airship",
		"id"      => BLIMPLY_PREFIX . "_app_key",
		"title"   => __( 'Application API Key', 'blimply' ),
		"desc"    => __( '22 character long app key( like SYk74m98TOiUhHHHHb5l_Q.', 'blimply' ),
		"type"    => "text",
		"std"     => __('my-blimply','blimply'),
		"class"   => "nohtml"
	);
	$options[] = array(
		"section" => "urban_airship",
		"id"      => BLIMPLY_PREFIX . "_app_secret",
		"title"   => __( 'Application Master Secret', 'blimply' ),
		"desc"    => __( '22 character long app master secret( like SYk74m98TOiUhHHHHb5l_Q.', 'blimply' ),
		"type"    => "text",
		"std"     => __('my-blimply','blimply'),
		"class"   => "nohtml"
	);	
	return $options;	
}

/**
 * Contextual Help
 */
function blimply_options_page_contextual_help() {
	
	$text 	= "<h3>" . __('Blimply','blimply') . "</h3>";
	$text 	.= "<p>" . __('Urban Airship and Blimply Settings.','blimply') . "</p>";
	
	// must return text! NOT echo
	return $text;
} ?>