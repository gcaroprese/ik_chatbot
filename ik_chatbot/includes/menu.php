<?php
/*

WP Menu | IK Chatbot
Created: 01/23/2024
Update: 01/23/2024
Author: Gabriel Caroprese | inforket.com

*/

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

//Menu constants
define('IK_CHATBOT_VAL_CONFIG', "ik_chatbot_config_page");

// I add menus on WP-admin considering user roles
function ik_chatbot_wpmenu(){
    // Add main menu item with submenus
    add_menu_page(__( 'IK Chatbot', 'ik_chatbot'), __( 'IK Chatbot', 'ik_chatbot'), 'manage_options', IK_CHATBOT_VAL_CONFIG, 'ik_chatbot_config_page', 'dashicons-format-chat' );
}
add_action('admin_menu', 'ik_chatbot_wpmenu', 999);


//Function to add config panel content
function ik_chatbot_config_page(){
    include (IK_CHATBOT_DIR.'/includes/templates/messages.php');
}

?>