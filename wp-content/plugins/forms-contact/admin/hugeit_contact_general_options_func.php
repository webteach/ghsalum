<?php
if(! defined( 'ABSPATH' )) exit;
if (function_exists('current_user_can'))
    if (!current_user_can('manage_options')) {
        die('Access Denied');
    }
if (!function_exists('current_user_can')) {
    die('Access Denied');
}

function showsettings($op_type = "0")
{
    global $wpdb;
    $query = "SELECT *  from " . $wpdb->prefix . "huge_it_contact_general_options ";
    $rows = $wpdb->get_results($query);
    $param_values = array();
    foreach ($rows as $row) {
        $key = $row->name;
        $value = $row->value;
        $param_values[$key] = $value;
    }
    html_showsettings($param_values, $op_type);
}

function save_styles_options(){
    @session_start();
    if(isset($_POST['csrf_token_hugeit_forms']) && (!isset($_SESSION["csrf_token_hugeit_forms"]) || $_SESSION["csrf_token_hugeit_forms"] != @$_POST['csrf_token_hugeit_forms'])) { exit; }
    
    global $wpdb;
    if (isset($_POST['params'])){
    $params = $_POST['params'];
        foreach ($params as $key => $value) {
            $wpdb->update($wpdb->prefix . 'huge_it_contact_general_options',
                array('value' => $value),
                array('name' => $key),
                array('%s')
            );
        }
        $adminMessage = stripslashes($_POST['adminmessage']);
        $userMessage = stripslashes($_POST['usermessage']);
        $images='';
        $pattern='/(<img.*?>)/';
        preg_match_all($pattern, $userMessage, $images);
        $i=0;
        $patterns=array();
        foreach ($images[0] as $image) {
            $image =preg_replace('/"/', "", $image); 
            $image =preg_replace('/\</', "", $image);
            $image =preg_replace('/\>/', "", $image);   
             

            $patterns[$i]=$image;
            $i++;           
        }
        $userMessage=preg_replace($images[0], $patterns, $userMessage);
        $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."huge_it_contact_general_options SET  value='%s'  WHERE name = 'form_adminstrator_message' ", $adminMessage));
        $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."huge_it_contact_general_options SET  value='%s'  WHERE name = 'form_user_message' ", $userMessage));

        ?>
        <div class="updated"><p><strong><?php _e('Item Saved'); ?></strong></p></div>
        <?php
	}
    ?>
    
 <?php } ?>