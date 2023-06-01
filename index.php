<?php
/*
Plugin Name: Gestión de Productos
Plugin URI: https://www.ejemplo.com
Description: Plugin para gestión de productos con estilo B).
Version: 1.0 
Author: Isaac, Diego & Luis
Author URI: 
License: GPLv2 or later
Text Domain: plugin
*/

// Crear la tabla de productos al activar el plugin
register_activation_hook(__FILE__, 'Crear_productos');
function Crear_productos() {
    global $wpdb;
    $Ntabla = $wpdb->prefix . 'productos';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $Ntabla (
        id INT(11) NOT NULL AUTO_INCREMENT,
        producto VARCHAR(100) NOT NULL,
        categoria VARCHAR(100) NOT NULL,
        precio INT NOT NULL,
        descripcion TEXT,
        imagen_id int(11) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
}

function styles() {
  // Enlazar tu hoja de estilos
  wp_enqueue_style( 'style', plugins_url( 'public\assets\css\style.css',__FILE__) );
}
add_action( 'admin_enqueue_scripts', 'styles' );

function scripts() {
    // Enlazar tu archivo JavaScript
    wp_enqueue_script( 'custom-script', plugins_url( 'public\assets\js\main.js', __FILE__ ), array( 'jquery' ), '1.0', true );
}
add_action( 'admin_enqueue_scripts', 'scripts' );
  

// Agregar menú de gestión de productos en el panel de administración
add_action('admin_menu', 'plugin_menu');
function plugin_menu() {
    add_menu_page(
        'Gestión de Productos',
        'Productos',
        'manage_options',
        'mi-plugin-productos',
        'mostrar_pagina',
        'dashicons-book',
        30
    );
}

// Función para subir la imagen y obtener su ID
function Subirimagen($file) {
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    
    $attachment_id = media_handle_upload('imagen', 0);

  if (!is_wp_error($attachment_id)) {
      return $attachment_id;
  }

  return false;
}

// Mostrar la página de gestión de productos en el panel de administración
function mostrar_pagina() {
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $Ntabla = $wpdb->prefix . 'productos';
    $categorias_tabla = $wpdb->prefix . 'categorias';

    // Procesar formulario de creación de producto
    if (isset($_POST['subir-crear'])) {
        $producto = sanitize_text_field($_POST['producto']);
        $categoria_id = sanitize_text_field($_POST['categoria']);
        $precio = sanitize_text_field($_POST['precio']);
        $descripcion = sanitize_textarea_field($_POST['descripcion']);
        $file = $_FILES['imagen']['tmp_name'];
        $imagen_id = 0;
        if (!empty($_FILES['imagen']['tmp_name'])) {
            $imagen_id = Subirimagen($_FILES['imagen']['tmp_name']);
        }
    
        $wpdb->insert(
            $Ntabla,
            array(
                'producto' => $producto,
                'categoria' => $categoria_id,
                'precio' => $precio,
                'descripcion' => $descripcion,
                'imagen_id' => $imagen_id,
            )
        );
    }


    // Procesar solicitud de eliminación de producto
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['product_id'])) {
        $id = absint($_GET['product_id']);
        $product = $wpdb->get_row($wpdb->prepare("SELECT imagen_id FROM $Ntabla WHERE id = %d", $id));
        if ($product && $product->imagen_id) {
            $image_ids = explode(',', $product->imagen_id);
            foreach ($image_ids as $image_id) {
                wp_delete_attachment($image_id, true);
            }
        }

        $wpdb->delete(
            $Ntabla,
            array('id' => $id)
        );
    }

    // Mostrar formulario de creación de producto
    ?>

    <div class="formulario">
        <h1>Gestión de Productos</h1>

        <h2>Agregar Producto</h2>
        <form method="POST" enctype="multipart/form-data" class="form">
            <label for="producto">Nombre del Producto:</label>
            <input type="text" name="producto" required>
            <br>
            <label for="precio">Precio:</label>
            <input type="number" name="precio" required>
            <br>
            <label for="categoria">Categoría:</label>
            <select name="categoria" required>
                <?php
                // Obtener todas las categorías de la base de datos
                $categorias = $wpdb->get_results("SELECT id, nombre FROM $categorias_tabla");

                foreach ($categorias as $categoria) {
                    echo '<option value="' . esc_attr($categoria->id) . '">' . esc_html($categoria->nombre) . '</option>';
                }
                ?>
            </select>
            <br>
            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion"></textarea>
            <div id="drop-area" class="file-upload">
                <label for="imagen" class="custom-button">Seleccionar o Arrastrar Imágenes</label>
                <input type="file" name="imagen" id="imagen" accept=".jpg,.jpeg,.png" onchange="validateFileType()" multiple>
            </div>
            <ul id="preview-container" class="preview-list"></ul>
            <div class="botonform">
                <input type="submit" name="subir-crear" class="boton" value="Agregar Producto">
            </div>
        </form>
    </div>
    <h2 class="Lp">Listado de Productos</h2>
    <div class="Lproducts">
        <?php
        // Obtener todos los productos de la base de datos
        $productos = $wpdb->get_results("SELECT * FROM $Ntabla");
        if ($productos) {
            foreach ($productos as $producto) {
                echo '<div id="catalogo">';    
                echo  '<div class="producto">';
                if ($producto->imagen_id) {
                    $imagen_url = wp_get_attachment_image_src($producto->imagen_id, 'thumbnail');
                    if ($imagen_url) {
                        echo '<img src="' . $imagen_url[0] . '" alt="Imagen del producto">';
                    }
                }
                echo    '<h3>'. $producto->producto .'</h3>';
                $categoria = $wpdb->get_row($wpdb->prepare("SELECT nombre FROM $categorias_tabla WHERE id = %d", $producto->categoria));
                    if ($categoria) {
                        echo '<p>' . $categoria->nombre . '</p>';
                    }
                echo    '<p>$'. $producto->precio .'</p>';
                echo    '<div class="acciones">';
                echo      '<button class="editar">Editar</button>';
                echo      '<button class="eliminar" onclick="window.location.href=\'?page=mi-plugin-productos&action=delete&product_id=' . $producto->id . '\'">Eliminar</button>';
                echo    '</div>';
                echo  '</div>';
                echo'</div>';
            }
        } else {
            echo 'No se encontraron productos.';
        }
        ?>
    </div>
<script>
    
</script>
    <?php
}
// Shortcode para mostrar la lista de productos en una página de WordPress
add_shortcode('mostrar_productos', 'mostrar_productos_shortcode');
function mostrar_productos_shortcode() {
    global $wpdb;
    $Ntabla = $wpdb->prefix . 'productos';

    $products = $wpdb->get_results("SELECT * FROM $Ntabla");
    if ($products) {
        ob_start();
        ?>
        <div class="products-list">
            <?php foreach ($products as $product) : ?>
                <div class="product">
                    <h3><?php echo esc_html($product->producto); ?></h3>
                    <p>Categoría: <?php echo esc_html($product->categoria); ?></p>
                    <p>Precio: <?php echo $product->precio; ?></p>
                    <div class="product-images">
                        <?php
                        $image_ids = explode(',', $product->imagen_id);
                        foreach ($image_ids as $image_id) {
                            echo wp_get_attachment_image($image_id, 'thumbnail');
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}