<?php
/**
 * Plugin Name: Clientes Registrados
 * Description: Un plugin para guardar datos de personas mediante un formulario.
 * Version: 1.0
 * Author: Tu Nombre
 */

// Función para agregar el menú del plugin en el panel de administración
// Registro del menú de administración
add_action('admin_menu', 'agregar_pagina_administracion');

// Función para agregar la página de administración al menú
function agregar_pagina_administracion() {
    add_menu_page(
        'Clientes Registrados', // Título de la página
        'Clientes', // Título del menú
        'manage_options', // Capacidad requerida para acceder a la página
        'clientes_registrados', // Identificador único de la página
        'mostrar_pagina_administracion', // Función que muestra el contenido de la página
        'dashicons-admin-users',
        30
    );
}

// Función que muestra el contenido de la página de administración
function mostrar_pagina_administracion() {
    // Verificar si el usuario tiene los permisos necesarios
    if (!current_user_can('manage_options')) {
        wp_die('No tienes permiso para acceder a esta página.');
    }

    // Obtener los datos de los clientes registrados
    global $wpdb;
    $table_name = $wpdb->prefix . 'clientes';
    $clientes = $wpdb->get_results("SELECT * FROM $table_name");

    // Mostrar los datos de los clientes
    // Mostrar los datos de los clientes
    echo '<h1>Clientes Registrados</h1>';
    echo '<table class="wp-list-table widefat fixed">';
    echo '<thead><tr><th>Nombre</th><th>Apellido</th><th>Email</th><th>Teléfono</th></tr></thead>';
    echo '<tbody>';
    foreach ($clientes as $cliente) {
        echo '<tr>';
        echo '<td>' . esc_html($cliente->nombre) . '</td>';
        echo '<td>' . esc_html($cliente->apellidos) . '</td>';
        echo '<td>' . esc_html($cliente->correo) . '</td>';
        echo '<td>' . esc_html($cliente->telefono) . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';

}

// Registro del shortcode para mostrar el formulario
add_shortcode('formulario_guardar_datos', 'mostrar_formulario_guardar_datos');

// Función que muestra el formulario
function mostrar_formulario_guardar_datos() {
    ob_start(); // Iniciar el almacenamiento en búfer de salida

    // Verificar si se envió el formulario
    if (isset($_POST['guardar_datos_submit'])) {
        // Procesar los datos enviados
        $nombre = sanitize_text_field($_POST['nombre']);
        $apellido = sanitize_text_field($_POST['apellido']);
        $email = sanitize_email($_POST['email']);
        $telefono = sanitize_text_field($_POST['telefono']);

        // Validación adicional si es necesario

        // Guardar los datos en la base de datos o realizar las acciones necesarias
        // Aquí puedes personalizar el código según tus necesidades
        guardar_datos_persona($nombre, $apellido, $email, $telefono);

        // Mostrar un mensaje de éxito
        echo '<div class="mensaje-exito">¡Los datos se han guardado correctamente!</div>';
    }

    // Mostrar el formulario
    ?>
    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required>

        <label for="apellido">Apellido:</label>
        <input type="text" name="apellido" id="apellido" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>

        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono" id="telefono" required>

        <input type="submit" name="guardar_datos_submit" value="Guardar">
    </form>
    <?php

    return ob_get_clean(); // Devolver el contenido del búfer de salida
}


// Función para crear la tabla en la base de datos
function crear_tabla_datos_personas() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'clientes';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(255) NOT NULL,
        apellido VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        telefono VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'crear_tabla_datos_personas');

// Función para guardar los datos en la base de datos
function guardar_datos_persona($nombre, $apellido, $email, $telefono) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'clientes';
    $data = array(
        'nombre' => $nombre,
        'apellido' => $apellido,
        'email' => $email,
        'telefono' => $telefono
    );
    $wpdb->insert($table_name, $data);
}


// Función para obtener los clientes registrados
function obtener_clientes_registrados() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'clientes';
    $clientes = $wpdb->get_results("SELECT * FROM $table_name");
    return $clientes;
}