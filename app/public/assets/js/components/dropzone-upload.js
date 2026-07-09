// DropzoneUpload.js — drag & drop con preview sobre un input[type=file] existente.
// No reemplaza el submit del form ni toca los controllers: Dropzone corre con
// autoProcessQueue=false y solo copia el archivo elegido al input[type=file] real
// (oculto) vía DataTransfer, así el form sigue enviando multipart/form-data como siempre.
//
// Uso (la clase "dropzone" es obligatoria: Dropzone.js solo inyecta el
// mensaje/estilo por defecto en elementos que la tengan):
//   <div class="dropzone" data-dropzone-target="#file"></div>
//   <input type="file" id="file" name="file" hidden>

// autoDiscover se desactiva acá (fuera de init) porque el propio Dropzone.js
// registra su auto-attach en el mismo evento DOMContentLoaded que usamos abajo,
// y de estar dentro de init() ya sería tarde: la libería adjunta primero con
// opciones por defecto y pisa la config custom (mensaje, maxFiles, etc).
if (typeof Dropzone !== 'undefined') {
    Dropzone.autoDiscover = false;
}

window.DropzoneUpload = (function () {
    function init(selector) {
        if (typeof Dropzone === 'undefined') return;
        selector = selector || '[data-dropzone-target]';

        document.querySelectorAll(selector).forEach(function (el) {
            if (el.dataset.dropzoneInitialized) return;
            el.dataset.dropzoneInitialized = 'true';

            var targetInput = document.querySelector(el.getAttribute('data-dropzone-target'));
            if (!targetInput) return;

            new Dropzone(el, {
                url: '#',
                autoProcessQueue: false,
                maxFiles: 1,
                acceptedFiles: 'image/*',
                addRemoveLinks: true,
                dictDefaultMessage: 'Arrastrá una imagen o hacé click para elegir',
                dictRemoveFile: 'Quitar',
                dictInvalidFileType: 'Solo se permiten imágenes',
                init: function () {
                    this.on('addedfile', function (file) {
                        if (this.files.length > 1) this.removeFile(this.files[0]);
                        var dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        targetInput.files = dataTransfer.files;
                    });
                    this.on('removedfile', function () {
                        targetInput.value = '';
                    });
                },
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        init();
    });

    return { init: init };
})();
