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
    session_start();

    // Verificar si el carrito ya existe en la sesión
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = array();
    }

    // Verificar si el producto ya está en el carrito
    if (isset($_SESSION['carrito'][$producto_id])) {
        // Si el producto ya está en el carrito, aumentar la cantidad
        $_SESSION['carrito'][$producto_id] += $cantidad;
    } else {
        // Si el producto no está en el carrito, agregarlo
        $_SESSION['carrito'][$producto_id] = $cantidad;
    }
}

// Función para mostrar los productos en el carrito
function mostrar_carrito() {

    // Verificar si el carrito existe en la sesión
    if (isset($_SESSION['carrito'])) {
        $carrito_html = '<ul>';

        foreach ($_SESSION['carrito'] as $producto_id => $cantidad) {
            // Obtener los detalles del producto según su ID
            $producto = get_post($producto_id);

            if ($producto) {
                $carrito_html .= '<li>';
                $carrito_html .= '<strong>Nombre:</strong> ' . esc_html($producto->post_title) . '<br>';
                $carrito_html .= '<strong>Precio:</strong> ' . esc_html(get_post_meta($producto_id, 'precio', true)) . '<br>';
                $carrito_html .= '<strong>Cantidad:</strong> ' . esc_html($cantidad) . '<br>';
                $carrito_html .= '</li>';
            }
        }

        $carrito_html .= '</ul>';

        return $carrito_html;
    } else {
        return 'El carrito está vacío.';
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