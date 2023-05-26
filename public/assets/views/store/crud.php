<?php 
 
  $store_obj = new Store;
  $stores = $store_obj->index();
 
?>
<?php ob_start(); ?>


<!-- Button trigger modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
AGREGAR TIENDA
</button>

<!--MODAL INSERT-->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Crear tienda</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
    
                    <form id="crud_stores_form_create"  action="" method="POST">
                                <input type="text" name="action" value="create-store">
                                <input type="text" name="_wpnonce" value="<?php echo wp_create_nonce('create-store'); ?>">
                                <div class="form-group">
                                    <input type="text"  name="store_name" class="form-control" placeholder="Nombre">
                                </div>
                                <div class="form-group">
                                    <input type="text" name="store_address" rows="3" class="form-control">
                                </div>
                                <div class="form-group">
                                    <input type="text"  name="store_latitud"  class="form-control" placeholder="Latitud">
                                </div>
                                <div class="form-group">
                                    <input type="text"  name="store_longitud"  class="form-control" placeholder="Longitud">
                                </div>
                                <div class="form-group text-center">
                                    <input type="submit" class="btn btn-success">
                                </div>
                        </form> 

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

      </div>
    </div>
  </div>
</div>
<!--MODAL INSERT-->


<!--MODAL UPDATE-->
<div class="modal fade" id="storeUpdateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">

  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Actualizar tienda</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
    
                    <form id="crud_stores_form_update"  action="" method="POST">
                                <input type="text" name="action" value="update-store">
                                <input type="text" name="_wpnonce" value="<?php echo wp_create_nonce('update-store'); ?>">

                                <input type="text" name="store_id">

                                <div class="form-group">
                                    <input type="text"  name="store_name" class="form-control" placeholder="Nombre">
                                </div>
                                <div class="form-group">
                                    <input type="text" name="store_address" rows="3" class="form-control">
                                </div>
                                <div class="form-group">
                                    <input type="text"  name="store_latitud"  class="form-control" placeholder="Latitud">
                                </div>
                                <div class="form-group">
                                    <input type="text"  name="store_longitud"  class="form-control" placeholder="Longitud">
                                </div>
                                <div class="form-group text-center">
                                    <input type="submit" class="btn btn-warning" value="ACTUALIZAR INFORMACIÓN">
                                </div>
                        </form> 

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

      </div>
    </div>
  </div>
</div>
<!--MODAL UPDATE-->


<div class="container">
    <div class="row">
        <div class="col-xs-12">

             <div class="table-responsive">
                     <table class="table">
                           <thead>
                              
                              <th>NOMBE</th>
                              <th>LATITUD</th>
                              <th>LONGITUD</th>
                              <th>EDITAR</th>
                              <th>BORRAR</th>

                           </thead>
                           <tbody id="stores-table-body">
                              <?php foreach($stores as $store){ ?>
                                    <tr id="store_id_<?php echo $store->id ?>">
                                        <td><?php echo $store->name; ?></td>
                                        <td><?php echo $store->latitud; ?></td>

                                        <td><?php echo $store->latitud; ?></td>
                                        <td >
                                            <form class="crud_stores_form_show"  action="" method="POST">
                                              <input type="text" name="action" value="show-store">
                                              <input type="text" name="store_id" id="store_id" value="<?php echo $store->id ?>">
                                              <input type="submit" value="EDITAR" class="btn btn-warning">
                                            </form>
                                         <td>
                                         <a href="" class="btn btn-danger">BORRAR</a>
                                         </td>

                                    </tr>
                              <?php }  ?>
                           </tbody>
                     </table>
             </div>

        </div>
    </div>
</div>


<?php  $view_crud_store = ob_get_contents(); ?>
<?php ob_end_clean(); ?>
