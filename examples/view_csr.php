<?php

include 'vendor/autoload.php';

use phpWsAfip\WS\WSASS;

// Nombre y ubicación del archivo .csr.
$alias      = 'jgutierrez';
$csr_file   = 'file://credentials/' . $alias . '.csr';

// Visualiza el DN de un CSR en formato PKCS#10.
print_r(WSASS::extractCsr($csr_file));
