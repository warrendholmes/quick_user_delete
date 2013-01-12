<?php
/*
Plugin Name: Quick User Delete.
Plugin URI: http://warrenholmes.co.za
Description: Quickly delete a user. Does not work on MS yet. Reason - http://core.trac.wordpress.org/ticket/19867
Version: 1.0.0
Author: Warren Holmes
Author URI: http://warrenholmes.co.za/
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
/*  Copyright 2012  WooThemes  (email : info@woothemes.com)

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
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//add action to create the delete link on user listings
add_filter( 'user_row_actions', 'quick_user_delete_link', 10, 2 );

//hook onto admin_init for the delete
add_action( 'admin_init', 'do_quick_delete_user' );

//add javascript
add_action( 'admin_enqueue_scripts', 'enqueue_styles_scripts' );

function quick_user_delete_link( $actions, $user ){
    $actions['quick_delete'] = '<a href="' . create_quick_user_delete_link( $user->ID ) . '" class="quick_user_delete">' . __( 'Quick&nbsp;Delete', 'quick_user_delete' ) . '</a>';
    return $actions;
}

//Code which creates the link. Can be used anywhere
function create_quick_user_delete_link( $user_id ){
    return wp_nonce_url( add_query_arg( array(
            'action'  => 'do_quick_delete_user',
            'user_id' => $user_id
        ), admin_url() ), "do_quick_delete_user_{$user_id}" );
}

function enqueue_styles_scripts(){
    
    $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    wp_enqueue_script( 'quick_user_delete', plugin_dir_url( __FILE__ ) . 'js/admin' . $suffix . '.js', array(), 1.0 );

}

//Code to actually delete a user
function do_quick_delete_user(){

    global $current_user;
    
    if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'do_quick_delete_user' ) {
        
        $redirect = 'users.php';

        if ( is_multisite() )
            wp_die( __('User deletion is not currently possible with this plugin.') );

        if ( empty($_REQUEST['user_id']) ) {
            wp_redirect( admin_url() );
            exit();
        }

        if ( ! current_user_can( 'delete_users' ) )
            wp_die(__('You can&#8217;t delete users.'));

        $user_id = $_REQUEST['user_id'];
        $update = 'del';
        $delete_count = 0;

        check_admin_referer("do_quick_delete_user_{$user_id}");

        $id = (int) $user_id;

        if ( ! current_user_can( 'delete_user', $id ) )
            wp_die(__( 'You can&#8217;t delete that user.' ) );

        if ( $id == $current_user->ID )
            $update = 'err_admin_del';
        
        if ( current_user_can('delete_user', $id) )
            wp_delete_user($id);
        
        ++$delete_count;

        $redirect = add_query_arg( array( 'delete_count' => $delete_count, 'update' => $update ), $redirect );
        wp_redirect( $redirect );
        exit();

            
        }
}