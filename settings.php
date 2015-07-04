<?php

if(!class_exists('octoprintSettings')) {
    class octoprintSettings {

        // General settings for the settings page / class

        const PAGE_TITLE = 'Octoprint Settings';    // Page title
        const MENU_TITLE = 'Octoprint';             // Title in the menu
        const CAPABILITY = 'manage_options';        // Who is allowed to change settings
        const MENU_SLUG = 'octoprint';              // Also the page name
        const SETTINGS_PREFIX = 'octoprint_';

        // Add the tabs for the settings page here
        // For each tab you need to create a callback function
        // public function init_settings_tab_tabname() { $this->init_settings('tabname'); }

        private $tabs = array(
            'default'       => '<span class="dashicons dashicons-admin-settings"></span> General Settings',
        );

        // Empty array('name' => '', 'title' => '', 'tab' => ''),
        // Don't forget to specify a helptext, even if it's empty.

        private $sections = array(
            array('name' => 'widget', 	'title' =>'Widget settings', 'tab' => 'default'),
            //array('name' => 'shortcode', 	'title' =>'Shortcode settings', 'tab' => 'default'),
            array('name' => 'general', 	'title' =>'Default settings', 'tab' => 'default')
        );

        // Empty array('name' => '', 'default' => '', 'title' => '', 'type' => '', 'section' => ''),

        private $settings = array(
            array('name' => 'octoprint_url', 		    'default' => '', 'title' => 'Octoprint URL', 	'type' => 'text',	    'section' => 'general'),
            array('name' => 'octoprint_api_key', 		'default' => '', 'title' => 'Api Key', 			'type' => 'text',	    'section' => 'general'),
            array('name' => 'octoprint_media_url',		'default' => '', 'title' => 'Media/Stream URL',	'type' => 'text',       'section' => 'general'),
            array('name' => 'octoprint_widget_title', 	'default' => '', 'title' => 'Widget title', 		'type' => 'text',	    'section' => 'widget'),
            array('name' => 'octoprint_widget_text', 	'default' => '', 'title' => 'Widget free text', 	'type' => 'textarea',	'section' => 'widget'),
        );
        // Holds the helptext for the sections

        private $helptext = array(
            'general'       => 'General settings.',
            'widget'        => 'Widget settings.',
        );

        // Functions for the callbacks

        public function init_settings_tab_default()       { $this->init_settings('default');    }
        public function init_settings_tab_help()          {        }

        ///////////////////////////////////////////
        //                                       //
        //  STOP! Do not modify anything below!  //
        //                                       //
        ///////////////////////////////////////////

        public function __construct() {
            foreach($this->tabs as $tab_key => $tab_caption) {
                add_action('admin_init', array(&$this, 'init_settings_tab_'.$tab_key));
            }
            add_action('admin_menu', array(&$this, 'add_menu'));
        }
        public function getSettingsArray() {
            return $this->settings;
        }

        public function getSettingPrefix() {
            return self::SETTINGS_PREFIX;
        }

        public function getMenuSlug() {
            return self::MENU_SLUG;
        }

        // Return the helptext for a section
        public function getHelptext( $arg ) {
            echo $this->helptext[$arg['id']];
        }

        // Return the tab for the actual field
        private function getTab($field) {
            $tab = 'default';
            foreach( $this->sections as $section ) {
                if( $section['name'] == $field['section']) $tab = $section['tab'];
            }
            return $tab;
        }

        // Function called for each tab by the callback function
        private function init_settings($tab) {
            foreach ($this->settings as $field) {
                if ($tab == $this->getTab($field)) $this->setup_field($field);
            }
            foreach ($this->sections as $section) {
                if ($tab == $section['tab']) $this->setup_section($section);
            }
        }

        // Used inside render_settings, just to keep code clean.
        private function setup_field($field) {
            register_setting($this->getTab($field), self::SETTINGS_PREFIX.$field['name']);
            $title = $field['title'].' [<i>'.self::SETTINGS_PREFIX.$field['name'].'</i>]';
            add_settings_field(self::SETTINGS_PREFIX.$field['name'], $title, array(&$this, $field['type']), $this->getTab($field), $field['section'], array('field' => self::SETTINGS_PREFIX.$field['name']));
        }
        private function setup_section($section) {
            add_settings_section( $section['name'], $section['title'], array(&$this, 'getHelptext'), $section['tab']);
        }

        // These function get the option value from DB and render the field
        public function text($args) { 											// This function provides text inputs for settings fields
            $field = $args['field']; 											// Get the field name from the $args array
            $value = get_option($field); 										// Get the value of this setting
            echo sprintf('<input type="text" name="%s" id="%s" value="%s" />', $field, $field, $value); 		// The input field
        }
        public function textarea($args) { 										// This function provides textarea inputs for settings fields
            $field = $args['field']; 											// Get the field name from the $args array
            $value = get_option($field); 										// Get the value of this setting
            echo sprintf('<textarea name="%s" id="%s" cols="50" rows="5">%s</textarea>', $field, $field, $value);  	// The textarea tag
        }
        public function checkbox($args) {										// This function provides checkbox inputs for settings fields
            $field = $args['field']; 											// Get the field name from the $args array
            $value = get_option($field); 										// Get the value of this setting

            if (!empty($value)) $checked = 'checked';
            else $value = 'true';

            echo sprintf('<input type="checkbox" name="%s" id="%s" value="%s" %s/>', $field, $field, $value, $checked);	// The checkbox tag

        }

        // Register the menu in WordPress
        public function add_menu() {
            add_options_page(self::PAGE_TITLE, self::MENU_TITLE, self::CAPABILITY, self::MENU_SLUG, array(&$this, 'plugin_settings_page'));
        }

        // Gives out the settings page with echo
        public function display_options() {
            $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'default';
            echo '<div class="wrap">';
            $this->plugin_options_tabs();
            if($tab == 'help') {
                include_once(sprintf( "%s/help.php", dirname(__FILE__) ));
            }
            else {
                echo '<form method="post" action="options.php">';
                wp_nonce_field( 'update-options' );
                settings_fields( $tab );
                do_settings_sections( $tab );
                submit_button();
                echo '</form>';
            }
            echo '</div>';
        }

        // Gives out the tab-navbar with echo
        public function plugin_options_tabs() {
            $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'default';

            echo '<h2>'.self::PAGE_TITLE.'</h2>';
            echo '<h2 class="nav-tab-wrapper">';
            foreach ( $this->tabs as $tab_key => $tab_caption ) {
                $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
                echo '<a class="nav-tab ' . $active . '" href="?page=' . self::MENU_SLUG . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
            }
            echo '</h2>';
        }

        // Checks if the user is allowed to acces the page
        // and display the page with display_options()
        public function plugin_settings_page() { 									// Menu Callback
            if(!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
            $this->display_options();
        }

    }
}
