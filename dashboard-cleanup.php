<?php
/*
Plugin Name: Dashboard Cleanup
Description: Remove options include wordpress.org feed, recent drafts, right now, recent comments, incloming links, plugins box, quick press. See readme.txt before activating!
Version: 0.2
Author: Kevin Dees
Author URI: http://kevindees.cc
*/

/*
/--------------------------------------------------------------------\
|                                                                    |
| License: GPL                                                       |
|                                                                    |
| Dashboard Cleanup - cleaning up the wordpress dashboard.                  |
| Copyright (C) 2011, Kevin Dees,                              |
| http://kevindees.ccm                                    |
| All rights reserved.                                               |
|                                                                    |
| This program is free software; you can redistribute it and/or      |
| modify it under the terms of the GNU General Public License        |
| as published by the Free Software Foundation; either version 2     |
| of the License, or (at your option) any later version.             |
|                                                                    |
| This program is distributed in the hope that it will be useful,    |
| but WITHOUT ANY WARRANTY; without even the implied warranty of     |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
| GNU General Public License for more details.                       |
|                                                                    |
| You should have received a copy of the GNU General Public License  |
| along with this program; if not, write to the                      |
| Free Software Foundation, Inc.                                     |
| 51 Franklin Street, Fifth Floor                                    |
| Boston, MA  02110-1301, USA                                        |   
|                                                                    |
\--------------------------------------------------------------------/
*/

register_activation_hook(__FILE__, 'dashc_install');
register_deactivation_hook(__FILE__, 'dashc_uninstall');

function dashc_install() {
	dashc_uninstall();					   
}

function dashc_uninstall() {
	global $wp_version;
	if(version_compare($wp_version,"3.0", "<")) {
		wp_die("This plugin requires the jacked-up 3.0 version of WordPress; or higher!"); }
	deactivate_plugins(basename(__FILE__));
}

/**
 * ADD PLUGIN PAGE
 * This adds the menu item to the dashboard menu. It also
 * creates the page for the settings. 
 */
add_action('admin_menu', 'dashc_menu');

// add menu to admin
function dashc_menu() {
	add_submenu_page('index.php', 'Dashboard Cleanup','Dashboard Cleanup','administrator',__FILE__,'dashc_settings_page','','');
	add_action('admin_init','dashc_register_settings'); }

// add settings for db
function dashc_register_settings() {
	register_setting('dashc_settings_group', 'dashc_options'); }

// create page for output and input
function dashc_settings_page() {
?>
    <div class="icon32" id="icon-options-general"><br></div>
    <div id="dashc-page" class="wrap">
    
    <?php
    	$options = $_POST;
    	foreach($options as $key) {
    		echo $dashc_options[$key];
    	}
    	update_option('dashc_options', $_POST);
    ?>
    
    <h2><?php _e('Dashboard Cleanup', 'dashboard-cleanup'); ?></h2>
    <form method="post" action="<?php echo esc_attr($_SERVER["REQUEST_URI"]); ?>">
    <?php settings_fields('dashc_settings_group');
	
    $dashc_boxes = 
    			array(
    				array('Quick Press', 'dashc_quick'), 
    				array('Plugins Box', 'dashc_plugins'), 
    				array('Right Now', 'dashc_now'), 
    				array('Recent Comments', 'dashc_comments'), 
    				array('Incoming Links', 'dashc_links'), 
    				array('Other News', 'dashc_secondary'), 
    				array('Wordpress News', 'dashc_primary'), 
    				array('Recent Drafts', 'dashc_drafts')
    			); ?>
    <table class="form-table">
    <?php 
    $dashc_options = get_option('dashc_options');
    $i = 0;
   	while($dashc_boxes[$i]) { ?>
    <tr>
    <th><label for="<?php print $dashc_boxes[$i][1]; ?>">Disable <?php print $dashc_boxes[$i][0]; ?></label></th>
    <td>
    <?php 
    $dashc_meta_selected = ''; 
    
    if($dashc_options[$dashc_boxes[$i][1]])
    	{ $dashc_meta_selected = 'checked="checked"'; } 
    ?>
    <input 	id="<?php print $dashc_boxes[$i][1]; ?>" 
    		type="checkbox" 
    		value="<?php print $dashc_boxes[$i][1]; ?>" 
    		name="<?php print $dashc_boxes[$i][1]; ?>" 
    		<?php echo $dashc_meta_selected; ?> />
    </td>
    </tr>	
   <?php $i++; } ?>
        
    <tr>
    <td colspan="2">This meta boxes from the dashboard.</td>
    </tr>
    </table>
    <p class="submit">
    <input type="submit" name="submit" class="button-primary" value="Save Changes" /></p>
    </form>
    
    </div>
    
    <?php }

/**
 * REMOVE UNWANTED WORDPRESS STUFF
 * This will remove dashboard boxes you might not want. Edit 
 * at will and your own risk. 
 */
add_action('admin_init', 'dashc_remove_dashboard_widgets'); // dashboard meta boxes

function dashc_remove_dashboard_widgets() {
	$dashc_options = get_option('dashc_options');
	if($dashc_options) {
	foreach($dashc_options as $key) {
		switch($key) {
			case 'dashc_now' :
				remove_meta_box('dashboard_right_now', 'dashboard', 'normal');   // right now
				break;
			case 'dashc_comments' : 
				remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'); // recent comments
				break;
			case 'dashc_links' : 
				remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');  // incoming links
				break;
			case 'dashc_plugins' :
				remove_meta_box('dashboard_plugins', 'dashboard', 'normal');   // plugins
				break;
			case 'dashc_quick' :
				remove_meta_box('dashboard_quick_press', 'dashboard', 'normal');  // quick press
				break;
			case 'dashc_drafts' :
				remove_meta_box('dashboard_recent_drafts', 'dashboard', 'normal');  // recent drafts
				break;
			case 'dashc_primary' :
				remove_meta_box('dashboard_primary', 'dashboard', 'normal');   // wordpress blog
				break;
			case 'dashc_secondary' :
				remove_meta_box('dashboard_secondary', 'dashboard', 'normal');   // other wordpress news
				break;
		} // end switch
		$i++;		
	} // end while	
	} // end if
} // end function

/**
 * ADD CSS TO PLUGIN PAGE
 * this code will add design styles to the meta
 * boxes you create for your custom fields. You
 * can add your own here.
 */

// Add Action
add_action('admin_head', 'dashc_css');

// custom fields styles
function dashc_css() { ?>
<style type="text/css">
.dashc_css p label {
	display: block;
	padding: 3px;
	font-weight: bold;
	margin-top: 3px;}

.dashc_css p input {
	width: 100%;}

#advanced_meta {
	background: #FFC;	
}
</style>
<?php		
}