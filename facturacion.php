<?php
/*
Plugin Name: Mi Plugin de Facturación
Description: Plugin para almacenar datos de facturación y archivos PDF en WordPress.
Version: 1.0
Author: Tu Nombre
*/

// Función para crear la tabla de base de datos al activar el plugin
function mi_plugin_facturacion_activate() {
    global $wpdb;
    $tabla_facturacion = $wpdb->prefix . 'datos_facturacion';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $tabla_facturacion (
        id INT NOT NULL AUTO_INCREMENT,
        nombre_cliente VARCHAR(100) NOT NULL,
        apellidos VARCHAR(100) NOT NULL,
        email_cliente VARCHAR(100) NOT NULL,
        CFSI VARCHAR(100) NOT NULL,
        regimenf Varchar(100) NOT NULL,
        rfc VARCHAR(100) NOT NULL,
        telefono VARCHAR(100) NOT NULL,
        archivo_pdf VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'mi_plugin_facturacion_activate' );

// Función para mostrar el formulario de facturación
function mi_plugin_facturacion_formulario() {
    ob_start(); // Iniciar el almacenamiento en búfer de salida

    // Comprobar si se envió el formulario
    if ( isset( $_POST['mi_plugin_facturacion_submit'] ) ) {
        // Procesar los datos del formulario
        $nombre_cliente = sanitize_text_field( $_POST['nombre_cliente'] );
        $email_cliente = sanitize_email( $_POST['email_cliente'] );
        $apellidos = sanitize_text_field( $_POST['apellidos'] );
        $cfsi = sanitize_text_field( $_POST['cfsi'] );
        $regimenf = sanitize_text_field( $_POST['regimenf'] );
        $rfc = sanitize_text_field( $_POST['rfc'] );
        $telefono = sanitize_text_field( $_POST['telefono'] );


        // Guardar el archivo PDF en el servidor
        $archivo_pdf = '';
        if ( ! empty( $_FILES['archivo_pdf']['name'] ) ) {
            $upload_dir = wp_upload_dir();
            $file_name = sanitize_file_name( $_FILES['archivo_pdf']['name'] );
            $file_path = $upload_dir['path'] . '/' . $file_name;
            $file_url = $upload_dir['url'] . '/' . $file_name;

            if ( move_uploaded_file( $_FILES['archivo_pdf']['tmp_name'], $file_path ) ) {
                $archivo_pdf = $file_url;
            }
        }

        // Guardar los datos en la base de datos
        global $wpdb;
        $tabla_facturacion = $wpdb->prefix . 'datos_facturacion';

        $wpdb->insert(
            $tabla_facturacion,
            array(
                'nombre_cliente' => $nombre_cliente,
                'email_cliente' => $email_cliente,
                'apellidos' => $apellidos,
                'cfsi' => $cfsi,
                'regimenf' => $regimenf,
                'rfc' => $rfc,
                'telefono' => $telefono,
                'archivo_pdf' => $archivo_pdf,
            )
        );

        // Mostrar mensaje de éxito
        echo '<div class="mi-plugin-facturacion-success">¡La facturación se ha enviado con éxito!</div>';
    }

    // Mostrar el formulario de facturación
    ?>
    <style>
        .mi-plugin-facturacion-form {
            max-width: 400px;
            margin: 0 auto;
        }

        .mi-plugin-facturacion-form label {
            display: block;
            margin-bottom: 10px;
        }

        .mi-plugin-facturacion-form input[type="text"],
        .mi-plugin-facturacion-form input[type="email"],
        .mi-plugin-facturacion-form input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .mi-plugin-facturacion-form input[type="file"] {
            padding-top: 12px;
        }

        .mi-plugin-facturacion-form textarea {
            width: 100%;
            height: 120px;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .mi-plugin-facturacion-form button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .mi-plugin-facturacion-success {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #d6e9c6;
            border-radius: 4px;
        }
    </style>

    <div class="mi-plugin-facturacion-form">
        <h2>Formulario de Facturación</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <label for="nombre_cliente">Nombre:</label>
            <input type="text" id="nombre_cliente" name="nombre_cliente" required>

            <label for="apellidos">Apellidos:</label>
            <input type="text" id="apellidos" name="apellidos" required>

            <label for="email_cliente">Email:</label>
            <input type="text" id="email_cliente" name="email_cliente" required>
            
            <label for="telefono">Numero Telefonico:</label>
            <input type="text" id="telefono" name="telefono" required>

            <label for="cfsi">CFSI:</label>
            <input type="text" id="cfsi" name="cfsi" required>

            <label for="regimenf">Regimen Fiscal:</label>
            <input type="text" id="regimenf" name="regimenf" required>

            <label for="rfc">RFC:</label>
            <input type="text" id="rfc" name="rfc" required>

            <label for="archivo_pdf">Constancia Fiscal PDF:</label>
            <input type="file" id="archivo_pdf" name="archivo_pdf" accept=".pdf" required>

            <button type="submit" name="mi_plugin_facturacion_submit">Enviar</button>
        </form>
    </div>
    <?php

    return ob_get_clean(); // Devolver el contenido del búfer de salida
}
add_shortcode( 'mi_plugin_facturacion_formulario', 'mi_plugin_facturacion_formulario' );

// Función para mostrar los datos de facturación en el panel de administración
function mi_plugin_facturacion_panel_administracion() {
    add_menu_page(
        'Facturación',
        'Facturación',
        'manage_options',
        'mi-plugin-facturacion',
        'mi_plugin_facturacion_pagina_administracion',
        'dashicons-chart-area',
        30
    );
}
add_action( 'admin_menu', 'mi_plugin_facturacion_panel_administracion' );

// Función para mostrar la página de administración de facturación
function mi_plugin_facturacion_pagina_administracion() {
    // Verificar permisos de administrador
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Obtener los registros de la base de datos de facturación
    global $wpdb;
    $tabla_facturacion = $wpdb->prefix . 'datos_facturacion';
    $registros = $wpdb->get_results( "SELECT * FROM $tabla_facturacion" );

    // Mostrar los registros en una tabla
    ?>
    <div class="wrap">
        <h1>Facturación</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellidos</th>
                    <th>Email</th>
                    <th>Telefono</th>
                    <th>CFSI</th>
                    <th>Regimen Fiscal</th>
                    <th>RFC</th>
                    <th>Archivo PDF</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ( $registros as $registro ) {
                    ?>
                    <tr>
                        <td><?php echo $registro->id; ?></td>
                        <td><?php echo isset($registro->nombre_cliente) ? $registro->nombre_cliente : ''; ?></td>
                        <td><?php echo isset($registro->apellidos) ? $registro->apellidos : ''; ?></td>
                        <td><?php echo isset($registro->email_cliente) ? $registro->email_cliente : ''; ?></td>
                        <td><?php echo isset($registro->telefono) ? $registro->telefono : ''; ?></td>
                        <td><?php echo isset($registro->cfsi) ? $registro->cfsi : ''; ?></td>
                        <td><?php echo isset($registro->regimenf) ? $registro->regimenf : ''; ?></td>
                        <td><?php echo isset($registro->rfc) ? $registro->rfc : ''; ?></td>
                        <td><a href="<?php echo isset($registro->archivo_pdf) ? $registro->archivo_pdf : ''; ?>" target="_blank">Ver PDF</a></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}
