/**
 * Archivo de configuración para el FronEnd del administrador de tienda
 *
 * @since 1.0.0
 */

jQuery(document).ready(function () {
  var wooCatalogoTableSelector = "#viwWooCatalogoTable";
  var productosTableFichaSelector = "#viwProductosTableFicha";
  var table;

  function hideBusyOverlay() {
    jQuery(".loader-woocatalogo").hide();
    jQuery(".popup-overlay").stop(true, true).fadeOut("fast");
  }

  function resetActionButton(selector, label) {
    var $button = jQuery(selector);
    $button.removeClass("disabled");
    if ($button.is("input")) {
      $button.val(label);
      return;
    }
    $button.text(label);
  }

  if (jQuery(wooCatalogoTableSelector).length) {
    var viwDatatablesUrl =
      VIW_Global.url +
      "?action=" +
      VIW_Global.datatables_action +
      "&nonce=" +
      VIW_Global.nonce;

    if (jQuery.fn.DataTable.isDataTable(wooCatalogoTableSelector)) {
      table = jQuery(wooCatalogoTableSelector).DataTable();
      if (table && table.ajax) {
        table.ajax.url(viwDatatablesUrl).load();
      }
    } else {
      table = jQuery(wooCatalogoTableSelector).DataTable({
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
          url: viwDatatablesUrl,
          dataSrc: "data",
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
    }
  }

  if (
    jQuery(productosTableFichaSelector).length &&
    !jQuery.fn.DataTable.isDataTable(productosTableFichaSelector)
  ) {
    jQuery(productosTableFichaSelector).DataTable({
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
  }

  //Descarga un en un excel todo el stock de los productos
  jQuery("#viwDownLoadCSVWooCatalogo").click(function (e) {
    e.preventDefault();
    jQuery("#viwDownLoadCSVWooCatalogo")
      .html('<i class="fa fa-spinner fa-spin" style="font-size:20px"></i>')
      .addClass("disabled");

    // Realizar la solicitud AJAX para obtener el archivo CSV
    var url =
      VIW_Global.url +
      "?action=" +
      VIW_DownLoadCSVWooCatalogo.action +
      "&nonce=" +
      VIW_Global.nonce;

    // Redirigir el navegador a la URL, lo que provocará la descarga del archivo
    window.location.href = url;

    // Reiniciar el botón
    jQuery("#viwDownLoadCSVWooCatalogo .fa-spin").remove();
    jQuery("#viwDownLoadCSVWooCatalogo").removeClass("disabled");
  });

  jQuery("#viwActualizarWooCatalogoJson").click(function (e) {
    e.preventDefault();
    jQuery("#viwActualizarWooCatalogoJson")
      .html('<i class="fa fa-spinner fa-spin" style="font-size:20px"></i>')
      .addClass("disabled");
    jQuery.ajax({
      type: "POST",
      url: VIW_Global.url,
      data: {
        action: VIW_UpdateJsonCatalog.action,
        nonce: VIW_Global.nonce,
      },
      beforeSend: function () {
        jQuery(".loader-woocatalogo").show();
        jQuery(".popup-overlay").fadeIn("slow");
        jQuery(".popup-overlay").height(jQuery(window).height());
      },
      success: function (data) {
        resetActionButton(
          "#viwActualizarWooCatalogoJson",
          "Actualizar lista de productos",
        );
        console.log(data);
        alert(data);
        location.reload();
      },
      error: function (xhr) {
        resetActionButton(
          "#viwActualizarWooCatalogoJson",
          "Actualizar lista de productos",
        );
        console.log(xhr);
        alert("Error al actualizar la lista de productos.");
      },
      complete: function () {
        hideBusyOverlay();
      },
    });
  });

  jQuery("#viwUpdateStockWooCatalogo").click(function (e) {
    e.preventDefault();
    jQuery("#viwUpdateStockWooCatalogo")
      .html('<i class="fa fa-spinner fa-spin" style="font-size:20px"></i>')
      .addClass("disabled");
    jQuery.ajax({
      type: "POST",
      url: VIW_Global.url,
      data: {
        action: VIW_UpdateStockWooCatalogo.action,
        nonce: VIW_Global.nonce,
      },
      beforeSend: function () {
        jQuery(".loader-woocatalogo").show();
        jQuery(".popup-overlay").fadeIn("slow");
        jQuery(".popup-overlay").height(jQuery(window).height());
      },
      success: function (data) {
        resetActionButton(
          "#viwUpdateStockWooCatalogo",
          "Actualizar Stock en Woocommerce",
        );
        console.log(data);
        alert(data);
        //location.reload();
      },
      error: function (xhr) {
        resetActionButton(
          "#viwUpdateStockWooCatalogo",
          "Actualizar Stock en Woocommerce",
        );
        console.log(xhr);
        alert("Error al actualizar el stock.");
      },
      complete: function () {
        hideBusyOverlay();
      },
    });
  });

  jQuery("#viwUpdatePrecioWooCatalogo").click(function (e) {
    e.preventDefault();
    jQuery("#viwUpdatePrecioWooCatalogo")
      .html('<i class="fa fa-spinner fa-spin" style="font-size:20px"></i>')
      .addClass("disabled");
    jQuery.ajax({
      type: "POST",
      url: VIW_Global.url,
      data: {
        action: VIW_UpdatPriceCatalogo.action,
        nonce: VIW_Global.nonce,
      },
      beforeSend: function () {
        jQuery(".loader-woocatalogo").show();
        jQuery(".popup-overlay").fadeIn("slow");
        jQuery(".popup-overlay").height(jQuery(window).height());
      },
      success: function (data) {
        resetActionButton(
          "#viwUpdatePrecioWooCatalogo",
          "Actualizar Precio en Woocommerce",
        );
        console.log(data);
        alert(data);
        //location.reload();
      },
      error: function (xhr) {
        resetActionButton(
          "#viwUpdatePrecioWooCatalogo",
          "Actualizar Precio en Woocommerce",
        );
        console.log(xhr);
        alert("Error al actualizar los precios.");
      },
      complete: function () {
        hideBusyOverlay();
      },
    });
  });

  jQuery("#viwSaveConfigGlobWooCatalogo").submit(function (event) {
    event.preventDefault();
    console.log(jQuery(this).serializeArray());
    var dataNumberWooCatalogo = jQuery(this).serializeArray();
    jQuery.ajax({
      type: "POST",
      url: VIW_Global.url,
      data: {
        action: VIW_SaveConfigGlobal.action,
        nonce: VIW_Global.nonce,
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
      error: function (xhr) {
        jQuery(".loader-woocatalogo").hide();
        console.log(xhr);
        alert("Error al guardar configuración global: " + xhr.status);
      },
    });
  });
  jQuery("#fSaveLicenseWooCatalogo").submit(function (event) {
    event.preventDefault();
    console.log(jQuery(this).serializeArray());
    var dataLicenseWooCatalogo = jQuery(this).serializeArray();
    jQuery.ajax({
      type: "POST",
      url: VIW_Global.url,
      data: {
        action: VIW_SaveLicenseWooCatalogo.action,
        nonce: VIW_Global.nonce,
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
      error: function (xhr) {
        jQuery(".loader-woocatalogo").hide();
        console.log(xhr);
        alert("Error al guardar configuración de licencia: " + xhr.status);
      },
    });
  });

  jQuery(document).on("click", ".viw-apply-config", function (event) {
    event.preventDefault();

    var $button = jQuery(this);
    var ganancia = $button.attr("data-ganancia") || "";
    var comision = $button.attr("data-comision") || "";
    var dolar = $button.attr("data-dolar") || "";
    var etiqueta = $button.attr("data-etiqueta") || "";

    jQuery("#gan-woocatalogo").val(ganancia);
    jQuery("#comision-woocatalogo").val(comision);
    jQuery("#dolar-woocatalogo").val(dolar);

    var $selectEtiqueta = jQuery("#categories-woocatalogo");
    if ($selectEtiqueta.find("option[value='" + etiqueta + "']").length === 0 && etiqueta !== "") {
      $selectEtiqueta.append(
        jQuery("<option>", {
          value: etiqueta,
          text: etiqueta,
        }),
      );
    }

    $selectEtiqueta.val(etiqueta).trigger("change");
  });

  jQuery("#viwOpenConfigModalWooCatalogo").click(function (e) {
    e.preventDefault();
    hideBusyOverlay();
    jQuery("#viw-popup").addClass("is-visible");
  });

  jQuery("#viwCloseConfigModalWooCatalogo").click(function (e) {
    e.preventDefault();
    jQuery("#viw-popup").removeClass("is-visible");
    hideBusyOverlay();
  });

  //popup de previsualizacion de producto//
  jQuery(".cd-popup").on("click", function (event) {
    if (
      jQuery(event.target).is(".cd-popup-close") ||
      jQuery(event.target).is(".cd-popup")
    ) {
      event.preventDefault();
      jQuery(this).removeClass("is-visible");
      hideBusyOverlay();
    }
  });

  //cierra el popup cuando se presiona la tecla esc
  jQuery(document).keyup(function (event) {
    if (event.which == "27") {
      jQuery(".cd-popup").removeClass("is-visible");
      jQuery("#viw-popup").removeClass("is-visible");
      hideBusyOverlay();
    }
  });
});

function viwPriceShowWooCatalogo(part_number) {
  jQuery.ajax({
    type: "POST",
    url: VIW_Global.url,
    data: {
      action: VIW_PriceShowWooCatalogo.action,
      nonce: VIW_Global.nonce,
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

function fPriceShowWooCatalogo(part_number) {
  return viwPriceShowWooCatalogo(part_number);
}

function viwStockShowWooCatalogo(part_number) {
  jQuery.ajax({
    type: "POST",
    url: VIW_Global.url,
    data: {
      action: VIW_StockShowWooCatalogo.action,
      nonce: VIW_Global.nonce,
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

function fStockShowWooCatalogo(part_number) {
  return viwStockShowWooCatalogo(part_number);
}

/////Borrar configuracion//////
function viwDeleteConfigWooCatalogo(idreg) {
  jQuery.ajax({
    type: "POST",
    url: VIW_Global.url,
    data: {
      action: VIW_DeleteConfigGlobal.action,
      nonce: VIW_Global.nonce,
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

function fDeleteConfigWooCatalogo(idreg) {
  return viwDeleteConfigWooCatalogo(idreg);
}

/////Insertar productos//////
function viwInsertProductWooCatalogo(part_number, proveedor) {
  jQuery.ajax({
    type: "POST",
    url: VIW_Global.url,
    data: {
      action: VIW_InsertProductoWooCatalogo.action,
      nonce: VIW_Global.nonce,
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

function fInsertProductWooCatalogo(part_number, proveedor) {
  return viwInsertProductWooCatalogo(part_number, proveedor);
}

/////Borrar productos//////
function viwDeleteProductWooCatalogo(part_number) {
  jQuery.ajax({
    type: "POST",
    url: VIW_Global.url,
    data: {
      action: VIW_DeleteProductoWooCatalogo.action,
      nonce: VIW_Global.nonce,
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

function fDeleteProductWooCatalogo(part_number) {
  return viwDeleteProductWooCatalogo(part_number);
}

function viwUpdateAtrrWooCatalogo(part_number) {
  jQuery.ajax({
    type: "POST",
    url: VIW_Global.url,
    data: {
      action: VIW_InsertAttrWooCatalogo.action,
      nonce: VIW_Global.nonce,
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
  return viwUpdateAtrrWooCatalogo(part_number);
}

function viwPreviewProductWooCatalogo(part_number) {
  jQuery.ajax({
    type: "POST",
    url: VIW_Global.url,
    data: {
      action: VIW_PreviewProductWooCatalogo.action,
      nonce: VIW_Global.nonce,
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

function fPreviewProductWooCatalogo(part_number) {
  return viwPreviewProductWooCatalogo(part_number);
}
