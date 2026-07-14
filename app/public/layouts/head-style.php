<!--
    Fase 9 plan-migracion-bootstrap-tailwind — eliminados del head:
      assets/css/preloader.css   (spinner del tema legacy)
      assets/css/bootstrap.css   (Bootstrap 5.3 — clases estructurales absorbidas en Fase 9b)
      assets/css/icons.css       (MDI + FontAwesome del tema, reemplazadas por Lucide)
      assets/css/app.css         (tema "Crow Advance" — reemplazado por tw/components.css)

    Bootstrap CSS del propio Bootstrap (assets/libs/bootstrap/...) lo mantienen
    las vistas de archive/, pero el head de las vistas activas no lo necesita.
-->

<!-- Css Personalizado-->
<link href="assets/css/style.css" rel="stylesheet"  type="text/css">

<!-- feedback (Sprint 1 plan-mejoras-frontend) -->
<link href="assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css">

<!-- selects con buscador (Sprint 3 plan-mejoras-frontend) -->
<link href="assets/libs/choices.js/public/assets/styles/choices.min.css" rel="stylesheet" type="text/css">

<!-- date picker (Sprint 4 plan-mejoras-frontend) -->
<link href="assets/libs/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css">

<!-- drag & drop de imagen de perfil (Sprint 4 plan-mejoras-frontend) -->
<link href="assets/libs/dropzone/min/dropzone.min.css" rel="stylesheet" type="text/css">

<!-- preview de PDF (facturas/certificados) (Sprint 4 plan-mejoras-frontend) -->
<link href="assets/libs/glightbox/css/glightbox.min.css" rel="stylesheet" type="text/css">

<!-- sidebar rebranding — fuentes del nuevo sidebar -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- sidebar + header rebranding migrados a Tailwind 4 (Fase 2 plan-migracion-bootstrap-tailwind):
     clases .sb-* / .hdr-* ahora son utilities en layouts/sidebar.php y layouts/header.php;
     este <link> a sidebar.css / header.css se retira porque las clases ya no existen. -->
<!-- (sidebar.css y header.css retirados en Fase 2) -->

<!-- dashboard rebranding migrado a Tailwind 4 (Fase 4 plan-migracion-bootstrap-tailwind):
     clases .db-* ahora son utilities en index.php + dt-* en tw/components.css;
     este <link> se retira porque las clases .db-* ya no existen. -->

<!-- clientes rebranding migrado a Tailwind 4 (Fase 3 plan-migracion-bootstrap-tailwind):
     clases .cl-clients reemplazadas por utilities en dash-customers*.php + dt-* en tw/components.css;
     este <link> se retira porque las clases .cl-* ya no existen. -->

<!-- Tailwind 4 (Fase 0 plan-migracion-bootstrap-tailwind): convive con Bootstrap sin Preflight;
     utilities en layer(utilities) con important para ganar a Bootstrap por cascade layers. -->
<link href="assets/css/tailwind.css?v=20260714-choices-reskin3" rel="stylesheet" type="text/css">
