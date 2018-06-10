<?php

include 'vendor/autoload.php';

use phpWsAfip\WS\WSFE;

$ta_file = 'tmp/ta.xml';

// Configuración del servicio WSFE.
$config = [
    'testing'           => true,                    // Utiliza el servicio de homologación.
    'wsdl_cache_file'   => 'tmp/wsfehomo_wsdl.xml', // Define la ubicación del caché WSDL.
];

$wsfe = new WSFE($config);

// Se precisa un TA.
if (file_exists($ta_file)) {
    $wsfe->setXmlTa(file_get_contents($ta_file));
    
    // Se visualiza el número del último comprobante.
    $pto_vta = array(
        'PtoVta'    => 1,
        'CbteTipo'  => 6    // 6 Factura B
    );
    $result = $wsfe->FECompUltimoAutorizado($pto_vta);
    print_r($result);
}
