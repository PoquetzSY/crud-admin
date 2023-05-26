<?php


class Store{

    public function index()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "stores"; 
        $wpdb->get_results('SELECT * FROM '.$table_name . ' WHERE deleted_at IS NULL', OBJECT );
        $response = $wpdb->last_result;
        return $response;
    }

    public function save($store_info)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "stores";
        $wpdb->insert($table_name, $store_info);
        $response = $wpdb;
        return $this->show($response->insert_id);
       
    }

    public function update($store_info, $id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "stores"; 
        $wpdb->update($table_name,$store_info,array('id'=>$id));
        $response = $wpdb;
        
        return $this->show($id);
    }

    public function show($id)
    {
       global $wpdb;
       $table_name = $wpdb->prefix . "stores"; 
       $response = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id" ,OBJECT, 0);
       return $response;

    }

    public function destroy($id)
    {
      global $wpdb;
      $table_name = $wpdb->prefix . "stores"; 
      $wpdb->delete($table_name, array('id'=>$id));
      $response = $wpdb->rows_affected == 1 ? $id : 0 ;
      return $response;
    }

}