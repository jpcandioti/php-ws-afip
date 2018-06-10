<?php

include 'vendor/autoload.php';

use phpWsAfip\WS\WSASS;

// Nombre y ubicación del archivo .pem.
$alias      = 'jgutierrez';
$pem_file   = 'credentials/' . $alias . '.pem';

// Visualiza el contenido del certificado.
print_r(WSASS::extractPem(file_get_contents($pem_file)));
