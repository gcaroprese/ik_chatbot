<?php
/*
Messages Template | IK Chatbot
Created: 01/23/2024
Update: 02/01/2024
Author: Gabriel Caroprese | inforket.com
*/

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$Ik_Chatbot = new Ik_Chatbot();

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (isset($_POST['question']) && isset($_POST['lang']) && isset($_POST['answer']) && isset($_POST['question_keyword'])){

        //DATA
        $args = array(
            'question'          => $_POST['question'],
            'lang'              => $_POST['lang'],
            'answer'            => $_POST['answer'],
            'question_keyword'  => $_POST['question_keyword']
        );

        //if editing an existing message id
        if(isset($_GET['edit_id'])){
            //if is creating or adding
            $args['message_id'] = intval($_GET['edit_id']);
            $Ik_Chatbot->edit_chat($args);

            $edit_data = $Ik_Chatbot->get_by_id($args['message_id']);
                        
        } else {
            //if is creating or adding
            $Ik_Chatbot->create_chats($args);

            //not being and edit because I just create it
            $edit_data = false;
        }
    }
} else {
    $edit_data = (isset($_GET['edit_id'])) ? $Ik_Chatbot->get_by_id($_GET['edit_id']) : false;
}
?>
<h1>Mensajes Chatbot</h1>
<div id="ik_chatbot_add_records">
<?php
//if edit
if($edit_data){

    $keywords = str_replace("''**", "\n", $edit_data->keywords);
    $lang_en = ($edit_data->language == 'en') ? 'selected' : '';
    $lang_es = ($edit_data->language == 'es') ? 'selected' : '';

?>
<form action="" method="post" enctype="multipart/form-data" autocomplete="no">
        <div class="ik_chatbot_fields">
    		<p>
                <h4>Pregunta</h4>
    		    <input type="text" required="" name="question" value="<?php echo $edit_data->question; ?>"> 
    		</p>	
            <p>
                <h4>Lenguaje</h4>
    		    <select required="" name="lang">
                    <option <?php echo $lang_es; ?> value="es">Español</option>
                    <option <?php echo $lang_en; ?> value="en">Inglés</option>
                </select> 
    		</p>
            <p>
                <h4>Respuesta</h4>
    		    <textarea required="" name="answer"><?php echo $edit_data->answer; ?></textarea>
    		</p>	
    		<p>
                <h4>Preguntas Clave (separados por renglones)</h4>
    		    <textarea required="" name="question_keyword"><?php echo $keywords; ?></textarea>
    		</p>
        </div>
        <input type="submit" class="button button-primary" value="Editar Answer">
        <a href="<?php echo $Ik_Chatbot->get_admin_url(); ?>" class="button">Agregar Nueva Respuesta</a>
    </form>
</div>
<?php
} else {
?>
<form action="" method="post" enctype="multipart/form-data" autocomplete="no">
        <div class="ik_chatbot_fields">
    		<p>
                <h4>Pregunta</h4>
    		    <input type="text" required="" name="question"> 
    		</p>	
            <p>
                <h4>Lenguaje</h4>
    		    <select required="" name="lang">
                    <option value="es">Español</option>
                    <option value="en">Inglés</option>
                </select> 
    		</p>
            <p>
                <h4>Respuesta</h4>
    		    <textarea required="" name="answer"></textarea>
    		</p>	
    		<p>
                <h4>Preguntas Clave (separados por renglones)</h4>
    		    <textarea required="" name="question_keyword"></textarea>
    		</p>
        </div>
        <input type="submit" class="button button-primary" value="Add Answer">
    </form>
</div>
<?php  
}
?>
<div id ="ik_chatbot_existing">
<?php echo $Ik_Chatbot->get_list_messages_wp_dashboard(); ?>
</div>
<script>
    jQuery(document).ready(function ($) {

        jQuery("#ik_chatbot_existing th .select_all").on( "click", function() {
            if (jQuery(this).attr('selected') != 'selected'){
                jQuery('#ik_chatbot_existing th .select_all').prop('checked', true);
                jQuery('#ik_chatbot_existing th .select_all').attr('checked', 'checked');
                jQuery('#ik_chatbot_existing tbody tr').each(function() {
                    jQuery(this).find('.select_data').prop('checked', true);
                    jQuery(this).find('.select_data').attr('checked', 'checked');
                });        
                jQuery(this).attr('selected', 'selected');
            } else {
                jQuery('#ik_chatbot_existing th .select_all').prop('checked', false);
                jQuery('#ik_chatbot_existing th .select_all').removeAttr('checked');
                jQuery('#ik_chatbot_existing tbody tr').each(function() {
                    jQuery(this).find('.select_data').prop('checked', false);
                    jQuery(this).find('.select_data').removeAttr('checked');
                });   
                jQuery(this).removeAttr('selected');
                
            }
        });
        jQuery("#ik_chatbot_existing td .select_data").on( "click", function() {
            jQuery('#ik_chatbot_existing th .select_all').prop('checked', false);
            jQuery('#ik_chatbot_existing th .select_all').removeAttr('checked');
            jQuery(this).removeAttr('selected');
        });

        jQuery('#ik_chatbot_existing').on('click','th.worder', function(e){
            e.preventDefault();

            var order = jQuery(this).attr('order');
            var urlnow = window.location.href;
            
            if (order != undefined){
                if (jQuery(this).hasClass('desc')){
                    var direc = 'asc';
                } else {
                    var direc = 'desc';
                }
                if (order == 'id'){
                    var orderby = '&orderby=id&orderdir='+direc;
                    window.location.href = urlnow+orderby;
                } else if (order == 'question'){
                    var orderby = '&orderby=question&orderdir='+direc;
                    window.location.href = urlnow+orderby;
                } else if (order == 'language'){
                    var orderby = '&orderby=language&orderdir='+direc;
                    window.location.href = urlnow+orderby;
                }
            }

        });

        jQuery("#ik_chatbot_existing .ik_chatbot_button_delete_bulk").on( "click", function() {
            var confirmar = confirm('<?php echo __( 'Are you sure to delete?', 'ik_chatbot_admin' ); ?>');
            if (confirmar == true) {
                jQuery('#ik_chatbot_existing tbody tr').each(function() {
                var elemento_borrar = jQuery(this).parent();
                    if (jQuery(this).find('.select_data').prop('checked') == true){
                        
                        var registro_tr = jQuery(this);
                        var iddata = registro_tr.attr('iddata');
                        
                        var data = {
                            action: "ik_chatbot_ajax_delete_message",
                            "post_type": "post",
                            "iddata": iddata,
                        };  
            
                        jQuery.post( ajaxurl, data, function(response) {
                            if (response){
                                registro_tr.fadeOut(700);
                                registro_tr.remove();
                            }        
                        });
                    }
                });
            }
            jQuery('#ik_chatbot_existing th .select_all').attr('selected', 'no');
            jQuery('#ik_chatbot_existing th .select_all').prop('checked', false);
            jQuery('#ik_chatbot_existing th .select_all').removeAttr('checked');
            return false;
        });
	
        jQuery('#ik_chatbot_existing').on('click','td .ik_chatbot_button_delete', function(e){
            e.preventDefault();
            var confirmar =confirm('<?php echo __( 'Are you sure to delete?', 'ik_chatbot_admin' ); ?>');
            if (confirmar == true) {
                var iddata = jQuery(this).parent().attr('iddata');
                var registro_tr = jQuery('#ik_chatbot_existing tbody').find('tr[iddata='+iddata+']');
                
                var data = {
                    action: 'ik_chatbot_ajax_delete_message',
                    "iddata": iddata,
                };  
        
                jQuery.post( ajaxurl, data, function(response) {
                    if (response){
                        registro_tr.fadeOut(700);
                        registro_tr.remove();
                    }        
                });
            }
        });
        jQuery('#ik_chatbot_existing').on('click','#searchbutton', function(e){
            e.preventDefault();
            
            var search_value = jQuery('#tag-search-input').val();
            var urlnow = window.location.href;
            window.location.href = urlnow+"&search="+search_value;

        });
        jQuery("#csv_export_messages").on( "click", function() {
            var data = {
                action: 'ik_chatbot_ajax_get_table_all_messages',
            };
            jQuery.post( ajaxurl, data, function(response) {
                if (response){
                    jQuery("#table_export").remove();
                    jQuery("body").append(response);
                    exportTableToCSV_additional_fields('messages'+Date.now()+'.csv')
                }
            });
            
            return false;
        });
        function downloadCSV(csv, filename) {
            var csvFile;
            var downloadLink;

            // CSV file
            csvFile = new Blob(["\uFEFF"+csv], {type: 'text/csv; charset=utf-18'});

            // Download link
            downloadLink = document.createElement("a");

            // File name
            downloadLink.download = filename;

            // Create a link to the file
            downloadLink.href = window.URL.createObjectURL(csvFile);

            // Hide download link
            downloadLink.style.display = "none";

            // Add the link to DOM
            document.body.appendChild(downloadLink);

            // Click download link
            downloadLink.click();
        }
        function exportTableToCSV(filename) {
            var csv = [];
            var rows = document.querySelectorAll("table#ik_chatbot_exporting_table tr");
            
            for (var i = 0; i < rows.length; i++) {
                var row = [], cols = rows[i].querySelectorAll("td, th");
                
                for (var j = 0; j < cols.length; j++) 
                    row.push(cols[j].innerText);
                
                csv.push(row.join(","));        
            }

            // Download CSV file
            downloadCSV(csv.join("\n"), filename);
        }
        jQuery('#ik_chatbot_export_csv').on('click', function(){
            var button_csv_export = jQuery(this);
            button_csv_export.prop('disabled', true);
            var data = {
                action: "ik_chatbot_ajax_get_full_log_table",
                "post_type": "post",
            };
            jQuery.post( ajaxurl, data, function(response) {
                if (response){
                    jQuery('body').append(response.table);
                    exportTableToCSV(response.name);
                    setTimeout(function(){ 
                        jQuery('#ik_chatbot_exporting_table').remove();
                        button_csv_export.prop('disabled', false);
                    }, 3000);
                }
            }, "json");
        });
    });
</script>