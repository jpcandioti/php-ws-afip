<?php

include 'vendor/autoload.php';

use phpWsAfip\WS\WSASS;

// Nombre y ubicación del archivo .key y el archivos .csr a generar.
$alias      = 'jgutierrez';
$key_file   = 'credentials/' . $alias . '.key';
$csr_file   = 'credentials/' . $alias . '.csr';

// Distinguished Name (DN) para el Certificate Signing Request (CSR).
// Los siguientes datos son de ejemplo y no concuerdan con una persona real.
$dn = array(
    'countryName'           => 'AR',
    'stateOrProvinceName'   => 'Santa Fe',
    'localityName'          => 'Rosario',
    'organizationName'      => 'Juan Gutiérrez',
    'commonName'            => 'jgutierrez',
    'serialNumber'          => 'CUIT 20260795326'
);


// CUIDADO con reescribir el CSR.
if (!file_exists($csr_file)) {
    // Genera un CSR en formato PKCS#10 con la clave privada y el DN.
    file_put_contents($csr_file, WSASS::generateCsr($key_file, $dn));
}
