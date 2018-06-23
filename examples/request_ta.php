<?php

include 'vendor/autoload.php';

use phpWsAfip\WS\WSAA;

// Nombre y ubicación de las credenciales.
$alias      = 'jgutierrez';
$key_file   = 'file://credentials/' . $alias . '.key';
$crt_file   = 'file://credentials/' . $alias . '.pem';

// Archivo dónde se almacenará el Ticket de Acceso (TA).
$ta_file    = 'tmp/ta.xml';

// Configuración del servicio WSAA.
$config = [
    'testing'           => true,                    // Utiliza el servicio de homologación.
    'wsdl_cache_file'   => 'tmp/wsaahomo_wsdl.xml', // Define la ubicación del caché WSDL.
    'tra_tpl_file'      => 'tmp/tra_%s.xml'         // Define la ubicación de los archivos temporarios con el TRA.
];

$wsaa = new WSAA('wsfe', $crt_file, $key_file, $config);

// Si el TA se generó con éxito...
if ($ta = $wsaa->requestTa()) {
    // Se visualiza los datos del encabezado.
    print_r($ta->header);

    // Guardar el XML en una variable. Luego puede almacenarse en una base de datos.
    //$xml = $ta->asXml();
    //echo $xml;

    // Guardar el TA en un archivo.
    $ta->asXml($ta_file);
}
