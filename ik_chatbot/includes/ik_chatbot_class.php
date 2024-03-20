<?php
/*

Chatbot Class | IK Chatbot
Created: 01/23/2024
Update: 02/02/2024
Author: Gabriel Caroprese | inforket.com

*/

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

class Ik_Chatbot{
    
    private $db_table_messages; //table for messages and questions
    private $db_table_logs; //table for logs of chats
    private $db_table_affirneg; // table to consider affirmation or negatives to evaluate Q and A
    private $admin_url; //Messages admin URL
    

    public function __construct() {

        global $wpdb;
        $this->db_table_messages = $wpdb->prefix . "ik_chatbot_answers";
        $this->db_table_logs = $wpdb->prefix . "ik_chatbot_log";
        $this->db_table_affirneg = $wpdb->prefix . "ik_chatbot_affneg";
        $this->admin_url = get_admin_url().'admin.php?page='.IK_CHATBOT_VAL_CONFIG;
    }

    //Get answers table name
    public function get_answers_table(){
        return $this->db_table_messages;
    }

    //Get logs table name
    public function get_logs_table(){
        return $this->db_table_logs;
    }

    //Get affirmation and negatives table name
    public function get_affirmations_table(){
        return $this->db_table_affirneg;
    }

    //Get affirmation and negatives table name
    public function get_admin_url(){
        return $this->admin_url;
    }

    //If not exist create the tables
    public function create_db_tables(){
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $sql_answers_table = "
        CREATE TABLE IF NOT EXISTS ".$this->get_answers_table()." (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            language varchar(6) NOT NULL,
            answer longtext NOT NULL,
            question longtext NOT NULL,
            keywords longtext NOT NULL,
            date_added datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            date_edited datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            UNIQUE KEY id (id)
        ) ".$charset_collate.";";
        dbDelta( $sql_answers_table );

        $sql_logs_table = "
        CREATE TABLE IF NOT EXISTS ".$this->get_logs_table()." (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            language varchar(6) NOT NULL,
            text_sent longtext NOT NULL,
            answer_given longtext NOT NULL,
            ip varchar(39) NOT NULL,
            date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            UNIQUE KEY id (id)
        ) ".$charset_collate.";";
        dbDelta( $sql_logs_table );

        $sql_affirneg_table = "
        CREATE TABLE IF NOT EXISTS ".$this->get_affirmations_table()." (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            language varchar(6) NOT NULL,
            keyword varchar(255) NOT NULL,
            type longtext NOT NULL,
            date_added datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            UNIQUE KEY id (id)
        ) ".$charset_collate.";";
        dbDelta( $sql_affirneg_table );
    }

    //sanitize chat question and answers data
    private function sanitize_chat_args($args){

        //for answers with links
        $allowed_tags = array(
            'a' => array(
                'href' => array(),
                'title' => array()
            ),
            'br' => array(),
            'em' => array(),
            'strong' => array()
        );
        
        $args_sanitized['message_id'] = (isset($args['message_id'])) ? intval($args['message_id']) : 0;
        $question = (isset($args['question'])) ? sanitize_text_field($args['question']) : '';
        $question = (isset($args['question'])) ? sanitize_text_field($args['question']) : '';
        $args_sanitized['question'] = str_replace('\\', '', $question);
        $lang = (isset($args['lang'])) ? sanitize_text_field($args['lang']) : 'es';
        $args_sanitized['lang'] = ($lang != 'en') ? 'es' : 'en';
        $answer = (isset($args['answer'])) ? wp_kses($args['answer'], $allowed_tags) : '';
        $args_sanitized['answer'] = str_replace('\\', '', $answer);
        $question_keyword = (isset($args['question_keyword'])) ? sanitize_textarea_field($args['question_keyword']) : '';
        $question_keyword = str_replace('\\', '', $question_keyword);
        $question_keyword = str_replace('\'\'**', '', $question_keyword);
        $args_sanitized['question_keyword'] = "''**".str_replace(array("\r\n", "\r", "\n"), "''**", $question_keyword);

        return $args_sanitized;
    }

    //create possible answers and questions
    public function create_chats($args = array()){
    
        $args = $this->sanitize_chat_args($args);

        if ($args['question'] != ''){
            $data_insert = array (
                'question'	    => $args['question'],
                'language'      => $args['lang'],
                'date_added'    => current_time('mysql'),
                'date_edited'   => current_time('mysql'),
                'answer'        => $args['answer'],
                'keywords'      => $args['question_keyword'],
            );
            
            global $wpdb;
            $rowResult = $wpdb->insert($this->db_table_messages, $data_insert, NULL);   
            $message_id = $wpdb->insert_id;
            
            //return the created message id
            return $message_id;
        }
        
        return false;
    }

