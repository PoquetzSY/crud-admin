<?php
/*
Plugin Name: Gestión de Categorías
Plugin URI: 
Description: Plugin para gestión de categorías.
Version: 1.0
Author: Isaac, Diego & Luis
Author URI: 
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
                echo '<thead>';
                    echo '<tr>';
                        echo '<th>Nombre</th>';
                        echo '<th>Tipo de material</th>';
                        echo '<th></th>';
                    echo '</tr>';
                echo '</thead>';
            foreach ($categorias as $categoria) {
                echo '<tr>';
                    echo '<td>' . $categoria->nombre.'</td>';
                    echo '<td>' . $categoria->tipo_material . '</td>';
                    echo '<td> <a class="eliminarc" href="?page=mi-plugin-categorias&action=delete&categoria_id=' . $categoria->id . '">Eliminar</a> </td>';
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
