<?php


function created_table_store(){

global $wpdb;
$table_name = $wpdb->prefix . "stores";

//        $charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE $table_name(
id INT(11) NOT NULL AUTO_INCREMENT,
name VARCHAR(255) NOT NULL,
address TEXT NOT NULL,
latitud DOUBLE NULL,
longitud DOUBLE NULL, 
created_at TIMESTAMP NULL,
updated_at TIMESTAMP NULL,
deleted_at TIMESTAMP NULL,
PRIMARY KEY (id)

);";


require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );
}