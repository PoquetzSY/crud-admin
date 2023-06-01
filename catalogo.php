<?php
/*
Plugin Name: Catalogo de Productos
Plugin URI: 
Description: Plugin para la gestión de productos.
Version: 1.0
Author: Guiné Designers
Author URI:
License: GPL-2.0+
License URI: 
*/

// Función para agregar la página de administración al menú
add_action('admin_menu', 'catalogo');
function catalogo() {
    add_menu_page(
        'Catalogo de Productos',
        'Catalogo',
        'manage_options',
        'catalogo-productos',
        'mostrar_catalogo',
        'dashicons-list-view',
        30
    );
}
function mostrar_catalogo() {
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $Ntabla = $wpdb->prefix . 'productos';
    $categorias_tabla = $wpdb->prefix . 'categorias';
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
?>
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
                echo      '<button class="eliminar" onclick="window.location.href=\'?page=catalogo-productos&action=delete&product_id=' . $producto->id . '\'">Eliminar</button>';
                echo    '</div>';
                echo  '</div>';
                echo'</div>';
            }
        } else {
            echo 'No se encontraron productos.';
        }
        ?>
    </div>
<?php
}