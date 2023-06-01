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

// Agregar menú de gestión de productos en el panel de administración   
add_action('admin_menu', 'plugin_menu');
function styles() {
    // Enlazar tu hoja de estilos
    wp_enqueue_style( 'style', plugins_url( 'public\assets\css\style.css', __FILE__ ) );
}
add_action( 'admin_enqueue_scripts', 'styles' );
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
    function validateFileType(){
        var fileName = document.getElementById("imagen").value;
        var idxDot = fileName.lastIndexOf(".") + 1;
        var extFile = fileName.substr(idxDot, fileName.length).toLowerCase();
        if (extFile=="jpg" || extFile=="jpeg" || extFile=="png"){
            //TO DO
        }else{
            alert("Solamente archivos .jpg/jpeg y .png están permitidos!");
            var fileName = document.getElementById("imagen").value = '';
        }   
    }
    // Obtener elementos del DOM
    const dropArea = document.getElementById('drop-area');
    const uploadInput = document.getElementById('imagen');
    const previewContainer = document.getElementById('preview-container');

    // Manejar el evento de selección de archivos
    uploadInput.addEventListener('change', handleFileSelect);

    // Manejar los eventos de arrastrar y soltar archivos
    dropArea.addEventListener('dragenter', handleDragEnter);
    dropArea.addEventListener('dragover', handleDragOver);
    dropArea.addEventListener('dragleave', handleDragLeave);
    dropArea.addEventListener('drop', handleDrop);

    // Manejar el evento de selección de archivos
    function handleFileSelect(event) {
      const files = event.target.files;
      previewImages(files);
    }

    // Manejar los eventos de arrastrar y soltar archivos
    function handleDragEnter(event) {
      event.preventDefault();
      dropArea.classList.add('drag-over');
    }

    function handleDragOver(event) {
      event.preventDefault();
    }

    function handleDragLeave(event) {
      dropArea.classList.remove('drag-over');
    }

    function handleDrop(event) {
      event.preventDefault();
      dropArea.classList.remove('drag-over');
      const files = event.dataTransfer.files;
      previewImages(files);
    }

    // Mostrar las imágenes seleccionadas o arrastradas en la lista de previsualización
    function previewImages(files) {
      const maxFiles = 4;
      const totalFiles = previewContainer.children.length + files.length;

      if (totalFiles > maxFiles) {
        alert(`Solo se puede subir como máximo ${maxFiles} foto.`);
        return;
      }

      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();

        reader.onload = function (event) {
          const imageSrc = event.target.result;
          const imageCard = createImageCard(imageSrc, file.name);
          previewContainer.appendChild(imageCard);
        };

        reader.readAsDataURL(file);
      }
    }

    // Crear una tarjeta de imagen para la previsualización
    function createImageCard(imageSrc, imageName) {
      const imageCard = document.createElement('li');
      imageCard.className = 'image-card';

      const image = document.createElement('img');
      image.src = imageSrc;
      image.alt = imageName;
      image.className = 'preview';
      image.style.width = '70px';
      image.style.height = '70px';

      const imageInfo = document.createElement('div');
      imageInfo.className = 'image-info';

      const imageNameContainer = document.createElement('div');
      imageNameContainer.className = 'image-name-container';

      const imageNameText = document.createElement('p');
      imageNameText.className = 'image-name';
      imageNameText.textContent = imageName;
      imageNameText.style.overflow = 'hidden';
      imageNameText.style.textOverflow = 'ellipsis';
      imageNameText.style.whiteSpace = 'nowrap';
      imageNameText.style.maxWidth = '100px';

      const deleteButton = document.createElement('button');
      deleteButton.className = 'delete-button';
      deleteButton.textContent = 'Eliminar';
      deleteButton.addEventListener('click', function () {
        imageCard.remove();
      });

      imageNameContainer.appendChild(imageNameText);
      imageInfo.appendChild(imageNameContainer);
      imageInfo.appendChild(deleteButton);

      imageCard.appendChild(image);
      imageCard.appendChild(imageInfo);

      return imageCard;
    }
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
                    <p>Descripción: <?php echo esc_html($product->descripcion); ?></p>
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