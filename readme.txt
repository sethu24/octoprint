=== Plugin Name ===
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

== Description ==

This plugin polls the Octoprint API and displays the status of your 3D printer in a widget or on a page with a shortcode.

== Installation ==

1. Activate the API in the Octoprint settings
2. Copy the API key in the settings fields.
3. Set the URL to access Octoprint, without slash at the end (ex: http://localhost:5000)

If Octoprint is not running on the same server than Wordpress you will need to make Ocotprint accessible for this server. If it is a VM i recommend using a SSH tunnel.

== Screenshots ==

== Changelog ==

= 1.0 =
* Almost complete rewrite fo the code.

= 0.2 =
* Shortcode [octorprint] is now working.
* Code optimization.

= 0.1 =
* First working version. Only displays the printer's state.
