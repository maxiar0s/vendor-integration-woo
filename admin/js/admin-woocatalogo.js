/**
 * Archivo de configuración para el FronEnd del administrador de tienda
 *
 * @since 1.0.0
 */

jQuery(document).ready(function () {
  table = jQuery("#WooCatalogoTable").DataTable({
    dom: "Bfrtip",
    buttons: [
      "copy",
      "csv",
      "excel",
      "pdf",
      "print",
      "selected",
      "selectedSingle",
      "selectAll",
      "selectNone",
      "selectRows",
      "selectColumns",
      "selectCells",
    ],
    responsive: true,
    ajax: {
      url: "/wp-admin/admin-ajax.php?action=datatables_endpoint_vendor_integration",
      dataSrc: "data",
      data: function (d) {
        d.nonce = Global.nonce;
      },
    },
    select: {
      style: "multi",
    },
    columns: [
      { data: "woo" },
      { data: "id" },
      { data: "sku" },
      { data: "mpn" },
      { data: "nombre" },
      { data: "precio" },
      { data: "stock" },
      { data: "categoria" },
      { data: "subcategoria" },
      { data: "proveedor" },
      { data: "creado" },
      { data: "actualizado" },
      { data: "acciones" },
    ],
  });

  jQuery(document).ready(function ($) {
    jQuery("#productosTableFicha").DataTable({
      language: {
        url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json",
      },
      dom: "Bfrtip",
      buttons: [
        "copy",
        "csv",
        "excel",
        "pdf",
        "print",
        "selected",
        "selectedSingle",
        "selectAll",
        "selectNone",
        "selectRows",
        "selectColumns",
        "selectCells",
      ],
      responsive: true,
    });
  });

  //Descarga un en un excel todo el stock de los productos
  jQuery("#fDownLoadCSVWooCatalogo").click(function (e) {
    e.preventDefault();
    jQuery("#fDownLoadCSVWooCatalogo")
      .html('<i class="fa fa-spinner fa-spin" style="font-size:20px"></i>')
      .addClass("disabled");

    // Realizar la solicitud AJAX para obtener el archivo CSV
    var url =
      Global.url +
      "?action=" +
      aDownLoadCSVWooCatalogo.action +
      "&nonce=" +
      Global.nonce;

    // Redirigir el navegador a la URL, lo que provocará la descarga del archivo
    window.location.href = url;

    // Reiniciar el botón
    jQuery("#fDownLoadCSVWooCatalogo .fa-spin").remove();
    jQuery("#fDownLoadCSVWooCatalogo").removeClass("disabled");
  });

  jQuery("#fActualizarWooCatalogoJson").click(function (e) {
    e.preventDefault();
    jQuery("#fActualizarWooCatalogoJson")
      .html('<i class="fa fa-spinner fa-spin" style="font-size:20px"></i>')
      .addClass("disabled");
    jQuery.ajax({
      type: "POST",
      url: Global.url,
      data: {
        action: aUpdateJsonCatalog.action,
        nonce: Global.nonce,
      },
      beforeSend: function () {
        jQuery(".loader-woocatalogo").show();
        jQuery(".popup-overlay").fadeIn("slow");
        jQuery(".popup-overlay").height(jQuery(window).height());
      },
      success: function (data) {
        jQuery("#fActualizarWooCatalogoJson .fa-spin").remove();
        jQuery("#fActualizarWooCatalogoJson").removeClass("disabled");
        console.log(data);
        alert(data);
        jQuery(".loader-woocatalogo").hide();
        jQuery(".popup-overlay").fadeOut("slow");
        location.reload();
      },
    });
  });

  jQuery("#fUpdateStockWooCatalogo").click(function (e) {
    e.preventDefault();
    jQuery("#fUpdateStockWooCatalogo")
      .html('<i class="fa fa-spinner fa-spin" style="font-size:20px"></i>')
      .addClass("disabled");
    jQuery.ajax({
      type: "POST",
      url: Global.url,
      data: {
        action: aUpdateStockWooCatalogo.action,
        nonce: Global.nonce,
      },
      beforeSend: function () {
        jQuery(".loader-woocatalogo").show();
        jQuery(".popup-overlay").fadeIn("slow");
        jQuery(".popup-overlay").height(jQuery(window).height());
      },
      success: function (data) {
        jQuery("#fUpdateStockWooCatalogo .fa-spin").remove();
        jQuery("#fUpdateStockWooCatalogo").removeClass("disabled");
        console.log(data);
        jQuery(".loader-woocatalogo").hide();
        jQuery(".popup-overlay").fadeOut("slow");
        alert(data);
        //location.reload();
      },
    });
  });

  jQuery("#fUpdatePrecioWooCatalogo").click(function (e) {
    e.preventDefault();
    jQuery("#fUpdatePrecioWooCatalogo")
      .html('<i class="fa fa-spinner fa-spin" style="font-size:20px"></i>')
      .addClass("disabled");
    jQuery.ajax({
      type: "POST",
      url: Global.url,
      data: {
        action: aUpdatPriceCatalogo.action,
        nonce: Global.nonce,
      },
      beforeSend: function () {
        jQuery(".loader-woocatalogo").show();
        jQuery(".popup-overlay").fadeIn("slow");
        jQuery(".popup-overlay").height(jQuery(window).height());
      },
      success: function (data) {
        jQuery("#fUpdatePrecioWooCatalogo .fa-spin").remove();
        jQuery("#fUpdatePrecioWooCatalogo").removeClass("disabled");
        console.log(data);
        jQuery(".loader-woocatalogo").hide();
        jQuery(".popup-overlay").fadeOut("slow");
        alert(data);
        //location.reload();
      },
    });
  });

  jQuery("#fSaveConfigGlobWooCatalogo").submit(function (event) {
    event.preventDefault();
    console.log(jQuery(this).serializeArray());
    var dataNumberWooCatalogo = jQuery(this).serializeArray();
    jQuery.ajax({
      type: "POST",
      url: Global.url,
      data: {
        action: aSaveConfigGlobal.action,
        nonce: Global.nonce,
        dataNumberWooCatalogo: dataNumberWooCatalogo,
      },
      beforeSend: function () {
        jQuery(".loader-woocatalogo").show();
      },
      success: function (data) {
        console.log(data);
        jQuery(".loader-woocatalogo").hide();
        alert(data);
        location.reload();
      },
    });
  });
  jQuery("#fSaveLicenseWooCatalogo").submit(function (event) {
    event.preventDefault();
    console.log(jQuery(this).serializeArray());
    var dataLicenseWooCatalogo = jQuery(this).serializeArray();
    jQuery.ajax({
      type: "POST",
      url: Global.url,
      data: {
        action: aSaveLicenseWooCatalogo.action,
        nonce: Global.nonce,
        dataLicenseWooCatalogo: dataLicenseWooCatalogo,
      },
      beforeSend: function () {
        jQuery(".loader-woocatalogo").show();
      },
      success: function (data) {
        console.log(data);
        jQuery(".loader-woocatalogo").hide();
        alert(data);
        location.reload();
      },
    });
  });

  jQuery("#fOpenConfogModalWooCatalogo").click(function (e) {
    e.preventDefault();
    jQuery("#popup").addClass("is-visible");
  });

  jQuery("#fCloseConfogModalWooCatalogo").click(function (e) {
    e.preventDefault();
    jQuery("#popup").removeClass("is-visible");
  });

  //popup de previsualizacion de producto//
  jQuery(".cd-popup").on("click", function (event) {
    if (
      jQuery(event.target).is(".cd-popup-close") ||
      jQuery(event.target).is(".cd-popup")
    ) {
      event.preventDefault();
      jQuery(this).removeClass("is-visible");
    }
  });

  //cierra el popup cuando se presiona la tecla esc
  jQuery(document).keyup(function (event) {
    if (event.which == "27") {
      jQuery(".cd-popup").removeClass("is-visible");
      jQuery("#popup").removeClass("is-visible");
    }
  });
});