    //method to edit chats
    public function edit_chat($args = array()){
        
        if (isset($args['message_id'])){        
            $args = $this->sanitize_chat_args($args);
            $message_id = intval($args['message_id']);

            if ($args['question'] != ''){
                
                $data_update = array (
                    'question'	    => $args['question'],
                    'language'      => $args['lang'],
                    'date_edited'   => current_time('mysql'),
                    'answer'        => $args['answer'],
                    'keywords'      => $args['question_keyword'],
                );
    
                global $wpdb;
                $where = [ 'id' => $message_id ];
                $rowResult = $wpdb->update($this->db_table_messages, $data_update, $where);

                return $message_id;
            }
        }
        
        return false;
    }

    
    //Get chat message by ID
    public function get_by_id($message_id = 0){
        $message_id = absint($message_id);
        
        if ( $message_id > 0){
            
            global $wpdb;
            $query = "SELECT * FROM ".$this->db_table_messages." WHERE id = ".$message_id;
            $message = $wpdb->get_results($query);
    
            if (isset($message[0]->id)){ 
                return $message[0];
            }
        }
        
        return false;
    }

    //Get all messages data
    public function get_messages(){
                
        global $wpdb;
        $query = "SELECT * FROM ".$this->db_table_messages." ORDER BY id ASC";
        $messages = $wpdb->get_results($query);

        if (isset($messages[0]->id)){ 
            return $messages;
        }
        
        return false;
    }

    //Get all logs data
    public function get_logs(){
            
        global $wpdb;
        $query = "SELECT * FROM ".$this->db_table_logs." ORDER BY id DESC";
        $logs = $wpdb->get_results($query);

        if (isset($logs[0]->id)){ 
            return $logs;
        }
        
        return false;
    }

    
    //Count the quantity of message records
    public function qty_records(){

        //make sure is not search
        if (isset($_GET['search'])){
            $search = sanitize_text_field($_GET['search']);
            $where = " WHERE keywords LIKE '%".$search."%' OR answer LIKE '%".$search."%' OR question LIKE '%".$search."%'";
        } else {
            $where = "";
        }

        global $wpdb;
        $query = "SELECT * FROM ".$this->db_table_messages.$where;
        $result = $wpdb->get_results($query);

        if (isset($result[0]->id)){ 
            
            $count_result = count($result);

            return $count_result;
            
        } else {
            return false;
        }
    }

    //List messages
    private function get_messages_list($qty = 30){
        $qty = absint($qty);

        if (isset($_GET["list"])){
            // I check if value is integer to avoid errors
            if (strval($_GET["list"]) == strval(intval($_GET["list"])) && $_GET["list"] > 0){
                $page = intval($_GET["list"]);
            } else {
                $page = 1;
            }
        } else {
             $page = 1;
        }

        $offset = ($page - 1) * $qty;

        if (isset($_GET['search'])){
            $search = sanitize_text_field($_GET['search']);
        } else {
            $search = NULL;
        }
        
        // Chechking order
        if (isset($_GET["orderby"]) && isset($_GET["orderdir"])){
            $orderby = sanitize_text_field($_GET["orderby"]);
            $orderdir = sanitize_text_field($_GET["orderdir"]);  
            if (strtoupper($orderdir) != 'DESC'){
                $orderDir= ' ASC';
                $orderClass= 'sorted asc';
            } else {
                $orderDir = ' DESC';
                $orderClass= 'sorted desc';
            }
        } else {
            $orderby = 'id';
            $orderDir = 'ASC';
            $orderClass= 'sorted asc';
        } 
        if (is_int($offset)){
            $offsetList = ' LIMIT '.$qty.' OFFSET '.$offset;
        } else {
            $offsetList = ' LIMIT '.absint($qty);
        }
        
        //Values to order filters CSS classes
        $empty = '';
        $idClass = $empty;
        $questionClass = $empty;
        $languageClass = $empty;
    
        
        if ($orderby != 'id'){	
            if ($orderby == 'question'){
                $orderQuery = ' ORDER BY '.$this->db_table_messages.'.question '.$orderDir;
                $questionClass = $orderClass;
            } else {
                $orderQuery = ' ORDER BY '.$this->db_table_messages.'.language '.$orderDir;
                $languageClass = $orderClass;
            }
        } else {
            $orderQuery = ' ORDER BY '.$this->db_table_messages.'.id '.$orderDir;
            $idClass = $orderClass;
        }

        $classData = array(
            'id' => $idClass,
            'question' => $questionClass,
            'language' => $languageClass,
        );

        if ($search != NULL){ 
            $where = " WHERE keywords LIKE '%".$search."%' OR answer LIKE '%".$search."%' OR question LIKE '%".$search."%'";
        } else {
            $where = "";
            $search = '';
        }

        $groupby = (isset($groupby)) ? $groupby : " GROUP BY ".$this->db_table_messages.".id ";

        global $wpdb;

        $query = "SELECT * FROM ".$this->db_table_messages." ".$where.$groupby.$orderQuery.$offsetList;

        $messages = $wpdb->get_results($query);
        $messages_data = array(
            'data' => $messages,
            'class' => $classData,
            'search_value' => $search,        
        );

        return $messages_data;

    }    

