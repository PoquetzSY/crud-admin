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

    if (!empty($carrito)) {
        $carrito_html = '<ul>';

        foreach ($carrito as $producto_id => $cantidad) {
            // Obtener los detalles del producto según su ID
            global $wpdb;
            $table_name = $wpdb->prefix . 'productos';
            $producto = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $producto_id));


            if ($producto) {
                $carrito_html .= '<li>';

                $carrito_html .= '<strong>Nombre:</strong> ' . ($producto->producto) . '<br>';
                $carrito_html .= '<strong>Precio:</strong> ' . ($producto->precio) . '<br>';
                $carrito_html .= '<strong>Cantidad:</strong> ' . ($cantidad) . '<br>';
                
            if ($producto->imagen_id) {
                $imagen_url = wp_get_attachment_image_src($producto->imagen_id, 'thumbnail');
                if ($imagen_url) {
                    $carrito_html .= '<img src="' . esc_url($imagen_url[0]) . '" alt="Imagen del producto">';
                }
            }
                // Formulario para eliminar el producto del carrito
                $carrito_html .= '<form method="post" action="' . esc_url(add_query_arg(array('action' => 'eliminar_del_carrito', 'producto_id' => $producto_id), home_url('/index.php'))) . '">';
                $carrito_html .= '<input type="hidden" name="producto_id" value="' . esc_attr($producto_id) . '" />';
                $carrito_html .= '<input type="submit" value="Eliminar" />';
                $carrito_html .= '</form>';

                $carrito_html .= '</li>';
            }
        }

        $carrito_html .= '</ul>';

        return $carrito_html;
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
    $carrito_html = '<h2>Carrito de Compras</h2>';
    $carrito_html .= mostrar_carrito();


    return $carrito_html;
}

// Registrar el shortcode [carrito_compras]
add_shortcode('carrito_compras', 'mostrar_carrito_shortcode');