<?php
/*
Plugin Name: Gestión de Productos
Plugin URI: https://www.ejemplo.com
Description: Plugin para gestión de productos.
Version: 1.0
Author: Isaac, Diego & Luis
Author URI: https://www.tunombre.com
License: GPLv2 or later
Text Domain: mi-plugin
*/

// Crear la tabla de productos al activar el plugin
register_activation_hook(__FILE__, 'mi_plugin_create_table');
function mi_plugin_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'productos';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        producto VARCHAR(100) NOT NULL,
        categoria VARCHAR(100) NOT NULL,
        precio INT NOT NULL,
        descripcion TEXT,
        imagen_id VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    $result = dbDelta($sql);
    if (is_wp_error($result)) {
        echo $result->get_error_message();
    }
}

// Agregar menú de gestión de productos en el panel de administración
add_action('admin_menu', 'mi_plugin_menu');
function mi_plugin_menu() {
    add_menu_page(
        'Gestión de Productos',
        'Productos',
        'manage_options',
        'mi-plugin-productos',
        'mi_plugin_productos_page',
        'dashicons-book',
        30
    );
}

// Función para subir la imagen y obtener su ID
function mi_plugin_handle_upload($file) {
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $attachment_id = media_handle_upload($file, 0, array());

    if (is_wp_error($attachment_id)) {
        // Error al subir la imagen, puedes manejar el error aquí si es necesario
        return false;
    }

    return $attachment_id;
}


// Mostrar la página de gestión de productos en el panel de administración
function mi_plugin_productos_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'productos';
    $categorias_table_name = $wpdb->prefix . 'categorias';

    // Procesar formulario de creación o edición de producto
    if (isset($_POST['submit_create']) || isset($_POST['submit_edit'])) {
        $id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $producto = sanitize_text_field($_POST['producto']);
        $categoria_id = sanitize_text_field($_POST['categoria']);
        $precio = sanitize_text_field($_POST['precio']);
        $descripcion = sanitize_textarea_field($_POST['descripcion']);

        $imagen_id = array();
        if (!empty($_FILES['imagen']['name'])) {
            $image_count = isset($_FILES['imagen']['name']) && is_array($_FILES['imagen']['name']) ? count($_FILES['imagen']['name']) : 0;
            for ($i = 0; $i < $image_count; $i++) {
                $file = array(
                    'name' => $_FILES['imagen']['name'][$i],
                    'type' => $_FILES['imagen']['type'][$i],
                    'tmp_name' => $_FILES['imagen']['tmp_name'][$i],
                    'error' => $_FILES['imagen']['error'][$i],
                    'size' => $_FILES['imagen']['size'][$i]
                );
            }
        }

        // Crear o actualizar el producto en la base de datos
        if (isset($_POST['submit_create'])) {
            $wpdb->insert(
                $table_name,
                array(
                    'producto' => $producto,
                    'categoria' => $categoria_id,
                    'precio' => $precio,
                    'descripcion' => $descripcion,
                    'imagen_id' => implode(',', $imagen_id)
                )
            );
        } elseif (isset($_POST['submit_edit'])) {
            $wpdb->update(
                $table_name,
                array(
                    'producto' => $producto,
                    'categoria' => $categoria_id,
                    'precio' => $precio,
                    'descripcion' => $descripcion
                ),
                array('id' => $id)
            );
        }
    }

    // Procesar acción de borrado de producto
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['product_id'])) {
        $product_id = absint($_GET['product_id']);
        $wpdb->delete($table_name, array('id' => $product_id));
    }

    // Obtener todos los productos de la base de datos
    $productos = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    // Obtener todas las categorías de la base de datos
    $categorias = $wpdb->get_results("SELECT * FROM $categorias_table_name", ARRAY_A);
    $categorias_dropdown = array();
    foreach ($categorias as $categoria) {
        $categorias_dropdown[$categoria['id']] = $categoria['nombre'];
    }

    // Mostrar formulario de creación o edición de producto
    if (isset($_GET['action']) && ($_GET['action'] === 'create' || $_GET['action'] === 'edit') && isset($_GET['product_id'])) {
        $action = $_GET['action'];
        $product_id = absint($_GET['product_id']);

        $producto_data = array(
            'producto' => '',
            'categoria' => '',
            'precio' => '',
            'descripcion' => '',
            'imagen_id' => ''
        );

        if ($action === 'edit') {
            $producto_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $product_id), ARRAY_A);
            if (!$producto_data) {
                // El producto no existe, puedes manejar el error aquí si es necesario
            }
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html($action === 'create' ? 'Crear Producto' : 'Editar Producto'); ?></h1>

            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="producto">Producto</label></th>
                            <td><input type="text" name="producto" id="producto" value="<?php echo esc_attr($producto_data['producto']); ?>" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="categoria">Categoría</label></th>
                            <td>
                                <select name="categoria" id="categoria" required>
                                    <option value="">Seleccionar categoría</option>
                                    <?php foreach ($categorias_dropdown as $categoria_id => $categoria_nombre) : ?>
                                        <option value="<?php echo esc_attr($categoria_id); ?>" <?php selected($categoria_id, $producto_data['categoria']); ?>><?php echo esc_html($categoria_nombre); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="precio">Precio</label></th>
                            <td><input type="number" name="precio" id="precio" value="<?php echo esc_attr($producto_data['precio']); ?>" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="descripcion">Descripción</label></th>
                            <td><textarea name="descripcion" id="descripcion" required><?php echo esc_textarea($producto_data['descripcion']); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="imagen">Imágenes</label></th>
                            <td>
                                <input type="file" name="imagen[]" id="imagen" multiple>
                                <?php if ($producto_data['imagen_id']) : ?>
                                    <p>Imágenes actuales:</p>
                                    <?php $imagenes = explode(',', $producto_data['imagen_id']); ?>
                                    <?php foreach ($imagenes as $imagen_id) : ?>
                                        <?php echo wp_get_attachment_image($imagen_id, 'thumbnail'); ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php submit_button($action === 'create' ? 'Crear' : 'Guardar', 'primary', $action === 'create' ? 'submit_create' : 'submit_edit'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=mi-plugin-productos')); ?>" class="button">Cancelar</a>
            </form>
        </div>
        <?php
    } else {
        // Mostrar lista de productos
        ?>
        <div class="wrap">
            <h1>Lista de Productos</h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=mi-plugin-productos&action=create')); ?>" class="page-title-action">Crear Producto</a>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col">Producto</th>
                        <th scope="col">Categoría</th>
                        <th scope="col">Precio</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto) : ?>
                        <tr>
                            <td><?php echo esc_html($producto['producto']); ?></td>
                            <td><?php echo esc_html($categorias_dropdown[$producto['categoria']]); ?></td>
                            <td><?php echo esc_html($producto['precio']); ?></td>
                            <td>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=mi-plugin-productos&action=edit&product_id=' . $producto['id'])); ?>">Editar</a>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=mi-plugin-productos&action=delete&product_id=' . $producto['id'])); ?>" onclick="return confirm('¿Estás seguro de que deseas borrar este producto?')">Borrar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
