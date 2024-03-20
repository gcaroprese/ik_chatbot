<?php
/*

Ajax Functions | IK Chatbot
Created: 01/23/2024
Update: 01/23/2024
Author: Gabriel Caroprese | inforket.com

*/

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

//ajax to delete message 
add_action('wp_ajax_ik_chatbot_ajax_delete_message', 'ik_chatbot_ajax_delete_message');
function ik_chatbot_ajax_delete_message(){
    if (isset($_POST['iddata'])){
        $chatbot = new Ik_Chatbot();
        $message_id = intval($_POST['iddata']);
        $chatbot->delete($message_id);
    }
    wp_send_json(true);
    wp_die();         
}

//ajax to get messages and give a response
add_action('wp_ajax_nopriv_ik_chatbot_ajax_response', 'ik_chatbot_ajax_response');
add_action('wp_ajax_ik_chatbot_ajax_response', 'ik_chatbot_ajax_response');
function ik_chatbot_ajax_response() {
    if (isset($_POST['message'])) {
        $chatbot = new Ik_Chatbot();
        $chatbot_response = $chatbot->get_response($_POST['message']);

        wp_send_json($chatbot_response);
    }
    wp_die();
}

//ajax to generate table to export logs to csv
add_action( 'wp_ajax_ik_chatbot_ajax_get_full_log_table', 'ik_chatbot_ajax_get_full_log_table');
function ik_chatbot_ajax_get_full_log_table(){
    $Ik_Chatbot = new Ik_Chatbot();

    $table_log_data = $Ik_Chatbot->get_log_table_export_data();

    echo json_encode( $table_log_data );
    wp_die();         
}