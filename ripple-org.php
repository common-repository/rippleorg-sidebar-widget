<?php
/*
Plugin Name: Ripple.org Sidebar Widget
Plugin URI: http://bites-n-pieces.com/blog/projects/rippleorg-sidebar-widget/
Description: Sidebar widget to display random Splashponds (buttons) from <a href="http://ripple.org">Ripple.org</a>
Version: 0.5.1
Author: Ping
Author URI: http://bites-n-pieces.com/blog/
*/

/*	Copyright (c) 2008

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
if ( !defined( 'WP_CONTENT_URL' ) )
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );

if ( !defined( 'WP_PLUGIN_URL' ) )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );

// Define the plugin content url
define("RPL_PLUGIN_URL", WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__) ) . "/");

function widget_RippleOrg_activate() {

	include_once('ripple-org-common.php');
	
	// INITIALISE DATABASE
	$sql = "CREATE TABLE " . $wp_ripple_db . "  (
		category 		VARCHAR(30) 	NOT NULL,
		clicks 			INT UNSIGNED 	NOT NULL 	default 0,
		lastclicked		DATETIME		NOT NULL,
		PRIMARY KEY (category) );";

	// create the table
	if($wpdb->get_var("show tables like '$wp_ripple_db'") != $wp_ripple_db) {
		$wpdb->query($sql);
	}
	
	$date = date('Y-m-d H:i:s');
	
	for ( $i = 0; $i < count($buttons); $i++) {
		$query_check = sprintf("SELECT category FROM %s WHERE category = '%s';" 
			, $wpdb->escape($wp_ripple_db)
			, $wpdb->escape(strtoupper($buttons[$i])));

		$isPopulated = $wpdb->get_results($query_check);
		
		if (empty($isPopulated)) {
			$query_ins = sprintf("INSERT INTO %s (category, clicks, lastclicked) VALUES ('%s', %s, '%s');"
				, $wpdb->escape($wp_ripple_db)
				, $wpdb->escape(strtoupper($buttons[$i]))
				, 0
				, $date);
			$wpdb->query($query_ins);			
		}
	}
	
	// INITIALISE OPTIONS
	// Get options
	$defaultOptions = array(
		'title'=>'Support'
		, 'singleclicknoun'=>'click'
		, 'pluralclicknoun'=>'clicks'
		, 'displaytext'=>'<div style="text-align: center; width: 172px; margin-top: -5px">%clickcount% %clicknoun% so far!</div>'
		, 'beforebutton' => ''
		, 'afterbutton' => '');
	$options = get_option('widget_RippleOrg');
	
	// options exist? if not set defaults
	if ( !is_array($options) ) {
		update_option('widget_RippleOrg', $defaultOptions);
	} else {
		// make sure the all the current available options are there
		foreach ($options as $i => $value) {
			$defaultOptions[$i] = $options[$i];
		}
		update_option('widget_RippleOrg', $defaultOptions);
	}

}

function widget_RippleOrg_init() {
	
	if (!function_exists('register_sidebar_widget')) return;
	
	function widget_RippleOrg($args) {
		include_once('ripple-org-common.php');
		
		// Extract title from options
		extract($args);
		$options = get_option('widget_RippleOrg');
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$singleClickNoun = htmlspecialchars($options['singleclicknoun'], ENT_QUOTES);
		$pluralClickNoun = htmlspecialchars($options['pluralclicknoun'], ENT_QUOTES);
		//$displayText = htmlspecialchars($options['displaytext'], ENT_QUOTES);
		$displayText = htmlspecialchars_decode($options['displaytext']);
		$beforeButton = htmlspecialchars_decode($options['beforebutton'], ENT_QUOTES);
		$afterButton = htmlspecialchars_decode($options['afterbutton'], ENT_QUOTES);
		
		// Randomly pick button for display		
		srand(time());
		$random = (rand()%(count($buttons)));
		$selButton =$buttons[$random];
		
		if ($wpdb) {	// hmm throwing error in the widget setup
			$clickcount = $wpdb->get_var("select sum(clicks) from $wp_ripple_db");
			$clickNoun = $singleClickNoun;
			if ($clickcount <> 1) 
				$clickNoun = $pluralClickNoun;

			$displayText = str_replace("%clicknoun%", $clickNoun, $displayText);
			$displayText = str_replace("%clickcount%", "$clickcount", $displayText);
			//$displayText = htmlspecialchars_decode($displayText);
		}

		// Render widget
		echo $before_widget . $before_title	. $title . $after_title;
		
		echo $beforeButton;
?>
		<p>
			<a target="_blank" 
				href=" <?php echo $wp_ripple_root; ?>ripple-org-go.php?cat=<?php echo strtolower($selButton); ?>">
				<img src="<?php echo RPL_PLUGIN_URL; ?>images/<?php echo strtolower($selButton); ?>.gif" 
				alt="Give <?php echo $selButton; ?>" width="172" height="203" border="0"  />
			</a><br/>
			<?php echo $displayText; ?>
		</p>
<?php
		echo $afterButton;
		echo $after_widget;
	}
	
	function widget_RippleOrg_control() {
 
		// Get options
		$options = get_option('widget_RippleOrg');
			
        // form posted?
		if ( $_POST['RippleOrg-submit'] ) {
			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['RippleOrg-title']));
			$options['singleclicknoun'] = strip_tags(stripslashes($_POST['RippleOrg-singleclicknoun']));
			$options['pluralclicknoun'] = strip_tags(stripslashes($_POST['RippleOrg-pluralclicknoun']));
			$options['displaytext'] = stripslashes($_POST['RippleOrg-displaytext']);
			$options['beforebutton'] = stripslashes($_POST['RippleOrg-beforebutton']);
			$options['afterbutton'] = stripslashes($_POST['RippleOrg-afterbutton']);
			update_option('widget_RippleOrg', $options);
		}
		
		// Get options for form fields to show
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$singleClickNoun = htmlspecialchars($options['singleclicknoun'], ENT_QUOTES);
		$pluralClickNoun = htmlspecialchars($options['pluralclicknoun'], ENT_QUOTES);
		$displayText = htmlspecialchars($options['displaytext'], ENT_QUOTES);
		$beforeButton = htmlspecialchars($options['beforebutton'], ENT_QUOTES);
		$afterButton = htmlspecialchars($options['afterbutton'], ENT_QUOTES);
		
?>
		<!-- The form field -->
		<p style="text-align:left;">
				<label for="RippleOrg-title"> <?php echo __('Title:'); ?></label>
				<input style="width: 200px;" id="RippleOrg-title" name="RippleOrg-title" type="text" value="<?php echo $title; ?>" />
				<br/><label for="RippleOrg-singleclicknoun"> <?php echo __('Singular noun, e.g. click:'); ?></label>
				<input style="width: 200px;" id="RippleOrg-singleclicknoun" name="RippleOrg-singleclicknoun" type="text" value="<?php echo $singleClickNoun; ?>" />
				<br/><label for="RippleOrg-pluralclicknoun"> <?php echo __('Plural noun, e.g. clicks:'); ?></label>
				<input style="width: 200px;" id="RippleOrg-pluralclicknoun" name="RippleOrg-pluralclicknoun" type="text" value="<?php echo $pluralClickNoun; ?>" />
				<br/><label for="RippleOrg-displaytext"> <?php echo __('Display Text, e.g. %clickcount% %clicknoun% so far!:'); ?></label>
				<input style="width: 200px;" id="RippleOrg-displaytext" name="RippleOrg-displaytext" type="text" value="<?php echo $displayText; ?>" />
				<br/><label for="RippleOrg-beforebutton"> <?php echo __('Text before image, e.g. &lt;center&gt;:'); ?></label>
				<input style="width: 200px;" id="RippleOrg-beforebutton" name="RippleOrg-beforebutton" type="text" value="<?php echo $beforeButton; ?>" />
				<br/><label for="RippleOrg-afterbutton"> <?php echo __('Text after image, e.g. &lt;/center&gt;:'); ?></label>
				<input style="width: 200px;" id="RippleOrg-afterbutton" name="RippleOrg-afterbutton" type="text" value="<?php echo $afterButton; ?>" />
				
				</p>
		<input type="hidden" id="RippleOrg-submit" name="RippleOrg-submit" value="1" />
<?php
	}
	
	// Register widget for use
	register_sidebar_widget(array('Ripple.org', 'widgets'), 'widget_RippleOrg');
	// Register settings for use, 300x100 pixel form
	register_widget_control(array('Ripple.org', 'widgets'), 'widget_RippleOrg_control');
}

// *** ACTIVATION  ***
register_activation_hook(__FILE__, 'widget_RippleOrg_activate');

// *** INITIALISE WIDGET ***
add_action('plugins_loaded', 'widget_RippleOrg_init');

?>