<?php

/*
Contributors: christian.loelkes
Plugin Name: Octoprint for WP
Plugin URI: http://wordpress.org/extend/plugins/octoprint/
Description: Octoprint plugin for Wordpress
Version: 0.1
Stable tag: trunk
Tags:
Requires at least: 3.0
Tested up to: 3.9
Author: Christian Lölkes
Author URI: http://www.db4cl.com
License: GPLv2

Copyright 2014 Christian Loelkes  (email : christian.loelkes@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// --------------------------
// Plugin class starting here
// --------------------------

if(!class_exists('OctoprintPlugin')) {
	class OctoprintPlugin {
		public function __construct() {
        		// Initialize Settings
            		require_once(sprintf("%s/settings.php", dirname(__FILE__)));
			$octoprintSettings = new octoprintSettings();

			function insert_octoprint() {
				extract( shortcode_atts( array(
					'url' => get_option('octoprint_url'),
					'key' => get_option('octoprint_api_key')
				), $atts ) );
			}
			// Function for the shortcode
			add_shortcode( 'octoprint', 'insert_octoprint' );

		} // END public function __construct

		public static function activate() {} // END public static function activate
		public static function deactivate() {} // END public static function deactivate

	} 
} 

// ------------------------
// Plugin class ending here
// ------------------------

// -----------------------------
// Octoprint class starting here
// -----------------------------

class Octoprint {
	
	public $data;

	public function __construct( $url, $key ) {
		if( empty($url) ) $url = get_option('octoprint_url');
		if( empty($key) ) $key = get_option('octoprint_api_key');
		$this->data = $this->poll( $url, $key );
	}

	public function poll( $url, $key ) {
		$json = file_get_contents( $url.'/api/state?apikey='.$key );
		$obj = json_decode( $json );
		return $obj;
	}

	public function getStateString() {
		return $this->data->state->stateString;
	}

	public function getProgress() {
		return round($this->data->progress->completion,2);
	}

	public function getTemp($tool) {
		return $this->data->temperatures->$tool->actual;
	}
}

// ---------------------------
// Octoprint class ending here
// ---------------------------

// --------------------------
// Widget class starting here
// --------------------------

class OctoprintWidget extends WP_Widget {

	public $widget_form_fields = array(
		array( 'title' => 'Title', 'type' => 'text'),
		array( 'title' => 'URL', 'type' => 'text'),
		array( 'title' => 'Key', 'type' => 'text'),
		array( 'title' => 'Comment', 'type' => 'text'),
		array( 'title' => 'Media', 'type' => 'text')
	);

	function octoprintWidget() {
		$widget_ops = array('classname' => 'OctoprintWidget', 'description' => 'Displays a random post with thumbnail' );
		$this->WP_Widget('OctoprintWidget', 'Octoprint Widget', $widget_ops);
	}

	function form($instance) {
		echo '<p>Leave the fields empty to use the default parameters set on the plugin settings page.</p>';
		foreach( $this->widget_form_fields as $field ) {
			echo '<p><label for="'.$this->get_field_id( $field[ 'title' ] ).'"></label>';
			echo $field[ 'title' ].': <input class="widefat" id="'.$this->get_field_id( $field[ 'title' ] ).'" ';
			echo ' name="'.$this->get_field_name( $field[ 'title' ] ).'" type="'.$field[ 'type' ].'" value="'.attribute_escape( $instance[ $field[ 'title' ] ] ).'" />';
			echo '</p>';
		}
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		foreach( $this->widget_form_fields as $field ) {
			$instance[$field['title']] = $new_instance[$field['title']];
		}
		return $instance;
	}

	function widget($args, $instance) {

		$octoprint = new Octoprint( $instance['URL'], $instance['Key'] );

		extract($args, EXTR_SKIP);
		echo $before_widget;
		$title = apply_filters('widget_title', $instance['Title'] );
		if (!empty($title)) {
			echo $before_title . $title . $after_title;;
			echo get_option('octoprint_widget_text').'<hr />';
			echo 'State: '.$octoprint->getStateString().'<br />';
			echo 'Head temp: '.$octoprint->getTemp('tool0').' °C<br />';
			echo 'Progress: '.$octoprint->getProgress().' %<br />';
			echo $after_widget;
		}
	}
}

// ------------------------
// Widget class ending here
// ------------------------

if( class_exists( 'octoprintPlugin' )) {
	// Installation and uninstallation hooks
	register_activation_hook( __FILE__, array('octoprintPlugin', 'activate' ));
	register_deactivation_hook( __FILE__, array('octoprintPlugin', 'deactivate' ));

	// instantiate the plugin class
	$octoprintPlugin = new OctoprintPlugin();

    // Add a link to the settings page onto the plugin page
    if( isset( $octoprintPlugin )) {
	// Add the widget
	add_action( 'widgets_init', create_function( '', 'return register_widget( "OctoprintWidget" );') );

	// Add the settings link to the plugins page
        function octoprintWidget_settings_link( $links ) {
            $settings_link = '<a href="options-general.php?page=octoprint">Settings</a>';
            array_unshift( $links, $settings_link );
            return $links;
        }
        $plugin = plugin_basename( __FILE__ );
        add_filter( "plugin_action_links_$plugin", 'octoprintWidget_settings_link' );
    }
}