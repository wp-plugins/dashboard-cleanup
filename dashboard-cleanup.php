<?php
/*
Plugin Name: Dashboard Cleanup
Plugin URI: http://wordpress.org/extend/plugins/dashboard-cleanup/
Description: Remove options include wordpress.org feed, recent drafts, right now, recent comments, incoming links, plugins box, quick press. See readme.txt before activating!
Version: 1.0
Tags: remove, dashboard, admin, design, cleanup
Author: Kevin Dees
Author URI: http://kevindees.cc

/--------------------------------------------------------------------\
|                                                                    |
| License: GPL                                                       |
|                                                                    |
| Dashboard Cleanup - cleaning up the wordpress dashboard.           |
| Copyright (C) 2011, Kevin Dees,                                    |
| http://kevindees.cc                                               |
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

// protect yourself
if ( !function_exists( 'add_action') ) {
	echo "Hi there! Nice try. Come again.";
	exit;
}

class dashc {
	// when object is created
	function __construct() {
		add_action('admin_menu', array($this, 'menu')); // add item to menu
		add_action('admin_init', array($this, 'remove_dashboard_widgets')); // dashboard meta boxes
	}
	
	// make menu
	function menu() {
		add_submenu_page('index.php', 'Dashboard Cleanup', 'Cleanup', 'administrator', __FILE__,array($this, 'settings_page'), '', '');
	}
	
	// create page for output and input
	function settings_page() {
		?>
	    <div class="icon32" id="icon-options-general"><br></div>
	    <div id="dashc-page" class="wrap">
	    
	    <h2>Dashboard Cleanup</h2>
	    
	    <?php
	    // $_POST needs to be sanitized by version 1.0
	   	if($_POST['submit']) {
	   		
	   		// cleanup POST data
	   		$set_options = array();
	   		foreach($_POST as $key) {
	   			$safe_key = trim(addslashes($key));
	   			if( preg_match('/^dashc_.*/' , $safe_key) ) {
	   				$set_options[$safe_key] = $safe_key;
	   			}
	   		}
	   		
	   		update_option('dashc_options', $set_options);
	   		if($_POST['dashc_menu_icons'] != '') { 
	   			$dashc_message .= 'Menu icons removed, <b>changes will be seen on next page load<b>.'; 
	   		}
	   		echo '<div id="message" class="updated below-h2"><p>Dashboard updated. '. $dashc_message .' <a href="/wp-admin">Go to Dashboard</a></p></div>';
	   	}
	    ?>
	    
	    <form method="post" action="<?php echo esc_attr($_SERVER["REQUEST_URI"]); ?>">
	    <?php
	    // add options
	    $dashc_boxes = 
	    			array(
	    				array('Quick Press', 'dashc_quick'), 
	    				array('Plugins Box', 'dashc_plugins'), 
	    				array('Right Now', 'dashc_now'), 
	    				array('Recent Comments', 'dashc_comments'), 
	    				array('Incoming Links', 'dashc_links'), 
	    				array('Other News', 'dashc_secondary'), 
	    				array('Wordpress News', 'dashc_primary'), 
	    				array('Recent Drafts', 'dashc_drafts'),
	    				array('Admin Menu Icons', 'dashc_menu_icons')
	    				
	    			); ?>
	    <table class="form-table">
	    <?php 
	    if(get_option('dashc_options')) {
	    	$dashc_options = get_option('dashc_options');
	    }
	    else {
	    	$dashc_options = array();
	    }
	    	
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
	    </table>
	    <p class="submit">
	    <input type="submit" name="submit" class="button-primary" value="Save Changes" /></p>
	    </form>
	    
	    </div>
	    
	    <?php }
	    
	// remove icons
    function icons_css() { ?>
	    <style type="text/css">
	    #adminmenu div.wp-menu-image, .wp-menu-image { display: none; }
	    #adminmenuwrap #adminmenu > li > a { padding-left: 12px; font-weight: normal; }
	    #collapse-menu { display: none; }
	    </style>
	    <?php		
    }
    
    // remove widgets
    function remove_dashboard_widgets() {
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
    				case 'dashc_menu_icons' :
    					add_action('admin_head', array($this, 'icons_css'));   // menu icons
    					break;
    				default :
    					break;
    			} // end switch
    			$i++;
    		} // end while
    	} // end if
    } // end remove_dashboard_widgets
    
} // end dashc obj

new dashc();