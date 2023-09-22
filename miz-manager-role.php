<?php
/**
 * Plugin Name: Manager User Role
 * Plugin URI: https://gitlab.com/university-of-missouri/mizzou-digital/wordpress/wp-plugins/miz-manager-role
 * Description: WordPress Plugin that adds the Manager User Role
 * Version: 0.1.0
 * Author: Travis Cook, Digital Service, University of Missouri
 * Author URI: https://digitalservice.missouri.edu
 *
 * @package WordPress
 * @subpackage Mizzou Manager User Role
 * @category plugin
 * @category functions
 * @category Users
 * @author Travis Cook (cooktw@missouri.edu), Digital Service, University of Missouri
 * @copyright 2023 Curators of the University of Missouri
 * @version 0.1.0
 */

register_activation_hook( __FILE__, 'mizzouAddManagerRole' );

/**
 * Adds a custom role of Manager to WordPress' roles
 */
function mizzouAddManagerRole() {
	// we dont want to add the manager role if it already exists.
	if ( is_null( $obj_manager = get_role( 'manager' ) ) ) {
		$ary_new_caps = array(
			'edit_users',
			'list_users',
			'promote_users',
			'create_users',
			'add_users',
			'delete_users',
			'remove_users',
		);

		// get the editor role so we can clone it.
		$obj_editor_role = get_role( 'editor' );

		// create our new role with the same caps as editor.
		$obj_manager_role = add_role( 'manager', 'Manager', $obj_editor_role->capabilities );

		if ( ! is_null( $obj_manager_role ) && $obj_manager_role instanceof WP_Role ) {
			// now let's add on our extra caps.
			foreach ( $ary_new_caps as $str_new_cap ) {
				$obj_manager_role->add_cap( $str_new_cap );
			}
		} else {
			// what happened?
		}
	}
}