    //Method to show list of messages for backend
    public function get_list_messages_wp_dashboard($qty = 30){

        $qty = absint($qty);

        $messages_data = $this->get_messages_list($qty);
        $messages = $messages_data['data'];;
        $search = $messages_data['search_value'];;

        //classes for columns that are filtered
        $classData = $messages_data['class'];

        $idClass = $classData['id'];
        $questionClass = $classData['question'];
        $languageClass = $classData['language'];

        $searchBar = '<p class="search-box">
            <label class="screen-reader-text" for="tag-search-input">Search message:</label>
            <input type="search" id="tag-search-input" name="search" value="'.$search.'">
            <input type="submit" id="searchbutton" class="button" value="Search">
        </p>';

        // If data exists
        if (isset($messages[0]->id)){

            $columnsheading = '<tr>
                <th><input type="checkbox" class="select_all" /></th>
                <th order="id" class="worder '.$idClass.'">'.__( 'ID', 'ik_chatbot_admin' ).' <span class="sorting-indicator '.$idClass.'"></span></th>
                <th order="question" class="wide-data worder '.$questionClass.'">'.__( 'Pregunta', 'ik_chatbot_admin' ).' <span class="sorting-indicator '.$questionClass.'"></span></th>
                <th order="language" class="wide-data worder '.$languageClass.'">'.__( 'Lenguaje', 'ik_chatbot_admin' ).' <span class="sorting-indicator '.$languageClass.'"></span></th>
                <th class="wide-actions">
                    <button class="ik_chatbot_button_delete_bulk button action">'.__( 'Eliminar', 'ik_chatbot_admin' ).'</button></td>
                </th>
            </tr>';

            $csv_export = '<button class="button-primary panel_button" id="ik_chatbot_export_csv" href="#">'.__( 'Exportar Logs', 'ik_chatbot_admin' ).'</button>';

            $listing = '
            <div class="tablenav-pages">'.__( 'Total', 'ik_chatbot_admin' ).': '.$this->qty_records().' - '.__( 'Mostrando', 'ik_chatbot_admin' ).': '.count($messages).'</div>'.$searchBar.$csv_export;

            if ($search != NULL){
                $listing .= '<p class="show-all-button"><a href="'.$this->admin_url.'" class="button button-primary">'.__( 'Mostrar Todo', 'ik_chatbot_admin' ).'</a></p>';
            }

            $listing .= '<table id="ik_chatbot_existing">
                <thead>
                    '.$columnsheading.'
                </thead>
                <tbody>';
                foreach ($messages as $message){
                    
                    $listing .= '
                        <tr iddata="'.$message->id.'">
                            <td><input type="checkbox" class="select_data" /></td>
                            <td class="ik_chatbot_id">'.$message->id.'</td>
                            <td class="ik_chatbot_question">'.$message->question.'</td>
                            <td class="ik_chatbot_language">'.$message->language.'</td>
                            <td iddata="'.$message->id.'">
                                <a href="'.$this->admin_url.'&edit_id='.$message->id.'" class="ik_chatbot_button_edit_message button action">'.__( 'Editar', 'ik_chatbot_admin' ).'</a>
                                <button class="ik_chatbot_button_delete button action">'.__( 'Eliminar', 'ik_chatbot_admin' ).'</button></td>
                        </tr>';
                }
                $listing .= '
                </tbody>
                <tfoot>
                    '.$columnsheading.'
                </tfoot>
                <tbody>
            </table>';

                //admin_url list
                $listing .= $this->admin_ur_pages($this->qty_records(), $qty, $this->admin_url);
            
            return $listing;
            
        } else {
            if ($search != NULL){
                $listing = $searchBar.'
                <div id="ik_chatbot_existing">
                    <p>'.__( 'Nada encontrado.', 'ik_chatbot_admin' ).'</p>
                    <p class="show-all-button"><a href="'.$this->admin_url.'" class="button button-primary">'.__( 'Mostrar Todo', 'ik_chatbot_admin' ).'</a></p>
                </div>';

                return $listing;
            }
        }
        
        return '';
    }

