jQuery(document).ready(function($) {
    $('#iniciar-actualizacion').on('click', function() {
        var $boton = $(this);
        var $barraProgreso = $('#barra-progreso');
        var $estadoProgreso = $('#estado-progreso');
        var $porcentajeProgreso = $('#porcentaje-progreso');
        var $contenedorProgreso = $('#progreso-actualizacion');

        $boton.prop('disabled', true);
        //$contenedorProgreso.show();

        var totalProductos = 0;
        var productosActualizados = 0;
        var productosProcesados = 0;
        var tamanoLote = 50;

        function actualizarLote(offset) {
            $.ajax({
                url: VIW_datosActualizacion.ajax_url,
                type: 'POST',
                data: {
                    action: VIW_datosActualizacion.action,
                    offset: offset,
                    tamano_lote: tamanoLote,
                    nonce: VIW_datosActualizacion.nonce
                },
                success: function(response) {
                    if (response.success) {
                        totalProductos = response.data.total;
                        productosActualizados += response.data.actualizados;
                        productosProcesados += response.data.procesados || response.data.actualizados;

                        if (totalProductos === 0) {
                            $boton.prop('disabled', false);
                            $estadoProgreso.text('No se encontraron productos vinculados para actualizar.');
                            $porcentajeProgreso.text('0% de productos revisados');
                            $barraProgreso.css('width', '0%');
                            alert('No se encontraron productos vinculados (proveedor/sku proveedor).');
                            return;
                        }

                        console.log('Datos API: ' + response.data.datos_api);
                        console.log('Datos Precio: ' + response.data.datos_precio);
                        console.log('Etiqueta Producto: ' + response.data.etiqueta_producto);
                        console.log('Etiqueta BD: ' + response.data.etiqueta_bd);
                        var porcentaje = (productosProcesados / totalProductos) * 100;
                        $barraProgreso.css('width', porcentaje + '%');
                        $estadoProgreso.text(productosActualizados + ' productos actualizados - ' + productosProcesados + ' revisados de ' + totalProductos);
                        $porcentajeProgreso.text(porcentaje.toFixed(2)+ '%' + ' de productos revisados');

                        if (productosProcesados < totalProductos && (response.data.procesados || 0) > 0) {
                            actualizarLote(offset + tamanoLote);
                        } else {
                            $boton.prop('disabled', false);
                            $estadoProgreso.text(productosActualizados + ' productos Actualizados');
                            $porcentajeProgreso.text('100%' + ' de productos actualizados');
                            $barraProgreso.css('width', '100%');
                            alert('Actualización completada.');
                        }
                    } else {
                        console.log(response.data);
                        $boton.prop('disabled', false);
                        alert('Error en la actualización.');
                    }
                },
                error: function() {
                    $boton.prop('disabled', false);
                    alert('Error en la comunicación con el servidor.');
                }
            });
        }

        actualizarLote(0);
    });
});
