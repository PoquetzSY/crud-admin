<?php
/*
Plugin Name: Carrito de Compras
Plugin URI: Tu_URL
Description: Un plugin para gestionar un carrito de compras personalizado.
Version: 1.0
Author: Tu_Nombre
Author URI: Tu_URL
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

// Función para agregar productos al carrito
function agregar_al_carrito($producto_id, $cantidad = 1) {
    $carrito = obtener_carrito_de_cookies();

    // Verificar si el producto ya está en el carrito
    if (isset($carrito[$producto_id])) {
        // Si el producto ya está en el carrito, aumentar la cantidad
        $carrito[$producto_id] += $cantidad;
    } else {
        // Si el producto no está en el carrito, agregarlo
        $carrito[$producto_id] = $cantidad;
    }

    // Guardar el carrito en las cookies
    guardar_carrito_en_cookies($carrito);
}

// Función para obtener el carrito de las cookies
function obtener_carrito_de_cookies() {
    $carrito_serializado = isset($_COOKIE['carrito_compras']) ? $_COOKIE['carrito_compras'] : '';
    $carrito = unserialize($carrito_serializado);
    return is_array($carrito) ? $carrito : array();
}

// Función para guardar el carrito en las cookies
function guardar_carrito_en_cookies($carrito) {
    $carrito_serializado = serialize($carrito);
    setcookie('carrito_compras', $carrito_serializado, time() + (86400 * 30), '/', '', false, true); // Caducidad de la cookie: 30 días
}

// Modificar la función mostrar_carrito para agregar el botón de eliminación
function mostrar_carrito() {
    $carrito = obtener_carrito_de_cookies();
    echo '<div class="d-flex cartB">';
    echo     '<h3>Carrito de Compras</h3>';
    echo     '<button onclick="w3_close()" class="cerrar"><i class="bi bi-x"></i></button>';
    echo '</div>';
    if (!empty($carrito)) {
        foreach ($carrito as $producto_id => $cantidad) {
            // Obtener los detalles del producto según su ID
            global $wpdb;
            $table_name = $wpdb->prefix . 'productos';
            $producto = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $producto_id));

            echo '<div class="cartL">';
                    
            echo '<div class="imgC">';
            if ($producto->imagen_id) {
                $imagen_url = wp_get_attachment_image_src($producto->imagen_id, 'thumbnail');
                if ($imagen_url) {
                    echo '<img src="' . $imagen_url[0] . '" alt="Imagen del producto" class="imageC"">';
                }
            }
            echo '</div>';
            if ($producto) {
                echo '<div class="desC">';
                
                // Formulario para eliminar el producto del carrito
                echo '<strong>'.$producto->producto.'</strong>';
                echo     '<div class="contC">';
                echo         '<input type="button" value="-" onclick="less()" class="btnC">';
                echo         '<p id="count" class="img-thumbnail number">'.$cantidad.'</p>';
                echo         '<input type="button" value="+" onclick="increment()" class="btnC"/>';
                echo     '</div>';
                echo '</div>';
                echo '<div class="delC">';
                echo     '<strong>$'.($producto->precio)*$cantidad.'</strong>';
                echo     '<form method="post" action="' . esc_url(add_query_arg(array('action' => 'eliminar_del_carrito', 'producto_id' => $producto_id), home_url('/index.php'))) . '">';
                echo     '<input type="hidden" name="producto_id" value="' . esc_attr($producto_id) . '" />';
                echo     '<input type="submit" value="Eliminar" />';
                echo     '</form>';
                echo '</div>';
            }
            echo '</div>';
        }
    } else {
        return 'El carrito está vacío.';
    }
}


// Función para eliminar un producto del carrito
function eliminar_del_carrito($producto_id) {
    $carrito = obtener_carrito_de_cookies();

    if (isset($carrito[$producto_id])) {
        unset($carrito[$producto_id]);
        guardar_carrito_en_cookies($carrito);
    }
}

// Función para mostrar el carrito de compras mediante un shortcode
function mostrar_carrito_shortcode() {
    echo mostrar_carrito();
}

// Registrar el shortcode [carrito_compras]
add_shortcode('carrito_compras', 'mostrar_carrito_shortcode');