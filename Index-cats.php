<?php
/*
Plugin Name: Gestión de Categorías
Plugin URI: https://www.ejemplo.com
Description: Plugin para gestión de categorías.
Version: 1.0
Author: Isaac, Diego & Luis
Author URI: https://www.tunombre.com
License: GPLv2 or later
Text Domain: mi-plugin-categorias
*/

// Función para crear la tabla de categorías en la base de datos
function mi_plugin_create_table_cats() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'categorias';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(100) NOT NULL,
        tipo_material VARCHAR(100) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'mi_plugin_create_table_cats');

// Función para agregar el menú del plugin en el panel de administración
function mi_plugin_menu2() {
    add_menu_page(
        'Gestión de Categorías',
        'Categorías',
        'manage_options',
        'mi-plugin-categorias',
        'mi_plugin_categorias_page_cats',
        'dashicons-category',
        30
    );
}
add_action('admin_menu', 'mi_plugin_menu2');

// Función para mostrar la página de gestión de categorías en el panel de administración
function mi_plugin_categorias_page_cats() {
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'categorias';

    // Procesar formulario de creación de categoría
    if (isset($_POST['submit_create'])) {
        $nombre = sanitize_text_field($_POST['nombre']);
        $tipo_material = sanitize_text_field($_POST['tipo_material']);

        $wpdb->insert(
            $table_name,
            array(
                'nombre' => $nombre,
                'tipo_material' => $tipo_material,
            )
        );
    }

    // Procesar solicitud de eliminación de categoría
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['categoria_id'])) {
        $id = absint($_GET['categoria_id']);

        $wpdb->delete(
            $table_name,
            array('id' => $id)
        );
    }

    // Resto del código de la página de gestión de categorías...

    // Mostrar formulario
    ?>
        <style>
        .formulario{
            display: flex;
            flex-direction: column;
            width: 100%;
            align-items: center;
            margin: 20px
        }
        .form{
            background-color: lightgray;
            border-radius: 15px;
            width: 700px;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .producto{
            display: flex;
            flex-direction: column;
        }
        .box-p{
            display: flex;
            justify-content: space-between;
            width: 500px
        }
        label{
            font-weight: 400;
            font-size: 1rem;
            margin-bottom: 5px;
        }
        h1{
            font-size: 2rem;
            margin-bottom: 5px;
        }
        h2{
            font-size: 1.5rem;
        }
        .delete:hover{
            color:white;
            cursor: pointer;
            background-color: #b12222;
        }
        .delete{
            text-decoration:none;
            background-color: #e42222;
            border: 0;
            width: 200px;
            font-size: 1rem;
            font-weight: light;
            padding: .4em;
            border-radius: 10px;
            color: white;
            margin-top: 10px;
        }
        .botones{
            display:flex;
            flex-direction: column;
            align-items: center;
            margin-top: 15px;
        }
        .boton:hover{
            cursor: pointer;
            background-color: #154c78;
        }
        .boton {
            background-color: #2271b1;
            border: 0;
            width: 200px;
            font-size: 1rem;
            font-weight: light;
            padding: .7rem;
            border-radius: 10px;
            color: white;
            margin-top: 10px;
        }
        .botonc:hover{
            cursor: pointer;
            background-color: #b12222;
        }
        .botonc {
            background-color: #e42222;
            border: 0;
            width: 200px;
            font-size: 1rem;
            font-weight: light;
            padding: .7rem;
            border-radius: 10px;
            color: white;
            margin-top: 10px;
        }
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
        input[type=text]{
            background-color: transparent;
            border: 0;
            border-bottom: 2px solid rgba(87, 87, 86, 0.8);
            border-radius: 0;
        }
        input[type=text]:focus,
        input[type=text]:active{
            outline: 0;
            box-shadow: none;
            border-color: #1d2327;
        }
        input[type=number]{
            background-color: transparent;
            border: 0;
            border-bottom: 2px solid rgba(87, 87, 86, 0.8);
            border-radius: 0;
        }
        input[type=number]:focus,
        input[type=number]:active{
            outline: 0;
            box-shadow: none;
            border-color: #1d2327;
        }
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
          -webkit-appearance: none;
          margin: 0;
        }
    </style>
    <div class="formulario">
        <h1>Gestión de Categorías</h1>

        <h2>Agregar Categoría</h2>
        <form method="POST" class="form">
            <label for="nombre">Nombre de la Categoría:</label>
            <input type="text" name="nombre" required>
            <br>
            <label for="tipo_material">Tipo de Material:</label>
            <input type="text" name="tipo_material" required>
            <br>
            <div class="botones">
                <input type="submit" class="boton" name="submit_create" value="Agregar Categoría">
                <input type="submit" class="botonc" name="submit_create" value="Cancelar">
            </div>
        </form>

        <h2>Listado de Categorías</h2>
        <?php
        // Obtener todas las categorías de la base de datos
        $categorias = $wpdb->get_results("SELECT * FROM $table_name");

        if ($categorias) {
            echo '<table>';
            foreach ($categorias as $categoria) {
                echo '<tr>';
                    echo '<th>Nombre</th>';
                    echo '<th>Tipo de material</th>';
                    echo '<th>Acciones</th>';
                echo '</tr>';
                echo '<tr>';
                    echo '<td>' .esc_html($categoria->nombre).'</td>';
                    echo '<td>' . esc_html($categoria->tipo_material) . '</td>';
                    echo '<td> <a class="delete" href="?page=mi-plugin-categorias&action=delete&categoria_id=' . $categoria->id . '">Eliminar</a> </td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo 'No se encontraron categorías.';
        }
        ?>
    </div>
    <?php
}
