<?php
function drop_table_store(){
    global $wpdb;
    $table_name = $wpdb->prefix . "stores";
    $sql = "DROP TABLE $table_name;";
    $wpdb->query($sql);
}