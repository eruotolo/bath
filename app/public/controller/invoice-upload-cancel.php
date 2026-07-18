<?php

session_start();
require_once '../layouts/permissions.php';
require_permission('create', 'Invoice');
unset($_SESSION['carga_facturas']);

header('Location: ../dash-invoices-list.php');
