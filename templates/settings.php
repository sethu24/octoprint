<?php
if(!class_exists('octoprintSettings')) {

	class octoprintSettings { 							// Construct the plugin object

		public function __construct() {

			// register actions
            		add_action('admin_init', array(&$this, 'admin_init'));
        		add_action('admin_menu', array(&$this, 'add_menu'));

		} // END public function __construct

        public function admin_init() { 							// hook into WP's admin_init action hook

		////////////////////
		// Settings block // 
		////////////////////
		$sections = array(
			array('name' => 'widget', 	'title' =>'Widget settings'),
			//array('name' => 'shortcode', 	'title' =>'Shortcode settings'),
			array('name' => 'general', 	'title' =>'Default settings')
		);
		$settings = array(
			array('name' => 'octoprint_url', 		'title' => 'Octoprint URL', 		'type' => 'text',	'section' => 'general'),
			array('name' => 'octoprint_api_key', 		'title' => 'Api Key', 			'type' => 'text',	'section' => 'general'),
			array('name' => 'octoprint_media_url',		'title' => 'Media/Stream URL',		'type' => 'text',       'section' => 'general'),
			array('name' => 'octoprint_widget_title', 	'title' => 'Widget title', 		'type' => 'text',	'section' => 'widget'),
			array('name' => 'octoprint_widget_text', 	'title' => 'Widget free text', 		'type' => 'textarea',	'section' => 'widget'),
		);

		foreach( $settings as $field) {
			register_setting('octoprint-settings-group', $field['name']);
			add_settings_field($field['name'], $field['title'], array(&$this, $field['type']), 'octoprint', $field['section'], array('field' => $field['name']));
		}

		foreach( $sections as $section ) {
	      		add_settings_section( $section['name'], $section['title'], array(&$this, $section['name'].'_helptext'), 'octoprint');
		}

        } // END public static function activate

	public $settings_general_help = '<strong>Warning: </strong> In most cases you will need to make yoru octoprint installation accessible from the internet so Wordpress can access it. If you have a VM i recommend using a ssh tunnel.<br />
These settings are the default values for the plugin (widget and shortcode). You can override the values in the widget area or with shortcode arguments';

        public function general_helptext() { echo $this->settings_general_help; }
        public function shortcode_helptext() { echo ''; }
        public function widget_helptext() { echo ''; }

        public function text($args) { 											// This function provides text inputs for settings fields
            $field = $args['field']; 											// Get the field name from the $args array
            $value = get_option($field); 										// Get the value of this setting
            echo sprintf('<input type="text" name="%s" id="%s" value="%s" />', $field, $field, $value); 		// The input field
        } 														// END public function settings_field_input_text($args)

        public function textarea($args) { 										// This function provides textarea inputs for settings fields
            $field = $args['field']; 											// Get the field name from the $args array
            $value = get_option($field); 										// Get the value of this setting
            echo sprintf('<textarea name="%s" id="%s" cols="50" rows="5">%s</textarea>', $field, $field, $value);  	// The textarea tag
        } 														// END public function settings_field_input_textarea($args)

        public function checkbox($args) {										// This function provides checkbox inputs for settings fields
            $field = $args['field']; 											// Get the field name from the $args array
            $value = get_option($field); 										// Get the value of this setting

	    if (!empty($value)) $checked = 'checked';
	    else $value = 'true';

            echo sprintf('<input type="checkbox" name="%s" id="%s" value="%s" %s/>', $field, $field, $value, $checked);	// The checkbox tag

        } 														// END public function settings_field_checkbox($args)

        public function add_menu() { 											// Add a page to manage this plugin's settings
        	add_options_page('Octoprint Settings', 'Octoprint', 'manage_options', 'octoprint', array(&$this, 'plugin_settings_page'));
        } // END public function add_menu()

        public function plugin_settings_page() { 									// Menu Callback

        	if(!current_user_can('manage_options')) wp_die(__('You do not have sufficient permissions to access this page.'));
        	include(sprintf("%s/templates/settings.php", dirname(__FILE__))); 					// Render the settings template

        } // END public function plugin_settings_page()

    } // END class octoprintWidgetSettings

} // END if(!class_exists('octoprintWidgetSettings'))
