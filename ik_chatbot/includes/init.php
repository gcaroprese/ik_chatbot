<?php
/*

Init Functions | IK Chatbot
Created: 01/23/2024
Update: 01/23/2024
Author: Gabriel Caroprese | inforket.com

*/

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

//Required includes
require_once(IK_CHATBOT_DIR . '/includes/menu.php');
require_once(IK_CHATBOT_DIR . '/includes/ik_chatbot_class.php');
require_once(IK_CHATBOT_DIR . '/includes/functions.php');
require_once(IK_CHATBOT_DIR . '/includes/ajax.php');
require_once(IK_CHATBOT_DIR . '/includes/shortcode.php');

//function to create tables in DB
function ik_chatbot_create_tables() {
	$chatbot = new Ik_Chatbot();
	$chatbot->create_db_tables();
}

//I add style and scripts from plugin to dashboard
function ik_chatbot_add_css_js(){
	wp_register_style( 'ik_chatbot_css', IK_CHATBOT_PUBLIC . 'css/style_backend.css', false, '1.1.7', 'all' );
	wp_enqueue_style('ik_chatbot_css');
}
add_action( 'admin_enqueue_scripts', 'ik_chatbot_add_css_js' );

//I add style and scripts from plugin to frontend
function ik_chatbot_add_css_js_frontend(){
	if ( ! wp_script_is( 'jquery', 'enqueued' )) {
		wp_enqueue_script( 'jquery' );
	}
}
add_action( 'wp_enqueue_scripts', 'ik_chatbot_add_css_js_frontend' );

//show chatbot on footer
add_action('wp_footer', 'ik_chatbot_show_chatbot');
function ik_chatbot_show_chatbot() {
	echo do_shortcode('[ik_chatbot]');
}

?>