function fPriceShowWooCatalogo(part_number) {
  jQuery.ajax({
    type: "POST",
    url: Global.url,
    data: {
      action: aPriceShowWooCatalogo.action,
      nonce: Global.nonce,
      part_number: part_number,
    },
    beforeSend: function () {
      jQuery(".loader-woocatalogo").show();
      jQuery(".popup-overlay").fadeIn("slow");
      jQuery(".popup-overlay").height(jQuery(window).height());
    },
    success: function (data) {
      console.log(data);
      const objCatalogWooCatalogo = JSON.parse(data);

      // Verificar si el array 'data' tiene al menos un elemento
      if (objCatalogWooCatalogo.data.length > 0) {
        console.log(objCatalogWooCatalogo.data[0].precioMasBajo);
        jQuery(".loader-woocatalogo").hide();
        jQuery(".popup-overlay").fadeOut("slow");
        jQuery(".cd-popup").addClass("is-visible");

        jQuery(".view-config").html(
          "<h2>Información de precios</h2>" +
            '<table class="tgwoocatalogo" style="table-layout: fixed; width: 100%">' +
            "<tbody>" +
            "  <tr>" +
            '    <td class="tg-0lax tgwoocatalogo">PartNumber</td>' +
            '    <td class="tg-0lax tgwoocatalogo">' +
            objCatalogWooCatalogo.data[0].part_number +
            "</td>" +
            "  </tr>" +
            "  <tr>" +
            '    <td class="tg-0lax tgwoocatalogo">Precio más bajo</td>' +
            '    <td class="tg-0lax tgwoocatalogo">' +
            objCatalogWooCatalogo.data[0].PBP +
            ": " +
            objCatalogWooCatalogo.data[0].precioMasBajo +
            " USD</td>" +
            "  </tr>" +
            "  <tr>" +
            '    <td class="tg-0lax tgwoocatalogo">Precio más alto</td>' +
            '    <td class="tg-0lax tgwoocatalogo">' +
            objCatalogWooCatalogo.data[0].PAP +
            ": " +
            objCatalogWooCatalogo.data[0].precioMasAlto +
            " USD</td>" +
            "  </tr>" +
            "  <tr>" +
            '    <td class="tg-0lax tgwoocatalogo">Proveedores</td>' +
            '    <td class="tg-0lax tgwoocatalogo">' +
            objCatalogWooCatalogo.data[0].proveedores +
            "</td>" +
            "  </tr>" +
            "</tbody>" +
            "</table>",
        );
      } else {
        console.log("No se encontraron datos en el array 'data'.");
        jQuery(".loader-woocatalogo").hide();
        jQuery(".popup-overlay").fadeOut("slow");
        jQuery(".cd-popup").addClass("is-visible");
        jQuery(".view-config").html(
          "No se encontraron datos en el array 'data'.",
        );
      }
    },
  });
}

