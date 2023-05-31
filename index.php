<?php
/*
Plugin Name: Gestión de Productos
Plugin URI: 
Description: Plugin para gestión de productos.
Version: 1.0
Author: Isaac, Diego & Luis
Author URI: 
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

// Función para subir las imágenes y obtener sus IDs
function mi_plugin_handle_upload($image_data) {
  require_once ABSPATH . 'wp-admin/includes/image.php';
  require_once ABSPATH . 'wp-admin/includes/file.php';
  require_once ABSPATH . 'wp-admin/includes/media.php';

  $attachment_id = media_handle_sideload($image_data, 0);

  if (!is_wp_error($attachment_id)) {
      return $attachment_id;
  }

  return false;
}

// Mostrar la página de gestión de productos en el panel de administración
function mi_plugin_productos_page() {
  if (!current_user_can('manage_options')) {
      return;
  }

  global $wpdb;
  $table_name = $wpdb->prefix . 'productos';
  $categorias_table_name = $wpdb->prefix . 'categorias';

  // Procesar formulario de creación de producto
  if (isset($_POST['submit_create'])) {
      // Obtener los datos del formulario
      $producto = sanitize_text_field($_POST['producto']);
      $categoria_id = sanitize_text_field($_POST['categoria']);
      $precio = sanitize_text_field($_POST['precio']);
      $descripcion = sanitize_textarea_field($_POST['descripcion']);

      // Manejar la carga de las imágenes
      if (!empty($_FILES['images']['tmp_name'][0])) {
          $uploaded_image_ids = array();
          $image_files = $_FILES['images'];

          foreach ($image_files['tmp_name'] as $key => $tmp_name) {
              if (empty($tmp_name)) {
                  continue;
              }

              $image_data = array(
                  'name'     => $image_files['name'][$key],
                  'type'     => $image_files['type'][$key],
                  'tmp_name' => $tmp_name,
                  'error'    => $image_files['error'][$key],
                  'size'     => $image_files['size'][$key]
              );

              $attachment_id = mi_plugin_handle_upload($image_data);

              if ($attachment_id) {
                  $uploaded_image_ids[] = $attachment_id;
              }
          }

          // Guardar los IDs de las imágenes en la base de datos
          $imagen_ids = implode(',', $uploaded_image_ids);
      }
  }

    if (isset($_POST['submit_edit'])) {
        $id = absint($_POST['product_id']);
        $producto = sanitize_text_field($_POST['producto']);
        $categoria_id = sanitize_text_field($_POST['categoria']);
        $precio = sanitize_text_field($_POST['precio']);
        $descripcion = sanitize_textarea_field($_POST['descripcion']);
    
        $imagen_id = array();
        if (!empty($_FILES['imagen']['name'])) {
            $image_count = isset($_FILES['imagen']['name']) && is_array($_FILES['imagen']['name']) ? count($_FILES['imagen']['name']) : 0;
            for ($i = 0; $i < $image_count; $i++) {
                $imagen_id = mi_plugin_handle_upload($_FILES['imagen']['tmp_name'][$i]);
                if (!is_wp_error($imagen_id)) {
                    $imagen_id[] = $imagen_id;
                }
            }
        }
    
        $wpdb->update(
            $table_name,
            array(
                'producto' => $producto,
                'categoria' => $categoria_id,
                'precio' => $precio,
                'descripcion' => $descripcion,
                'imagen_id' => !empty($imagen_id) ? implode(',', $imagen_id) : '',
            ),
            array('id' => $id)
        );
    }

    // Procesar solicitud de eliminación de producto
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['product_id'])) {
        $id = absint($_GET['product_id']);
        $product = $wpdb->get_row($wpdb->prepare("SELECT imagen_id FROM $table_name WHERE id = %d", $id));
        if ($product && $product->imagen_id) {
            $image_ids = explode(',', $product->imagen_id);
            foreach ($image_ids as $image_id) {
                wp_delete_attachment($image_id, true);
            }
        }

        $wpdb->delete(
            $table_name,
            array('id' => $id)
        );
    }

    // Mostrar formulario de creación de producto
    ?>
    <style>
        .formulario{
            display: flex;
            flex-direction: column;
            width: 100%;
            align-items: center;
            margin: 20px
        }
        .form{
            background-color: lightgray;
            border-radius: 15px;
            width: 700px;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .producto{
            display: flex;
            flex-direction: column;
        }
        .box-p{
            display: flex;
            justify-content: space-between;
            width: 500px
        }
        label{
            font-weight: 400;
            font-size: 1rem;
            margin-bottom: 5px
        }
        a{
            text-decoration:none;
        }
        h1{
            font-size: 2rem;
            margin-bottom: 5px;
        }
        h2{
            font-size: 1.5rem
        }
        .boton{
            background-color: #2271b1;
            border: 0;
            width: 200px;
            font-size: 1rem;
            font-weight: light;
            padding: .7rem;
            border-radius: 10px;
            color: white;
            margin-top: 10px;
        }
        .boton:hover{
            cursor: pointer;
            background-color: #154c78;
        }
        .botonc{
            background-color: #e42222;
            border: 0;
            width: 200px;
            font-size: 1rem;
            font-weight: light;
            padding: .7rem;
            border-radius: 10px;
            color: white;
            margin-top: 10px;
        }
        .botonc:hover{
            cursor: pointer;
            background-color: #b12222;
        }
        input[type=text]{
            background-color: transparent;
            border: 0;
            border-bottom: 2px solid rgba(87, 87, 86, 0.8);
            border-radius: 0;
        }
        input[type=text]:focus,
        input[type=text]:active{
            outline: 0;
            box-shadow: none;
            border-color: #1d2327;
        }
        input[type=number]{
            background-color: transparent;
            border: 0;
            border-bottom: 2px solid rgba(87, 87, 86, 0.8);
            border-radius: 0;
        }
        input[type=number]:focus,
        input[type=number]:active{
            outline: 0;
            box-shadow: none;
            border-color: #1d2327;
        }
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
          -webkit-appearance: none;
          margin: 0;
        }
        .file-upload {
          position: relative;
          width: 700px;
          height: 100px;
          border: 2px dashed gray;
          margin: 20px auto;
          text-align: center;
          line-height: 100px;
          color: #888;
        }
        
        .custom-button {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          display: flex;
          justify-content: center;
          align-items: center;
          background-color: transparent;
          color: black;
          font-size: 16px;
          cursor: pointer;
          transition: background-color 0.3s ease;
        }
        
        .custom-button:hover {
          background-color: rgba(0, 0, 0, 0.1);
        }
        
        #imagen {
          display: none;
        }
        
        .preview-list {
          list-style: none;
          padding: 0;
          display: flex;
          flex-wrap: wrap;
          justify-content: space-evenly;
        }
        
        .image-card {
          width: 200px;
          margin: 10px;
          padding: 10px;
          border: 1px solid #ccc;
          border-radius: 4px;
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
          display: flex;
          align-items: center;
        }
        
        .preview {
          width: 70px;
          height: 70px;
          object-fit: cover;
          border-radius: 4px;
        }
        
        .image-info {
          flex-grow: 1;
          margin-left: 10px;
          overflow: hidden;
        }
        
        .image-name {
          font-weight: bold;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }
        .delete:hover{
            color:white;
            cursor: pointer;
            background-color: #b12222;
        }
        .delete{
            text-decoration:none;
            background-color: #e42222;
            border: 0;
            width: 200px;
            font-size: 1rem;
            font-weight: light;
            padding: .4em;
            border-radius: 10px;
            color: white;
            margin-top: 10px;
        }
        .delete-button {
          display: block;
          margin-top: 10px;
          background-color: #f44336;
          color: white;
          padding: 6px 12px;
          border: none;
          border-radius: 4px;
          cursor: pointer;
          transition: background-color 0.3s ease;
        }
        
        .delete-button:hover {
          background-color: #d32f2f;
        }
        #catalogo {
          display: flex;
          flex-wrap: wrap;
        }
        
        .producto {
          width: 200px;
          margin: 10px;
          padding: 10px;
          border: 1px solid #ccc;
          text-align: center;
          display: flex;
          flex-direction: column;
          align-items: center;
        }
        
        .producto img {
          width: 150px;
          height: 150px;
          object-fit: cover;
          margin-bottom: 10px;
          display: block;
          margin: 10px auto 0;
          border-radius: 10px;
        }
        
        .producto h3 {
          font-size: 18px;
          margin-bottom: 5px;
        }
        
        .producto p {
          font-size: 16px;
          margin-bottom: 10px;
        }
        
        .acciones button {
          margin: 10px 5px;
          padding: 5px 10px;
          border: none;
          border-radius: 5px;
          background-color: #eee;
          cursor: pointer;
          transition: background-color 0.3s ease;
        }

        .acciones button:hover {
          background-color: #dcdcdc;
        }

        .acciones button.editar {
          background-color: #ffc107;
        }

        .acciones button.editar:hover {
          background-color: #e6ac00;
        }

        .acciones button.eliminar {
          background-color: #dc3545;
          color: #fff;
        }

        .acciones button.eliminar:hover {
          background-color: #c82333;
        }
        .cat{
          display: flex;
          flex-wrap: wrap;
          justify-content: center;
        }
        h2{
          text-align: center;
        }
        .botones-f{
          display: flex;
          justify-content: center;
        }
    </style>
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
                    $categorias = $wpdb->get_results("SELECT id, nombre FROM $categorias_table_name");
         
                    foreach ($categorias as $categoria) {
                        echo '<option value="' . $categoria->id . '">' . $categoria->nombre . '</option>';
                    }
                    ?>
                </select>
                <br>
                <label for="descripcion">Descripción:</label>
                <textarea name="descripcion"></textarea>
                <div id="drop-area" class="file-upload">
                    <label for="imagen" class="custom-button">Seleccionar o Arrastrar Imágenes</label>
                    <input type="file" name="images[]" id="imagen" accept=".jpg,.jpeg,.png" onchange="validateFileType()" multiple>
                </div>
                <ul id="preview-container" class="preview-list"></ul>
                <input type="hidden" name="product_id" value="">
                <div class="botones-f">
                  <input type="submit" name="submit_create" class="boton" value="Agregar Producto">
                </div>
            </form>
        </div>
        <h2>Listado de Productos</h2>
        <div class="cat">
          <?php
          // Obtener todos los productos de la base de datos
          $productos = $wpdb->get_results("SELECT * FROM $table_name");

          if ($productos) {
              foreach ($productos as $producto) {
                  echo '<div id="catalogo">';
                  echo   '<div class="producto">';
                  echo     '<img src="https://th.bing.com/th/id/R.739ef233dbe8982a402ec6ca003e95c1?rik=%2fsks%2b8kT%2bGKfaA&pid=ImgRaw&r=0" alt="Nombre del producto">';
                  echo     '<h3>'.$producto->producto.'</h3>';
                  echo     '<p>Precio: $'.$producto->precio.'</p>';
                  echo     '<div class="acciones">';
                  echo      '<button class="editar">Editar</button>';
                  echo      '<button class="eliminar" onclick="window.location.href=\'?page=mi-plugin-productos&action=delete&product_id=' . $producto->id . '\'">Eliminar</button>';
                  echo     '</div>';
                  echo   '</div>';
                  echo '</div>';
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
    $table_name = $wpdb->prefix . 'productos';

    $products = $wpdb->get_results("SELECT * FROM $table_name");
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