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
    
    // Consulta el número del último comprobante y le sumo 1.
    $pto_vta = array(
        'PtoVta'    => 1,
        'CbteTipo'  => 6    // 6 Factura B
    );
    $result = $wsfe->FECompUltimoAutorizado($pto_vta);
    $cbte_nro = $result->FECompUltimoAutorizadoResult->CbteNro + 1;

    $today = date('Ymd');

    // Factura B por $302,50.
    $invoice = array(
        'FeCAEReq' => array(
            'FeCabReq' => array(
                'CantReg'      => 1,
                'CbteTipo'     => 6,                    // 6 Factura B
                'PtoVta'       => 1,
            ),
            'FeDetReq' => array(
                'FECAEDetRequest' => array(
                    'Concepto'     => 2,                // 2 Servicios.
                    'DocTipo'      => 96,               // 96 DNI.
                    'DocNro'       => 32472807,
                    'CbteDesde'    => $cbte_nro,
                    'CbteHasta'    => $cbte_nro,
                    'CbteFch'      => $today,
                    'ImpTotal'     => 302.5,
                    'ImpTotConc'   => 0,
                    'ImpNeto'      => 250,
                    'ImpOpEx'      => 0,
                    'ImpIVA'       => 52.5,
                    'ImpTrib'      => 0,
                    'FchServDesde' => $today,
                    'FchServHasta' => $today,
                    'FchVtoPago'   => $today,
                    'MonId'        => 'PES',
                    'MonCotiz'     => 1,
                    'Iva'          => array(
                        'AlicIva' => array(
                            'Id'        => 5,
                            'BaseImp'   => 250,
                            'Importe'   => 52.5
                        )
                    )
                )
            )
        )
    );

    // Se visualiza el resultado con el CAE correspondiente al comprobante.
    $result = $wsfe->FECAESolicitar($invoice);
    print_r($result);
}
