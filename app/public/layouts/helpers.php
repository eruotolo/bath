<?php

function require_authenticated_session(string $login_path): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header('Location: ' . $login_path);
        exit;
    }
}

function csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool {
    return isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

function format_clp($valor) {
    return number_format((float)$valor, 0, ',', '.') . ' CLP';
}

function format_fecha($fecha) {
    $timestamp = strtotime((string) $fecha);
    return $timestamp !== false ? date('d-m-Y', $timestamp) : (string) $fecha;
}

function bath_estado_tecnico(int $estado): array {
    return match ($estado) {
        1 => ['label' => 'Activo', 'tagBg' => 'bg-emerald-50 text-emerald-700'],
        2 => ['label' => 'Mantención', 'tagBg' => 'bg-amber-50 text-amber-700'],
        default => ['label' => 'Inactivo', 'tagBg' => 'bg-slate-100 text-slate-600'],
    };
}

function normalizar_rut($rut) {
    $rut = strtoupper(trim($rut));
    return preg_replace('/[^0-9K]/', '', $rut);
}

/**
 * Valida un RUT chileno con dígito verificador (módulo 11).
 * Acepta formatos "12345678-9", "12.345.678-9", "123456789".
 * Devuelve true solo si el cuerpo coincide con el DV calculado.
 */
function validar_rut_modulo11($rut) {
    $limpio = normalizar_rut($rut);
    if (strlen($limpio) < 2) {
        return false;
    }

    $cuerpo = substr($limpio, 0, -1);
    $dv = substr($limpio, -1);
    if (!ctype_digit($cuerpo)) {
        return false;
    }

    $factor = 2;
    $suma = 0;
    for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
        $suma += (int) $cuerpo[$i] * $factor;
        $factor = $factor === 7 ? 2 : $factor + 1;
    }
    $resto = $suma % 11;
    $esperado = 11 - $resto;
    if ($esperado === 11) {
        $esperado = 0;
    } elseif ($esperado === 10) {
        $esperado = 'K';
    }

    return (string) $esperado === $dv;
}

function excel_a_fecha($valor) {
    $valor = trim((string)$valor);

    if ($valor === '') {
        return null;
    }

    if (is_numeric($valor)) {
        $timestamp = ((float)$valor - 25569) * 86400;
        return gmdate('Y-m-d', (int)$timestamp);
    }

    if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $valor)) {
        $partes = explode('-', $valor);
        return sprintf('%04d-%02d-%02d', $partes[0], $partes[1], $partes[2]);
    }

    if (preg_match('#^(\d{1,2})[/-](\d{1,2})[/-](\d{4})$#', $valor, $m)) {
        return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
    }

    return null;
}

function columna_a_indice($letra) {
    $indice = 0;
    foreach (str_split($letra) as $caracter) {
        $indice = $indice * 26 + (ord($caracter) - 64);
    }
    return $indice - 1;
}

// Extrae un archivo puntual de dentro de un .zip leyendo las cabeceras locales
// a mano (sin la extensión zip de PHP, que no está instalada en este proyecto).
// No soporta modo streaming con "data descriptor" (bit 3 de flags) - los .xlsx
// que genera Excel/LibreOffice no lo usan.
function zip_leer_archivo($contenido_zip, $nombre_buscado) {
    $offset = 0;
    $largo = strlen($contenido_zip);

    while (($pos = strpos($contenido_zip, "\x50\x4b\x03\x04", $offset)) !== false) {
        $cabecera = substr($contenido_zip, $pos + 4, 26);
        if (strlen($cabecera) < 26) {
            break;
        }

        $datos = unpack('vversion/vflags/vmetodo/vhora/vfecha/Vcrc/Vcomprimido/Vsincomprimir/vlargo_nombre/vlargo_extra', $cabecera);

        $inicio_nombre = $pos + 30;
        $nombre = substr($contenido_zip, $inicio_nombre, $datos['largo_nombre']);
        $inicio_datos = $inicio_nombre + $datos['largo_nombre'] + $datos['largo_extra'];

        if ($nombre === $nombre_buscado) {
            $comprimido = substr($contenido_zip, $inicio_datos, $datos['comprimido']);
            return $datos['metodo'] === 0 ? $comprimido : gzinflate($comprimido);
        }

        $offset = $inicio_datos + $datos['comprimido'];
        if ($offset <= $pos) {
            break;
        }
    }

    return false;
}

// Lector XLSX propio, sin librerías externas (un .xlsx es un ZIP con XML adentro).
// Soporta una sola hoja, columnas fijas, sin fórmulas. Devuelve un array de filas,
// cada fila un array indexado 0,1,2... por columna (A,B,C...), con celdas vacías
// intercaladas si corresponde.
function leer_xlsx($ruta) {
    $contenido = file_get_contents($ruta);
    if ($contenido === false) {
        return false;
    }

    $shared_strings = [];
    $strings_xml = zip_leer_archivo($contenido, 'xl/sharedStrings.xml');
    if ($strings_xml !== false) {
        $xml = simplexml_load_string($strings_xml);
        if ($xml !== false) {
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $shared_strings[] = (string)$si->t;
                } else {
                    $texto = '';
                    foreach ($si->r as $r) {
                        $texto .= (string)$r->t;
                    }
                    $shared_strings[] = $texto;
                }
            }
        }
    }

    $sheet_xml = zip_leer_archivo($contenido, 'xl/worksheets/sheet1.xml');

    if ($sheet_xml === false) {
        return false;
    }

    $xml = simplexml_load_string($sheet_xml);
    if ($xml === false) {
        return false;
    }

    $filas = [];
    foreach ($xml->sheetData->row as $row) {
        $fila = [];
        foreach ($row->c as $c) {
            $ref = (string)$c['r'];
            preg_match('/([A-Z]+)(\d+)/', $ref, $m);
            $col_index = columna_a_indice($m[1]);

            $tipo = (string)$c['t'];
            $valor_raw = isset($c->v) ? (string)$c->v : '';

            if ($tipo === 's') {
                $valor = $shared_strings[(int)$valor_raw] ?? '';
            } else {
                $valor = $valor_raw;
            }

            $fila[$col_index] = $valor;
        }
        ksort($fila);
        $filas[] = $fila;
    }

    return $filas;
}
