<?php
/*
Plugin Name: IK Chatbot Plugin
Description: Answer questions with a chatbot
Version: 1.1.1
Author: Gabriel Caroprese | Inforket.com
Author URI: https://inforket.com/
Requires at least: 5.3
Requires PHP: 7.3
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$ik_chatbotDir = dirname( __FILE__ );
$ik_chatbotPublicDir = plugin_dir_url(__FILE__ );
define( 'IK_CHATBOT_DIR', $ik_chatbotDir);
define( 'IK_CHATBOT_PUBLIC', $ik_chatbotPublicDir);

require_once($ik_chatbotDir . '/includes/init.php');
register_activation_hook( __FILE__, 'ik_chatbot_create_tables' );

//I add a text domain for translations
function ik_chatbot_textdomain_init() {
    load_plugin_textdomain( 'ik_chatbot', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'ik_chatbot_textdomain_init' );