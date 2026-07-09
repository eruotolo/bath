<?php

session_start();
unset($_SESSION['carga_facturas']);

header('Location: ../dash-invoices-list.php');