    // method to add admin_url to lists
    public function admin_ur_pages($qty, $qtyToList, $page_url){
        $qty = intval($qty);
        $qtyToList = $qtyToList;
        $page_url = sanitize_url($page_url);
        $page = (isset($_GET['list'])) ? intval($_GET['list']) : 1;
        $page = ($page == 0) ? 1 : $page;
        $output = '';

        if ($qty > 0){
            $data_dataSubstr = $qty / $qtyToList;
            $total_pages = intval($data_dataSubstr);
                
                if (is_float($data_dataSubstr)){
                    $total_pages = $total_pages + 1;
                }
            
            if ($qty > $qtyToList){
                
                if ($total_pages > 1){
                    $output .= '<div class="ik_chatbot_pages">';
                    
                    //Enable certain page ids to show
                    $mitadlist = intval($total_pages/2);
                    
                    $pagesToShow[] = 1;
                    $pagesToShow[] = 2;
                    $pagesToShow[] = 3;
                    $pagesToShow[] = $total_pages;
                    $pagesToShow[] = $total_pages - 1;
                    $pagesToShow[] = $total_pages - 2;
                    $pagesToShow[] = $mitadlist - 2;
                    $pagesToShow[] = $mitadlist - 1;
                    $pagesToShow[] = $mitadlist;
                    $pagesToShow[] = $mitadlist + 1;
                    $pagesToShow[] = $mitadlist + 2;
                    $pagesToShow[] = $page+3;
                    $pagesToShow[] = $page+2;
                    $pagesToShow[] = $page+1;
                    $pagesToShow[] = $page;
                    $pagesToShow[] = $page-1;
                    $pagesToShow[] = $page-2;
                    
                    for ($i = 1; $i <= $total_pages; $i++) {
                        $show_page = false;
                        
                        //Showing enabled pages
                        if (in_array($i, $pagesToShow)) {
                            $show_page = true;
                        }
                        
                        if ($show_page == true){
                            if ($page == $i){
                                $PageNActual = 'actual_page';
                            } else {
                                $PageNActual = "";
                            }
                            $output .= '<a class="ik_listar_page_data '.$PageNActual.'" href="'.$page_url.'&list='.$i.'">'.$i.'</a>';
                        }
                    }
                    $output .= '</div>';
                }
            } 	            
        }
        return $output;
    }

    //delete message by ID
    public function delete($message_id){
        $message_id = absint($message_id);

        global $wpdb;
        $wpdb->delete( $this->db_table_messages, array( 'id' => $message_id ) );
        
        return true;
    }

    //write logs
    private function write_log($message, $language, $answer){
        //get IP
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }

        $request_log  = array (
            'date'=> current_time( 'mysql' ),	
            'language'=> $language,
            'text_sent'=> $message,
            'ip'=> $ip_address,
            'answer_given'=> $answer,
        );

        global $wpdb;
        $rowResult = $wpdb->insert($this->db_table_logs, $request_log, NULL);   
        $log_id = $wpdb->insert_id;
        
