<?php

include 'vendor/autoload.php';

use phpWsAfip\WS\WSASS;

// Nombre y ubicación del archivo .key a generar.
$alias      = 'jgutierrez';
$key_file   = 'credentials/' . $alias . '.key';

// CUIDADO con reescribir la clave privada.
if (!file_exists($key_file)) {
    // Genera una clave privada de 2048 bits.
    file_put_contents($key_file, WSASS::generatePrivateKey());
}