function fStockShowWooCatalogo(part_number) {
  jQuery.ajax({
    type: "POST",
    url: Global.url,
    data: {
      action: aStockShowWooCatalogo.action,
      nonce: Global.nonce,
      part_number: part_number,
    },
    beforeSend: function () {
      jQuery(".loader-woocatalogo").show();
      jQuery(".popup-overlay").fadeIn("slow");
      jQuery(".popup-overlay").height(jQuery(window).height());
    },
    success: function (data) {
      console.log(data);
      const objCatalogWooCatalogo = JSON.parse(data);

      // Verificar si el array 'data' tiene al menos un elemento
      if (objCatalogWooCatalogo.data.length > 0) {
        console.log(objCatalogWooCatalogo);
        jQuery(".loader-woocatalogo").hide();
        jQuery(".popup-overlay").fadeOut("slow");
        jQuery(".cd-popup").addClass("is-visible");

        jQuery(".view-config").html(
          "<h2>Información de stock</h2>" +
            '<table class="tgwoocatalogo" style="table-layout: fixed; width: 100%">' +
            "<tbody>" +
            "  <tr>" +
            '    <td class="tg-0lax tgwoocatalogo">PartNumber</td>' +
            '    <td class="tg-0lax tgwoocatalogo">' +
            objCatalogWooCatalogo.data[0].part_number +
            "</td>" +
            "  </tr>" +
            "  <tr>" +
            '    <td class="tg-0lax tgwoocatalogo">Stock más bajo</td>' +
            '    <td class="tg-0lax tgwoocatalogo">' +
            objCatalogWooCatalogo.data[0].proveedorStockMasBajo +
            ": " +
            objCatalogWooCatalogo.data[0].stockMasBajo +
            "</td>" +
            "  </tr>" +
            "  <tr>" +
            '    <td class="tg-0lax tgwoocatalogo">Stock más alto</td>' +
            '    <td class="tg-0lax tgwoocatalogo">' +
            objCatalogWooCatalogo.data[0].proveedorStockMasAlto +
            ": " +
            objCatalogWooCatalogo.data[0].stockMasAlto +
            "</td>" +
            "  </tr>" +
            "  <tr>" +
            '    <td class="tg-0lax tgwoocatalogo">Proveedores</td>' +
            '    <td class="tg-0lax tgwoocatalogo">' +
            objCatalogWooCatalogo.data[0].proveedores +
            "</td>" +
            "  </tr>" +
            "</tbody>" +
            "</table>",
        );
      } else {
        console.log("No se encontraron datos en el array 'data'.");
        jQuery(".loader-woocatalogo").hide();
        jQuery(".popup-overlay").fadeOut("slow");
        jQuery(".cd-popup").addClass("is-visible");
        jQuery(".view-config").html(
          "No se encontraron datos en el array 'data'.",
        );
      }
    },
  });
}

