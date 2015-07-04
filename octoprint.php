<?php

/*

Contributors: christian.loelkes
Plugin Name: Octoprint for WP
Plugin URI: http://wordpress.org/extend/plugins/octoprint/
Description: Octoprint plugin for Wordpress
Version: 1.0
Stable tag: 0.2
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VNRJ5FSUV3C6L
Tags: stl, 3d, shortcode, 3d printing, octoprint
Requires at least: 3.0
Tested up to: 4.1.1
Author: Christian Lölkes
Author URI: http://www.db4cl.com
License: CC Attribution-NoDerivatives 4.0 International

*/

require_once(sprintf("%s/settings.php", dirname(__FILE__)));

// --------------------------
// Plugin class starting here
// --------------------------

if(!class_exists('Octoprint')) {
	class Octoprint {

        public $Settings;

		public function __construct() {

            // Set hooks for (de)activation
            register_activation_hook( 	__FILE__, array( &$this, 'activate' ));
            register_deactivation_hook(	__FILE__, array( &$this, 'deactivate' ));

            // Add the widget
            add_action( 'widgets_init', create_function( '', 'return register_widget( "OctoprintWidget" );') );

			// Enable the shortcode
			add_shortcode( 'octoprint', array(&$this, 'insert_octoprint') );

            // Add the settings to the plugin
            $this->Settings = new octoprintSettings();

            $plugin = plugin_basename( __FILE__ );
            add_filter( "plugin_action_links_$plugin", array( &$this, 'octoprintWidget_settings_link') );

		} // END public function __construct

        public function insert_octoprint() {
            extract( shortcode_atts( array(
                'url' => get_option('octoprint_url'),
                'key' => get_option('octoprint_api_key')
            ), $atts ) );

            $octoprint = new Octoprint_API($url, $key);
            return $octoprint->render();

        }

        public function octoprintWidget_settings_link( $links ) {
            $settings_link = '<a href="options-general.php?page=octoprint">Settings</a>';
            array_unshift( $links, $settings_link );
            return $links;
        }

        public function activate() {
            $settings = $this->Settings->getSettingsArray();
            foreach($settings as $setting){
                add_option($this->Settings->getSettingPrefix().$setting['name'], $setting['default']);
            }
        }
        public function deactivate() {
            if( get_option($this->Settings->getSettingPrefix().'delete_settings') ) {
                $settings = $this->Settings->getSettingsArray();
                foreach ($settings as $setting) {
                    delete_option($this->Settings->getSettingPrefix() . $setting['name']);
                }
            }
        }

	} 
}

if(!class_exists('Octoprint_Post')) {
    class Octoprint_Post extends Octoprint{

        protected $args = array(
            'label'               => 'octoprint_client',
            'description'         => 'Octoprint client',
            'labels'              => '',
            'supports'            => array( 'title', 'custom-fields', ),
            'taxonomies'          => array( 'category', 'post_tag' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 5,
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
        );

        protected $labels = array(
            'name'                => 'Octoprint clients',
            'singular_name'       => 'Octoprint client',
            'menu_name'           => 'Octoprint client',
            'name_admin_bar'      => 'Octoprint clients',
            'parent_item_colon'   => 'Parent Item:',
            'all_items'           => 'All clients',
            'add_new_item'        => 'Add New clients',
            'add_new'             => 'Add New',
            'new_item'            => 'New client',
            'edit_item'           => 'Edit client',
            'update_item'         => 'Update client',
            'view_item'           => 'View client',
            'search_items'        => 'Search client',
            'not_found'           => 'Not found',
            'not_found_in_trash'  => 'Not found in Trash'
        );

        public function octoprint_post_type() {
            $this->args['labels'] = $this->labels;
            register_post_type( 'octoprint_client', $this->args );
        }

        public function __construct() {
            // Hook into the 'init' action
            add_action('init', array( &$this, 'octoprint_post_type'), 0);
        }
    }
}

// ------------------------
// Plugin class ending here
// ------------------------

// -----------------------------
// Octoprint class starting here
// -----------------------------

if(!class_exists('Octoprint_API')) {
	class Octoprint_API extends Octoprint{
	
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

		public function render() {
			$output = 'State: '.$this->getStateString().'<br />';
			$output .= 'Head temp: '.$this->getTemp('tool0').' °C<br />';
			$output .= 'Progress: '.$this->getProgress().' %<br />';
			return $output;
		}

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
			echo $octoprint->render();
			echo $after_widget;
		}
	}
}

// ------------------------
// Widget class ending here
// ------------------------


$octoprintPlugin = new Octoprint();

