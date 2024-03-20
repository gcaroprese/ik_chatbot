<?php
/*

Shortcode | IK Chatbot
Created: 01/23/2024
Update: 01/23/2024
Author: Gabriel Caroprese | inforket.com

*/

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

// shortcode to show chat box
function ik_chatbot_shortcode() {
    ob_start();
    ?>
    <style>
    #ik_chatbot_open_chat_btn{
        cursor: pointer;
        position: fixed;
        bottom: 6px;
        right: 6px;
        background: #ff6900;
        border: 1px solid #ff6900;
        z-index: 9999999;
        border-radius: 20px;
        padding: 9px 15px;
        width: 150px;
        height: 47px;
        font-size: 18px;
        color: #fff;
    }
    #ik_chatbot_chat_container{
        max-width: 97%;
        width: 330px;
        position: fixed;
        z-index: 9999999;
        right: 6px;
        bottom: 6px;
        background: #fff;
        border: 1px solid #ff6900;
        border-radius: 15px;
        transition: transform 1.5s ease-in-out;
    }
    #ik_chatbot_chat_container .ik_chatbot_chat_header {
        background: #ff6900;
        font-weight: 600;
        text-align: left;
        font-size: 18px;
        color: #fff;
        border-radius: 15px 15px 0 0;
    }
    #ik_chatbot_chat_box{
        padding: 15px;
    }
    #ik_chatbot_chat_messages{
        max-height: 210px;
        overflow: auto;
        font-size: 17px;
        margin-bottom: 12px;
    }
    #ik_chatbot_send_btn {
        width: 100%;
        padding: 3px;
        color: #fff;
        font-size: 15px;
        background: #333;
        border-radius: 3px;
        outline: none;
    }
    #ik_chatbot_chat_messages .message {
        display: flow-root;
        margin-bottom: 12px;
    }
    #ik_chatbot_chat_messages .message_chatbot, #ik_chatbot_chat_messages .message_user{
        padding: 3px 12px;
        border-radius: 9px;
        display: unset;
    }
    #ik_chatbot_chat_messages .message_chatbot {
        color: #fff;
        background: #333;
        float: left;
        margin-right:10%;
        margin-left:6px;
    }
    #ik_chatbot_chat_messages .message_user {
        float: right;
        background: #f1f1f1f1;
        color: #333;
        margin-left:10%;
        margin-right:6px;
    }
    #ik_chatbot_chat_container textarea {
        width: 100%;
        padding: 6px 12px;
        font-size: 15px;
        border-radius: 3px;
    }
    #ik_chatbot_chat_container #ik_chatbot_typing_indicator {
        margin: 9px 12px;      
        display: none;
        align-items: center;
    }
    #ik_chatbot_chat_container .ik_chatbot_typing_dot {
      width: 10px;
      height: 10px;
      background-color: #555;
      border-radius: 50%;
      margin-right: 5px;
      animation: chatbot_blink 1.2s infinite;
    }
    #ik_chatbot_chat_container .ik_chatbot_chat_panel_menu {
        float: right;
        margin-top: 3px;
        height: 27px;
    }
    #ik_chatbot_chat_container.minimized{
        border: 0;
    }
    #ik_chatbot_chat_container .ik_chatbot_chat_panel_title {
        padding: 6px 0 2px 20px
    }
    #ik_chatbot_chat_container .ik_chatbot_chat_panel {
        margin-right: 6px;
    }
    .ik_chatbot_chat_panel * {
        fill: #fff;
        stroke: #fff;
        cursor: pointer;
    }
    #ik_chatbot_chat_messages .message .message_chatbot a{
        color: #fff;
    }
    .ik_chatbot_chat_panel *:not(rect) {
        fill: #fff;
        stroke: #fff;
    }
    .ik_chatbot_chat_panel * {
        cursor: pointer;
    }
    #ik_chatbot_chat_max div{
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #fff;
        position: relative;
        top: -4px;
        left: 2px;
    }
    @keyframes chatbot_blink {
      0%, 50%, 100% {
        opacity: 0;
      }
      25%, 75% {
        opacity: 1;
      }
    }
    </style>
    <button id="ik_chatbot_open_chat_btn">
        <div>
            <span><?php echo __( 'Ask Me', 'ik_chatbot'); ?></span>
        </div>
    </button>
    <div id="ik_chatbot_chat_container" style="display: none;">
        <div class="ik_chatbot_chat_header">
            <span class="ik_chatbot_chat_panel_title"><?php echo __( 'Ask Me', 'ik_chatbot'); ?></span>
            <div class="ik_chatbot_chat_panel_menu">
                <span id="ik_chatbot_chat_min" class="ik_chatbot_chat_panel">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="12" x2="20" y2="12"></line>
                    </svg>
                </span>
                <span id="ik_chatbot_chat_max" class="ik_chatbot_chat_panel" style="display: none;">
                    <div></div>
                </span>
                <span id="ik_chatbot_chat_close" class="ik_chatbot_chat_panel">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>      
                </span>
            </div>
        </div>
        <div id="ik_chatbot_chat_box">
            <div id="ik_chatbot_chat_messages">
                <div class="message">
                    <div class="message message_chatbot message_chatbot_init"><?php echo __( 'Hello, how can I help you?', 'ik_chatbot'); ?></div>
                </div>
            </div>
            <div id="ik_chatbot_typing_indicator">
                <div class="ik_chatbot_typing_dot"></div>
                <div class="ik_chatbot_typing_dot"></div>
                <div class="ik_chatbot_typing_dot"></div>
            </div>
            <textarea id="ik_chatbot_user_input" placeholder="<?php echo __( 'Type your message...', 'ik_chatbot'); ?>"></textarea>
            <button id="ik_chatbot_send_btn" disabled><?php echo __( 'Send', 'ik_chatbot'); ?></button>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ik_chatbot_wait_time = 1000;
            const ik_chatbot_user_text_sent = document.getElementById('ik_chatbot_user_input');

            document.getElementById('ik_chatbot_open_chat_btn').addEventListener('click', function() {
                document.getElementById('ik_chatbot_chat_container').style.display = 'block';
                document.getElementById('ik_chatbot_open_chat_btn').style.display = 'none';
            });

            ik_chatbot_user_text_sent.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    document.getElementById('ik_chatbot_send_btn').click();
                }
            });
            function checkInput() {
                var userInput = document.getElementById('ik_chatbot_user_input').value;
                var sendBtn = document.getElementById('ik_chatbot_send_btn');
                if(userInput.trim() === ''){
                    sendBtn.disabled = true;
                } else {
                    sendBtn.disabled = false;
                }
            }
            document.getElementById('ik_chatbot_user_input').addEventListener('input', checkInput);

            document.getElementById('ik_chatbot_send_btn').addEventListener('click', function() {
                setTimeout(function(){
                    const userInput = document.getElementById('ik_chatbot_user_input');
                    const userInput_value = userInput.value;

                    if (userInput_value.trim() !== ""){
                        const typing_indicator = document.getElementById('ik_chatbot_typing_indicator');
                        ik_chatbot_js_appendMessage("user", userInput_value);
                        userInput.value = '';
                        typing_indicator.style = 'display: flex';
                        var data = {
                        action: "ik_chatbot_ajax_response",
                            "post_type": "post",
                            "message": userInput_value,
                        };
                        jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                            if (response){
                                ik_chatbot_js_appendMessage("chatbot", response);
                                typing_indicator.style = 'display: none';
                                const messageElements = document.getElementById('ik_chatbot_chat_messages');
                                const messages_chatbot = messageElements.querySelectorAll('.message_chatbot');
                                
                                messages_chatbot.forEach(function(messageElement) {
                                    const linkElement = messageElement.querySelector('a');

                                    if (linkElement) {
                                        linkElement.setAttribute('target', '_blank');
                                    }
                                });
                            }
                        }, "json");
                    }
                }, ik_chatbot_wait_time);
                ik_chatbot_wait_time = ik_chatbot_wait_time + 500;
            });
            document.getElementById('ik_chatbot_chat_close').addEventListener('click', function() {
                document.getElementById('ik_chatbot_chat_container').style.display = 'none';
                document.getElementById('ik_chatbot_open_chat_btn').style.display = 'block';
                const chatMessages = document.getElementById('ik_chatbot_chat_messages');
                const chatbotMessages = chatMessages.querySelectorAll('.message_chatbot');
                message_user
                chatbotMessages.forEach(message => {
                    if (!message.classList.contains('message_chatbot_init')) {
                        message.remove();
                    }
                });
            });
            document.getElementById('ik_chatbot_chat_min').addEventListener('click', function() {
                document.getElementById('ik_chatbot_chat_box').style.display = 'none';
                document.getElementById('ik_chatbot_chat_min').style.display = 'none';
                document.getElementById('ik_chatbot_chat_max').style.display = 'inline-block';
                document.getElementById('ik_chatbot_chat_container').className = 'minimized';
            });
            document.getElementById('ik_chatbot_chat_max').addEventListener('click', function() {
                document.getElementById('ik_chatbot_chat_container').className = '';
                document.getElementById('ik_chatbot_chat_box').style.display = 'block';
                document.getElementById('ik_chatbot_chat_min').style.display = 'inline-block';
                document.getElementById('ik_chatbot_chat_max').style.display = 'none';
            });
            function ik_chatbot_js_appendMessage(sender, message) {
                const chatMessages = document.getElementById('ik_chatbot_chat_messages');
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message';
                messageDiv.innerHTML = message;
                messageDiv.innerHTML = '<div class="message_'+sender+'">'+message+'</div>';

                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('ik_chatbot', 'ik_chatbot_shortcode');