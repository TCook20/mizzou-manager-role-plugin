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

/**
 * Helper function get getting roles that the user is allowed to create/edit/delete.
 *
 * @param   WP_User $user
 * @return  array
 */
function mizzouGetAllowedRoles( $user ) {
	$allowed = array();

	if ( in_array( 'administrator', $user->roles ) ) { // Admin can edit all roles.
		$allowed = array_keys( $GLOBALS['wp_roles']->roles );
	} elseif ( in_array( 'manager', $user->roles ) ) {
		$allowed[] = 'super-editor';
		$allowed[] = 'editor';
		$allowed[] = 'contributor';
		$allowed[] = 'subscriber';
	}

	return $allowed;
}

/**
 * Remove roles that are not allowed for the current user role.
 */
function mizzouEditRoles( $roles ) {
	if ( $user = wp_get_current_user() ) {
		$allowed = mizzouGetAllowedRoles( $user );

		foreach ( $roles as $role => $caps ) {
			if ( ! in_array( $role, $allowed ) ) {
				unset( $roles[ $role ] );
			}
		}
	}

	return $roles;
}

add_filter( 'editable_roles', 'mizzouEditRoles' );

/**
 * Prevent users deleting/editing users with a role outside their allowance.
 */
function mizzouCapUserRoles( $caps, $cap, $user_ID, $args ) {
	if ( ( $cap === 'edit_user' || $cap === 'delete_user' ) && $args ) {
		$the_user = get_userdata( $user_ID ); // The user performing the task.
		$user     = get_userdata( $args[0] ); // The user being edited/deleted.

		if ( $the_user && $user && $the_user->ID != $user->ID /* User can always edit self */ ) {
			$allowed = mizzouGetAllowedRoles( $the_user );

			if ( array_diff( $user->roles, $allowed ) ) {
				// Target user has roles outside of our limits.
				$caps[] = 'not_allowed';
			}
		}
	}

	return $caps;
}

add_filter( 'map_meta_cap', 'mizzouCapUserRoles', 10, 4 );