        //return the created message id
        return $log_id;
    }

    private function search_response($message_original){
        $patterns = array('1)', '2)', '3)', '4)', '5)', '6)', '7)', '8)', '9)', '10)', '11)', '12)', '13)', '14)', '15)');

        // Delete the string and any numbers and signs
        	
        $message = str_replace($patterns, '', $message_original);
        $message = ltrim($message);
        $message = str_replace(',', '', $message);

        $message_verify = trim($message);

        // Verify is not empty
        if (!empty($message_verify)) {

            //search first the raw question
            global $wpdb;
            $query = "SELECT ".$this->db_table_messages.".answer, ".$this->db_table_messages.".language FROM ".$this->db_table_messages." WHERE question LIKE '%".$message."%' LIMIT 1";
            $results = $wpdb->get_results($query);

            if($results[0]->answer){
                $response = $results[0]->answer;
                $language = $results[0]->language;
            } else {
                //search first the raw question
                global $wpdb;
                $query_keyword = "SELECT
                    ".$this->db_table_messages.".answer,
                    ".$this->db_table_messages.".language
                    FROM
                    ".$this->db_table_messages."
                    WHERE
                    keywords LIKE '%".$message."%' OR answer LIKE '%".$message."%' OR question LIKE '%".$message."%'
                    ORDER BY
                    (CASE WHEN keywords LIKE '".$message."%' THEN 1 ELSE 0 END) DESC,
                    (CASE WHEN keywords LIKE '%".$message."%' THEN 1 ELSE 0 END) DESC
                    LIMIT 1";
                $results_keyword = $wpdb->get_results($query_keyword);

                if($results_keyword[0]->answer){
                    $response = $results_keyword[0]->answer;
                    $language = $results_keyword[0]->language;
                } else {
                    //default answer when nothing found
                    $response = '<a href="'.get_site_url().'/#section-contacts">Click here</a> and complete the form to contact us and resolve your doubts.';
                    $language = 'en';
                }
            }
        } else {
            $response = 'Something went wrong. Please try again.';
            $language = 'en';
            $message = '"Message Empty"';
        }

        //write log
        $log_id = $this->write_log($message, $language, $response);

        return $response;
    }

    //give an answer
    public function get_response($message){
        $message = strtolower(sanitize_textarea_field($message));
        
        //to validate if log needed from here
        $regular_response_to_log = true;
        
        if($message == 'hola' || $message == 'hello' || $message == 'hi' || $message == 'ola' || $message == 'buen día'){
            //greetings
            if($message == 'hola' || $message == 'ola' || $message == 'buen día'){
                $response = 'Hola. ¿Cómo puedo ayudarte?';
                $lang = 'es';
            } else {
                $response = 'Hello. How can I help you?';
                $lang = 'en';
            }
                //giving thanks
        } else if($message == 'gracias' || $message == 'thanks' || $message == 'muchas gracias' || $message == 'thank you'){
                if($message == 'gracias' || $message == 'muchas gracias'){
                    $response = 'De Nada.';
                    $lang = 'es';
                } else {
                    $response = "You're welcome!";
                    $lang = 'en';
                }
                //leaving
        } else if($message == 'bye' || $message == 'hasta luego' || $message == 'chau' || $message == 'adios'){
                if($message == 'hasta luego' || $message == 'chau' || $message == 'adios'){
                    $response = 'Hasta Luego.';
                    $lang = 'es';
                } else {
                    $response = "Bye!";
                    $lang = 'en';
                }
        } else {
            //search db for answer
            $response = $this->search_response($message);
        }
        $regular_response_to_log = false;
        if($regular_response_to_log){
            //write log for not db checked responses
            $log_id = $this->write_log($message, $lang, $response);
        }

        return $response;
    }

    //method to generate log table to export details
    public function get_log_table_export_data(){

        $qty = 1000;

        $records = $this->get_logs($qty);

        // If data exists
        if (isset($records[0]->id)){
            $listing = '';
            $columnsheading = '<tr>
                <th>'.__( 'ID', 'ik_chatbot').'</th>
                <th>'.__( 'Language', 'ik_chatbot').'</th>
                <th>'.__( 'Text Sent', 'ik_chatbot').'</th>
                <th>'.__( 'Answer Given', 'ik_chatbot').'</th>
                <th>'.__( 'IP', 'ik_chatbot').'</th>
                <th>'.__( 'Date', 'ik_chatbot').'</th>
            </tr>';

            //won't be visible
            $listing .= '<table id="ik_chatbot_exporting_table" style="display:none">
                    <thead>
                        '.$columnsheading.'
                    </thead>
                    <tbody>';
                    foreach ($records as $record){
                        $listing .= '
                            <tr>
                                <td>'.$record->id.'</td>
                                <td>'.$record->language.'</td>
                                <td>'.$record->text_sent.'</td>
                                <td>'.$record->answer_given.'</td>
                                <td>'.$record->ip.'</td>
                            </tr>';
                        
                    }
                    $listing .= '
                    </tbody>
                </table>';

                //CSV file name
                $csv_export_file_name = 'report_logs_ll_'.date('m-d-Y').'.csv';
                
                $data_table_export = array(
                    'name' => $csv_export_file_name,
                    'table' => $listing,
                );
            
            return $data_table_export;
            
        }
        
        return false;
    }    
}