/////Borrar configuracion//////
function fDeleteConfigWooCatalogo(idreg) {
  jQuery.ajax({
    type: "POST",
    url: Global.url,
    data: {
      action: aDeleteConfigGlobal.action,
      nonce: Global.nonce,
      idreg: idreg,
    },
    beforeSend: function () {
      jQuery(".loader-woocatalogo").show();
      jQuery(".popup-overlay").fadeIn("slow");
      jQuery(".popup-overlay").height(jQuery(window).height());
    },
    success: function (data) {
      jQuery(".loader-woocatalogo").hide();
      jQuery(".popup-overlay").fadeOut("slow");
      console.log(data);
      alert(data);
      location.reload();
    },
  });
}

/////Insertar productos//////
function fInsertProductWooCatalogo(part_number, proveedor) {
  jQuery.ajax({
    type: "POST",
    url: Global.url,
    data: {
      action: aInsertProductoWooCatalogo.action,
      nonce: Global.nonce,
      part_number: part_number,
      proveedor: proveedor,
    },
    beforeSend: function () {
      jQuery(".loader-woocatalogo").show();
      jQuery(".popup-overlay").fadeIn("slow");
      jQuery(".popup-overlay").height(jQuery(window).height());
    },
    success: function (data) {
      jQuery(".loader-woocatalogo").hide();
      jQuery(".popup-overlay").fadeOut("slow");
      console.log(data);
      alert(data);
      location.reload();
    },
  });
}

/////Borrar productos//////
function fDeleteProductWooCatalogo(part_number) {
  jQuery.ajax({
    type: "POST",
    url: Global.url,
    data: {
      action: aDeleteProductoWooCatalogo.action,
      nonce: Global.nonce,
      part_number: part_number,
    },
    beforeSend: function () {
      jQuery(".loader-woocatalogo").show();
      jQuery(".popup-overlay").fadeIn("slow");
      jQuery(".popup-overlay").height(jQuery(window).height());
    },
    success: function (data) {
      jQuery(".loader-woocatalogo").hide();
      jQuery(".popup-overlay").fadeOut("slow");
      console.log(data);
      alert(data);
      location.reload();
    },
  });
}

function fUpdateAtrrWooCatalogo(part_number) {
  jQuery.ajax({
    type: "POST",
    url: Global.url,
    data: {
      action: aInsertAttrWooCatalogo.action,
      nonce: Global.nonce,
      part_number: part_number,
    },
    beforeSend: function () {
      jQuery(".loader-woocatalogo").show();
      jQuery(".popup-overlay").fadeIn("slow");
      jQuery(".popup-overlay").height(jQuery(window).height());
    },
    success: function (data) {
      jQuery(".loader-woocatalogo").hide();
      jQuery(".popup-overlay").fadeOut("slow");
      console.log(data);
      alert(data);
      location.reload();
    },
  });
}

function fPreviewProductWooCatalogo(part_number) {
  jQuery.ajax({
    type: "POST",
    url: Global.url,
    data: {
      action: aPreviewProductWooCatalogo.action,
      nonce: Global.nonce,
      part_number: part_number,
    },
    beforeSend: function () {
      jQuery(".loader-woocatalogo").show();
      jQuery(".popup-overlay").fadeIn("slow");
      jQuery(".popup-overlay").height(jQuery(window).height());
    },
    success: function (data) {
      jQuery(".loader-woocatalogo").hide();
      jQuery(".popup-overlay").fadeOut("slow");
      console.log(data);
      if (data === "Este producto no esta en Woocommerce") {
        alert(data);
      } else {
        window.open(data);
      }
    },
  });
}
