// LightboxPreview.js — GLightbox sobre links de preview (facturas/certificados en PDF).
// Se auto-inicializa sobre [data-glightbox-preview]. El PDF se abre inline dentro
// de un iframe en el modal (los navegadores lo renderizan con su visor nativo,
// que ya trae impresión/descarga), en vez de navegar a una pestaña nueva.
//
// Uso (esta build de GLightbox no soporta type="iframe": el tipo para embeber
// una URL propia dentro de un iframe es "external"; width/height son obligatorios,
// si no el iframe queda con la altura mínima por defecto):
//   <a href="controller/invoice-pdf.php?id_Factura=1&id_Contrato=2"
//      data-glightbox-preview data-type="external"
//      data-width="900px" data-height="90vh" title="Factura #1">Imprimir</a>

window.LightboxPreview = (function () {
    function init(selector) {
        if (typeof GLightbox === 'undefined') return;
        selector = selector || '[data-glightbox-preview]';

        if (!document.querySelector(selector)) return;

        GLightbox({
            selector: selector,
            touchNavigation: false,
            loop: false,
            zoomable: false,
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        init();
    });

    return { init: init };
})();
