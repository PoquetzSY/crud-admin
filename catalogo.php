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
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['product_id'])) {
        $product_id = absint($_GET['product_id']);
        mostrar_formulario_editar($product_id);
        return; // Detener la ejecución del resto del código
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
?>
    <h2 class="Lp">Listado de Productos</h2>
    <div class="Lproducts">
        <?php
        // Obtener todos los productos de la base de datos
        $productos = $wpdb->get_results("SELECT * FROM $Ntabla ORDER BY producto");
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
                echo      '<button class="editar" onclick="window.location.href=\'?page=catalogo-productos&action=edit&product_id=' . $producto->id . '\'">Editar</button>';
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
function mostrar_formulario_editar($product_id) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    global $wpdb;
    $Ntabla = $wpdb->prefix . 'productos';
    $categorias_tabla = $wpdb->prefix . 'categorias';
    // Obtener el producto de la base de datos según su ID
    $product = $wpdb->get_row($wpdb->prepare("SELECT * FROM $Ntabla WHERE id = %d", $product_id));
    if (!$product) {
        echo 'El producto no existe.';
        return;
    }
    // Obtener todas las categorías de la base de datos
    $categorias = $wpdb->get_results("SELECT * FROM $categorias_tabla");
    if (isset($_POST['submit_edit'])){
        $id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $producto = sanitize_text_field($_POST['producto']);
        $categoria_id = sanitize_text_field($_POST['categoria']);
        $precio = sanitize_text_field($_POST['precio']);
        $descripcion = sanitize_textarea_field($_POST['descripcion']);
        $file = $_FILES['imagen']['tmp_name'];
        $imagen_id = 0;
        if (!empty($_FILES['imagen']['tmp_name'])) {
            $imagen_id = Subirimagen($_FILES['imagen']['tmp_name']);
        }else{
            $imagen_id = $product->imagen_id;
        }
        $wpdb->update(
            $Ntabla,
            array(
                'producto' => $producto,
                'categoria' => $categoria_id,
                'precio' => $precio,
                'descripcion' => $descripcion,
                'imagen_id' => $imagen_id,
            ),
            array('id' => $id)
        );
        ?>
            <script>
                window.location.href = '?page=catalogo-productos'; // Redireccionar a la página del catálogo después de guardar los cambios
            </script>
        <?php
    }    
    ?>
    <h2 class="Lp">Editar Producto</h2>
    <div class="formulario">
        <form id="editar-producto-form" method="post" enctype="multipart/form-data" class="form">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <label for="nombre">Nuevo nombre del producto:</label>
            <input type="text" name="producto" value="<?php echo esc_attr($product->producto); ?>">
            <br>
            <label for="precio">Nuevo precio del producto:</label>
            <input type="number" name="precio" value="<?php echo esc_attr($product->precio); ?>">
            <br>
            <label for="categoria">Nueva categoria del producto:</label>
            <select name="categoria">
                <?php
                foreach ($categorias as $categoria) {
                    $selected = ($product->categoria == $categoria->id) ? 'selected' : '';
                    echo '<option value="' . $categoria->id . '" ' . $selected . '>' . $categoria->nombre . '</option>';
                }
                ?>
            </select>
            <br>
            <label for="precio">Nueva descripcion del producto:</label>
            <input name="descripcion" value="<?php echo esc_attr($product->descripcion); ?>"><br>
            <label for="imagen">Imagen:</label>
                <?php
                    if ($product->imagen_id) {
                        $imagen_url = wp_get_attachment_image_src($product->imagen_id, 'thumbnail');
                        if ($imagen_url) {
                            echo '<img src="' . $imagen_url[0] . '" alt="Imagen del producto">';
                        }
                    }
                ?>
            <div id="drop-area" class="file-upload">
                <label for="imagen" class="custom-button">Seleccionar o Arrastrar Imágenes</label>
                <input type="file" name="imagen" id="imagen" accept=".jpg,.jpeg,.png" onchange="validateFileType()" multiple>
            </div>
            <ul id="preview-container" class="preview-list"></ul>
            <div class="botonform">
                <input type="submit" name="submit_edit" class="boton" value="Guardar cambios">
            </div>
        </form>
    </div>
    <?php
}
