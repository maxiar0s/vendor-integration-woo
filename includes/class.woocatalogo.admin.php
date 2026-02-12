<?php
if (!defined('ABSPATH')) exit;
/**
 * Opciones de Administración
 * @link        https://siroe.cl
 * @since       1.0.0
 * 
 * @package     base
 * @subpackage  base/include
 */


class cWooCatalogoAdmin {

    public static function fAdminNoticesWooCatalogo() {
        $error = get_transient('woocatalogo_nexsys_auth_error');
        if ($error) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo esc_html($error); ?></p>
            </div>
            <?php
            // Optionally delete after showing, or keep until success
            // delete_transient('woocatalogo_nexsys_auth_error'); 
        }
    }


    //funcion para registrar el menu del administrador
    public static function fCreateMenuWooCatalogo() {
        add_menu_page( 
            'Vendor Integration Woo', // Título de la página en el menú
            'Vendor Integration', // Texto que se mostrará en el menú
            'manage_options', // Capacidad requerida para ver la página (en este caso, usuarios con 'manage_options' pueden verla)
            'woocatalogo_options', // Identificador único de la página
            ['cWooCatalogoAdmin', 'fMenuWooCatalogo'], // Callback para mostrar el contenido de la página
            'dashicons-money-alt', // Icono que se mostrará junto al menú (en este caso, un icono de dinero)
            '65' // Posición en la que se mostrará el menú dentro del menú de WordPress
        );
        add_action('admin_notices', ['cWooCatalogoAdmin', 'fAdminNoticesWooCatalogo']);
    }
    
    public static function fCreateSubMenuWooCatalogo() {
        // Agregar el submenú a la página de ajustes de WordPress
        add_submenu_page(
                'woocatalogo_options', // Página padre (el mismo que usaste en add_menu_page)
                'Opciones Vendor Integration', // Título del submenú
                'Opciones', // Texto en el menú
                'manage_options', // Capacidad requerida para ver el submenú (puedes ajustarlo según tus necesidades)
                'woocatalogo_submenu', // Identificador único del submenú
                ['cWooCatalogoAdmin', 'fSubmenuWooCatalogo'] // Función que muestra el contenido del submenú
            );
        // Agregar el submenú a la página de ajustes de WordPress
        add_submenu_page(
            'woocatalogo_options', // Página padre (el mismo que usaste en add_menu_page)
            'Mis Fichas Tecnicas', // Título del submenú
            'Fichas Tecnicas', // Texto en el menú
            'manage_options', // Capacidad requerida para ver el submenú (puedes ajustarlo según tus necesidades)
            'woocatalogo_submenuficha', // Identificador único del submenú
            ['cWooCatalogoAdmin', 'fSubmenuFicha'] // Función que muestra el contenido del submenú
        );
        // Agregar el submenú a la página de ajustes de WordPress
        add_submenu_page(
            'woocatalogo_options', // Página padre (el mismo que usaste en add_menu_page)
            'Sincronizar productos', // Título del submenú
            'Sincronizar', // Texto en el menú
            'manage_options', // Capacidad requerida para ver el submenú (puedes ajustarlo según tus necesidades)
            'woocatalogo_updateproducts', // Identificador único del submenú
            ['cWooCatalogoAdmin', 'fSubmenuProduct'] // Función que muestra el contenido del submenú
        );

    
    }
    public static function fSubmenuWooCatalogo() {

        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        ?>
        <div class="viw-root">
        <div class="wrap">
            <h1><?php echo get_admin_page_title();  ?></h1>
        </div>
    
        <div class="card">

            <div class="gracias">
                <h3 class="title">Vendor Integration Woo</h3>
                <p>Vendor Integration Woo es un plugin para integrar productos de proveedores en tu tienda de WooCommerce.</p>
            </div>
        </div>
    
        <div class="wrap">
            <div class="card">
            <h2 class="title"><?php _e('Configuración Nexsys', 'vendor-integration-woo')?></h2>
            <form class="savelicense" id="fSaveLicenseWooCatalogo" method="post">
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="woocatalogo_nexsys_email"><?php _e('Email Nexsys', 'vendor-integration-woo')?></label>
                            </th>
                            <td>
                                <input type="email" class="regular-text ltr" name="woocatalogo_nexsys_email" id="woocatalogo_nexsys_email" placeholder="email@nexsys.com" value="<?php echo esc_attr( get_option('woocatalogo_nexsys_email') ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="woocatalogo_nexsys_password"><?php _e('Password Nexsys', 'vendor-integration-woo')?></label>
                            </th>
                            <td>
                                <input type="password" class="regular-text ltr" name="woocatalogo_nexsys_password" id="woocatalogo_nexsys_password" placeholder="********" value="<?php echo esc_attr( get_option('woocatalogo_nexsys_password') ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="woocatalogo_nexsys_country"><?php _e('País (Código)', 'vendor-integration-woo')?></label>
                            </th>
                            <td>
                                <select name="woocatalogo_nexsys_country" id="woocatalogo_nexsys_country">
                                    <option value="cl" <?php selected( get_option('woocatalogo_nexsys_country', 'cl'), 'cl' ); ?>>Chile (cl)</option>
                                    <option value="co" <?php selected( get_option('woocatalogo_nexsys_country'), 'co' ); ?>>Colombia (co)</option>
                                    <option value="pe" <?php selected( get_option('woocatalogo_nexsys_country'), 'pe' ); ?>>Perú (pe)</option>
                                    <option value="mx" <?php selected( get_option('woocatalogo_nexsys_country'), 'mx' ); ?>>México (mx)</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p>
                    <strong><?php _e('Nota:', 'vendor-integration-woo')?></strong> <?php _e('Ingresa tus credenciales de Nexsys y haz clic en <strong>Guardar Cambios</strong>.', 'vendor-integration-woo')?>
                </p>
                <p class="submit">
                    <input type="submit" name="submit" id="save_key_woocatalogo" class="button button-primary" value="<?php  _e('Guardar cambios', 'vendor-integration-woo')?>">
                </p>
            </form>
            </div>
        </div>
        </div>
        <?php
    }

    public static function fMenuWooCatalogo() {

        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        $config_woocatalogo = (new cWooCatalogoApiRequest())->fGetConfigValuesWooCatalogo();
        if ($config_woocatalogo) {
            $dolar = $config_woocatalogo[0]['dolar'];
            $comision = $config_woocatalogo[0]['comision'];
            $ganancia = $config_woocatalogo[0]['fmult'];
        }else{
            $dolar = "";
            $comision = "";
            $ganancia = "";
        }
        ?>
        <div class="viw-root">
        <div class="wrap">
            <h1><?php echo get_admin_page_title();  ?></h1>
        </div>

        <div class="wrap" style="max-width: 100%;margin: auto;padding: 35px;">
            <div class="card" style="max-width: 100%;">
                <h2 class="title">Mis Proveedores</h2>
                    <p class="submit opciones-woocatalogo">
                        <input type="submit" name="submit" id="fActualizarWooCatalogoJson" class="button button-primary" value="<?php  _e('Actualizar lista de productos', 'vendor-integration-woo')?>">
                        <input type="submit" name="submit" id="fUpdateStockWooCatalogo" class="button button-primary" value="<?php  _e('Actualizar Stock en Woocommerce', 'vendor-integration-woo')?>">
                        <input type="submit" name="submit" id="fUpdatePrecioWooCatalogo" class="button button-primary" value="<?php  _e('Actualizar Precio en Woocommerce', 'vendor-integration-woo')?>">
                        <input type="submit" name="submit" id="fOpenConfogModalWooCatalogo" class="button button-primary" value="<?php  _e('Configuración Global', 'vendor-integration-woo')?>">
                        <input type="submit" name="submit" id="fDownLoadCSVWooCatalogo" class="button button-primary" value="<?php  _e('Descargar Catalogo', 'vendor-integration-woo')?>">
<!--
                        <input type="submit" name="submit" id="fDespublicarProductosWooCatalogo" class="button button-primary" value="<?php  _e('Despublicar productos', 'vendor-integration-woo')?>">
                        <input type="submit" name="submit" id="fCreateProductWooCatalogo" class="button button-primary" value="<?php  _e('Crear Productos Nuevos', 'vendor-integration-woo')?>">
                        <input type="submit" name="submit" id="fMultiInsertWooCatalogo" class="button button-primary" value="<?php  _e('Insertar Productos seleccionados', 'vendor-integration-woo')?>">
                        <input type="submit" name="submit" id="fMultiDeleteWooCatalogo" class="button button-primary" value="<?php  _e('Eliminar Productos seleccionados', 'vendor-integration-woo')?>">
-->
                    </p>
            </div>
            <div class="table card" style="max-width: 100%;">
                <table id="WooCatalogoTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            
                            <th>WOO</th>
                            <th>ID</th>
                            <th>SKU Proveedor</th>
                            <th>PartNumber</th> 
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Categoría</th>
                            <th>Subcategoría</th>       
                            <th>Proveedor</th>     
                            <th>Producto Creado</th>
                            <th>Última Actualización</th>  
                            <th>Acciones</th> 
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th>WOO</th>
                            <th>ID</th>
                            <th>SKU Proveedor</th>
                            <th>PartNumber</th> 
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Categoría</th>
                            <th>Subcategoría</th>       
                            <th>Proveedor</th>     
                            <th>Producto Creado</th>
                            <th>Última Actualización</th>  
                            <th>Acciones</th> 
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <!--Configuración Global-->
        <div id="popup" class="cd-popup" role="alert">
            <div class="cd-popup-container">
            <div class="close" id="fCloseConfogModalWooCatalogo">
                <a href="#" id="close"><img src="<?php echo plugins_url('../admin/img/closewoocatalogo.png',__FILE__)?>"/></a>
            </div>
                <div>
                    <h2>Configuración Global</h2>
                    <div class="viw-modal-grid">
                        <div class="viw-modal-column-left">
                            <form class="saveconfig" id="fSaveConfigGlobWooCatalogo" method="post">

                                <div class="viw-form-group">
                                    <label for="ganancia-woocatalogo">Ganacia</label>
                                    <input type="number" id="gan-woocatalogo" name="ganancia-woocatalogo" step="0.0001" value="<?php echo esc_attr($ganancia); ?>">
                                </div>

                                <div class="viw-form-group">
                                    <label for="comision-woocatalogo">Comisión</label>
                                    <input type="number" id="comision-woocatalogo" name="comision-woocatalogo" step="0.0001" value="<?php echo esc_attr($comision); ?>">
                                </div>

                                <div class="viw-form-group">
                                    <label for="dolar-woocatalogo">Dolar</label>
                                    <input type="number" id="dolar-woocatalogo" name="dolar-woocatalogo" step="0.0001" value="<?php echo esc_attr($dolar); ?>">
                                </div>

                                <div class="viw-form-group">
                                    <label for="categories-woocatalogo">Etiquetas</label>
                                    <select name="categories-woocatalogo" id="categories-woocatalogo" style="width:100%">
                                    <?php
                                        $all_tags = get_terms ('product_tag');
                                        if($all_tags){
                                            foreach ($all_tags as $tag) {
                                                echo ' <option value="'.esc_attr($tag->name).'">'.esc_html($tag->name).'</option>';
                                            } 
                                        }else{
                                            echo ' <option value="false">Sin resultados</option>';
                                        }
    
                                    ?>
                                    </select>
                                </div>
                            
                                <input type="submit" name="submit" class="button button-primary" id="fSaveConfigWooCatalogo" value="<?php  _e('Guardar o Actualizar', 'vendor-integration-woo')?>">

                            </form>
                        </div>
                        <div class="viw-modal-column-right">
                            <div class="viw-formula-box">
                                <label><strong>Formula</strong></label><br>
                                <code> (PRECIO TECNOGLOBAL * DOLAR) * GANANCIA * COMISION</code>
                            </div>
                            <div class="viw-tags-history">
                                <label><strong>Etiquetas Guardadas</strong></label><br>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Dolar</th>
                                            <th>Ganancia</th>
                                            <th>Comision</th>
                                            <th>Etiquetas</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <?php
                                    
                                    $aOptionsWooCatalogo = (new cWooCatalogoApiRequest())->fGetConfigValuesWooCatalogo();

                                    foreach ($aOptionsWooCatalogo as $option_woocatalogo) {

                                        echo "<tr>";
                                            echo "<td>".esc_html($option_woocatalogo["id"])."</td>";
                                            echo "<td>".esc_html($option_woocatalogo["dolar"])."</td>";
                                            echo "<td>".esc_html($option_woocatalogo["fmult"])."</td>";
                                            echo "<td>".esc_html($option_woocatalogo["comision"])."</td>";
                                            echo "<td>".esc_html($option_woocatalogo["etiquetas_precio"])."</td>";
                                            echo "<td>".esc_html($option_woocatalogo["reg_date"])."</td>";
                                            echo "<td style='text-align: center;'><button type='button' aria-label='Eliminar Etiqueta' data-tooltip='Eliminar Etiqueta' onclick='fDeleteConfigWooCatalogo(".intval($option_woocatalogo["id"]).")'><span class='dashicons dashicons-remove'></span></button></td>";
                                        echo "</tr>";

                                    }
                                    
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="popup-overlay"></div>
        <div class="loader-woocatalogo centered-woocatalogo" style='display: none;'></div>

        <div class="cd-popup" role="alert">
            <div class="cd-popup-container">
                <div class="view-config"></div>
                <a href="#0" class="cd-popup-close"></a>
            </div> <!-- cd-popup-container -->
        </div> <!-- cd-popup -->
        </div>
        <?php
    }
    public static function fSubmenuFicha() {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        ?>
        <div class="viw-root">
        <div class="wrap">
            <h1><?php echo get_admin_page_title();  ?></h1>
        </div>

        <div class="wrap">
        <h1>Mis Productos  (Puedes intentar publicar informacion de productos que son de tu bodega)</h1>
        <table id="productosTableFicha" class="display">
            <thead>
                <tr>
                    <th>ID del Producto</th>
                    <th>SKU</th>
                    <th>Nombre del Producto</th>
                    <th>Stock</th>
                    <th>Precio Normal</th>
                    <th>Tipo</th>
                    <th>Categoría</th>
                    <th>Enlace al Producto</th>
                    <th>PartNumber</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Llamar a la función para obtener los productos y mostrarlos
                $args = array(
                    'status' => 'publish',
                    'limit' => -1,
                );
            
                $products = wc_get_products($args);
            
                foreach ($products as $product) {
                    $id = $product->get_id();
                    $sku = $product->get_sku();
                    $name = $product->get_name();
                    $stock = $product->get_stock_quantity();
                    $price = $product->get_regular_price();
                    $type = $product->is_type('simple') ? 'Simple' : 'Variable';
                    $categories = wc_get_product_category_list($id);
                    $product_link = get_permalink($id);
                    // Obtener PartNumber o mpn
                    $partnumber = $product->get_attribute('PartNumber');
                    if (!$partnumber) {
                        $partnumber = $product->get_attribute('mpn');
                    }
                    if (!$partnumber) {
                        $partnumber = 'Sin partnumber';
                    }
                ?>
                    <tr>
                        <td><?php echo $id; ?></td>
                        <td><?php echo esc_html($sku); ?></td>
                        <td><?php echo esc_html($name); ?></td>
                        <td><?php echo esc_html($stock); ?></td>
                        <td><?php echo esc_html($price); ?></td>
                        <td><?php echo $type; ?></td>
                        <td><?php echo $categories; ?></td>
                        <td><a href="<?php echo $product_link; ?>" target="_blank">Ver Producto</a></td>
                        <td><?php echo esc_html($partnumber); ?></td>
                        <!--<td><button class="accionBtn" data-id="<?php echo $id; ?>"><span class='dashicons dashicons-search'></span>Buscar ficha tecnica</button></td>-->
                        <td><button type="button" onclick="fUpdateAtrrWooCatalogo('<?php echo $partnumberv2 = ($partnumber != 'Sin partnumber') ? $partnumber : $sku ; ?>')"><span class="dashicons dashicons-search"></span>Buscar ficha tecnica</button></td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>

        <div class="popup-overlay"></div>
        <div class="loader-woocatalogo centered-woocatalogo" style='display: none;'></div>
    </div>
    <?php
    }
    public static function fSubmenuProduct() {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        ?>
        <div class="viw-root">
        <div class="wrap">
            <h1>Sincroniza stock y precio con los(el) proveedor(es)</h1>
            <div class="gnu-sincronizar">
                <header>
                    <h2>Sincroniza el stock y precio de tu tienda con los proveedores.</h2>
                    <p>NOTA: Los productos a actualizar son aquellos publicados mediante el plugin. El conteo se realiza considerando únicamente los que tienen completados los campos "SKU proveedor" y "Proveedor".</p>
                </header>
                <div id="progreso-actualizacion" style="margin: 20px 0px;background: #f2f2f2;">
                    <div id="barra-progreso" style="width: 0%; height: 30px; background-color: #0073aa;border: 1px solid #cccccc;border-radius: 3px;"></div>
                </div>
                <button id="iniciar-actualizacion" class="button button-primary">Iniciar Actualización</button>
                <p id="porcentaje-progreso" style="font-size: larger;">0% de productos revisados</p> 
                <p id="estado-progreso" style="font-size: larger;">0 productos actualizados -  0 productos encontrados</p> 
                
            </div>
        <div class="popup-overlay"></div>
        <div class="loader-woocatalogo centered-woocatalogo" style='display: none;'></div>
        </div>
        <?php

        
 
    }
    public static function fSaveConfigGlobalWooCatalogo($nonce) {

        $nonce = sanitize_text_field( $_POST['nonce'] );
        if (!wp_verify_nonce($nonce, 'woocatalogo_admin')) {
            wp_die(__('Security check failed.', 'vendor-integration-woo'), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'vendor-integration-woo'), 403);
        }

        global $wpdb;
        $aDataNumberWooCatalogo = isset($_POST['dataNumberWooCatalogo']) ? $_POST['dataNumberWooCatalogo'] : array();
        $sfmultWooCatalogo      = isset($aDataNumberWooCatalogo[0]['value']) ? floatval($aDataNumberWooCatalogo[0]['value']) : 0;
        $sComWooCatalogo        = isset($aDataNumberWooCatalogo[1]['value']) ? floatval($aDataNumberWooCatalogo[1]['value']) : 0;
        $sDolarWooCatalogo      = isset($aDataNumberWooCatalogo[2]['value']) ? floatval($aDataNumberWooCatalogo[2]['value']) : 0;
        $aTagWooCatalogo        = isset($aDataNumberWooCatalogo[3]['value']) ? sanitize_text_field($aDataNumberWooCatalogo[3]['value']) : '';
        $dTimeWooCatalogo       = current_time('mysql');
        $sTableWooCatalogo      = $wpdb->prefix.'woocatalogo';
        $qWooCatalogo           = "SELECT * FROM $sTableWooCatalogo";
        $sResWooCatalogo        = $wpdb->get_results($qWooCatalogo, ARRAY_A);
        $aResTagWooCatalogo        = array_search($aTagWooCatalogo, array_column($sResWooCatalogo, 'etiquetas_precio'));
        

        if ($aResTagWooCatalogo === 0 || $aResTagWooCatalogo > 0) {

            $wpdb->query($wpdb->prepare("UPDATE {$sTableWooCatalogo} SET `dolar` = %f, `fmult` = %f, `comision` = %f WHERE `etiquetas_precio` = %s", $sDolarWooCatalogo, $sfmultWooCatalogo, $sComWooCatalogo, $aTagWooCatalogo));
            echo "Actualizado";
            wp_die();

        }

        if ($aResTagWooCatalogo === false) {
            if($aTagWooCatalogo != "false"){
                $sInsertWooCatalogo = array('dolar' => $sDolarWooCatalogo, 'fmult' => $sfmultWooCatalogo,'comision' => $sComWooCatalogo, 'etiquetas_precio' => $aTagWooCatalogo,'reg_date'=> $dTimeWooCatalogo );
                $wpdb->insert($sTableWooCatalogo,$sInsertWooCatalogo);
                echo "Creado";
            }else{
                echo "Debes crear por lo menos una etiqueta";
            }
            wp_die();
            
        }

        echo "Hubo un error al actualizar las etiquetas";
    }
    public static function fDeleteConfigGlobalWooCatalogo($nonce) {

        $nonce = sanitize_text_field( $_POST['nonce'] );
        if (!wp_verify_nonce($nonce, 'woocatalogo_admin')) {
            wp_die(__('Security check failed.', 'vendor-integration-woo'), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'vendor-integration-woo'), 403);
        }

        $idreg = isset($_POST['idreg']) ? sanitize_text_field($_POST['idreg']) : '';
        if (!empty($idreg)) {
            global $wpdb;
            $wpdb->delete(
                $wpdb->prefix . 'woocatalogo',
                ['id' => $idreg],
                ['%d']
            );
            echo "Registro eliminado";
        }else{
            echo "Hubo un error";
        }
    
        wp_die();
    }
    public static function fSaveLicenseWooCatalogo($nonce){

        $nonce = sanitize_text_field( $_POST['nonce'] );
        if (!wp_verify_nonce($nonce, 'woocatalogo_admin')) {
            wp_die(__('Security check failed.', 'vendor-integration-woo'), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'vendor-integration-woo'), 403);
        }
        $aDataLicenseWooCatalogo = isset($_POST['dataLicenseWooCatalogo']) ? $_POST['dataLicenseWooCatalogo'] : array();
        
        // Extract Nexsys form data
        $nexsys_email = '';
        $nexsys_password = '';
        $nexsys_country = 'cl';

        foreach ($aDataLicenseWooCatalogo as $field) {
            if ($field['name'] === 'woocatalogo_nexsys_email') $nexsys_email = $field['value'];
            if ($field['name'] === 'woocatalogo_nexsys_password') $nexsys_password = $field['value'];
            if ($field['name'] === 'woocatalogo_nexsys_country') $nexsys_country = $field['value'];
        }

        if (!empty($nexsys_email)) update_option('woocatalogo_nexsys_email', sanitize_email($nexsys_email));
        if (!empty($nexsys_password)) update_option('woocatalogo_nexsys_password', sanitize_text_field($nexsys_password));
        if (!empty($nexsys_country)) update_option('woocatalogo_nexsys_country', sanitize_text_field($nexsys_country));

        // Delete token to force re-authentication
        delete_transient('woocatalogo_nexsys_token');
        delete_transient('woocatalogo_nexsys_auth_error');

        echo "Configuración Nexsys guardada";
        wp_die();
    }
    
    public static function fPluginActivationWooCatalogo() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $table_name = $wpdb->prefix . 'woocatalogo';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
            dolar FLOAT NOT NULL,
            fmult FLOAT NOT NULL,
            comision FLOAT NOT NULL,
            etiquetas_precio VARCHAR(255) NOT NULL,
            reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        dbDelta($sql);

        // Only insert defaults if table is empty
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
        if ($count == 0) {
            $wpdb->insert(
                $table_name,
                array(
                    'dolar' => 90000000,
                    'fmult' => 1.12,
                    'comision' => 1.21,
                    'etiquetas_precio' => 'Bodega Externa'
                ),
                array('%f', '%f', '%f', '%s')
            );
        }
    }
    

	public static function fPluginDeactivationWooCatalogo() {
        // No longer drops table on deactivation — data is preserved
        // Table cleanup is handled in uninstall.php
	}


    public static function fAjaxEndpointWooCatalogo(){
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'woocatalogo_admin')) {
             wp_die(__('Security check failed.', 'vendor-integration-woo'), 403);
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'vendor-integration-woo'), 403);
        }

        $json_file = WOOCATALOGO__PLUGIN_DIR . 'admin/dataWooCatalogo/dataWooCatalogo.json';
        if (file_exists($json_file)) {
            header('Content-Type: application/json');
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local file
            echo file_get_contents($json_file);
        } else {
            echo wp_json_encode(array('data' => array()));
        }
        wp_die();
	}
}

 



