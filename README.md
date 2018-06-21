# phpWsAfip

Librería para la gestión de WebServices de la Agencia Federal de Ingresos Públicos (AFIP - Organismo de hacienda de Argentina).

phpWsAfip es una pequeña librería que permite que cualquier sistema en PHP pueda conectarse a los servicios de AFIP.


## Características

- Generación de _Clave privada_ RSA con _Frase secreta_.
- Generación de _Certificate Signing Request_ (CSR).
- Extracción del _Distinguished Name_ (DN) de un CSR.
- Extracción de información de certificado X.509.
- Caché WSDL de cada WebService.
- Ejecución de cualquier función ofrecida por el WebService a través de un método \__call.
- Firmado de TRA.
- Solicita un Ticket de Acceso (sesión).
- Gestiona la sesión para cualquier WebService de AFIP.
- Implementa WSFEv1.


La librería está compuesta por 3 clases principales:
- WSASS (Autoservicio de Acceso a WebServices).
- WSAA (WebService de Autenticación y Autorización).
- WSFE (WebService de Facturación Electrónica).


## Instalación

~~~
$ composer require jpcandioti/php-ws-afip:dev-master
~~~


## Creación de un certificado

Para la creación de un certificado que nos permita operar en la plataforma de WebService de AFIP se precisa de una _Clave privada_ y un _Certificate Signing Request_ (CSR). Desde phpWsAfip es posible crear ambas cosas.

Para conocer más puede acceder al siguiente documento de AFIP: [Generación de Certificados para Producción]

Para crear un certificado para Testing/Homologación puede acceder al siguiente documento de AFIP: [WSASS: Cómo adherirse al servicio]

### Generación de una _Clave privada_

La _Clave privada_ es importante conservarla en un lugar seguro.

#### Ejemplo

~~~php
use phpWsAfip\WS\WSASS;

$private_key = WSASS::generatePrivateKey();

echo $private_key;
~~~

### Generación de una _Clave privada_ con _Frase secreta_

Para conservar la seguridad de las claves generadas, nunca deberían almacenarse junto a su frase secreta. Un ejemplo de uso podría ser no almacenar frases secretas, y que cada usuario la ingrese cada vez que se necesite firmar.

#### Ejemplo

~~~php
use phpWsAfip\WS\WSASS;

$bits = 4096;
$passphrase = 'Una frase secreta';
$private_key = WSASS::generatePrivateKey($bits, $passphrase);

echo $private_key;
~~~

### Generación de un _CSR_

Para solicitar el certificado a AFIP es necesario generar un CSR.

#### Ejemplo

~~~php
use phpWsAfip\WS\WSASS;

$key_file = 'example.key';

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

$csr = WSASS::generateCsr($key_file, $dn);

echo $csr;
~~~


## Manejo de sesiones

Para poder operar en un WebService de Negocio (WSN) es necesario solicitar un Ticket de Acceso (TA).

### Solicitar un TA

#### Ejemplo

~~~php
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
    'tra_tpl_file'      => 'tmp/tra_%s.xml'
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
~~~


## WebService de Negocio (WSN)

La librería cuenta con la clase __phpWsAfip/WS/WSN.php__ que sirve cómo base para todos los servicios que precisan gestionar un TA.

Por el momento el único WSN implementado es el WebService de Facturación Electrónica (WSFE).

Si precisa utilizar otro de los WebServices de AFIP, puede implementarlo Ud mismo utilizando cómo base la clase __phpWsAfip/WS/WSFE.php__. Luego puede compartirla agregándola al proyecto a través de un Pull Request, para que otros puedan aprovecharlo.


## WebService de Facturación Electrónica (WSFE)

Para operar en el servicio WSFEv1 se debe contar con un TA activo.

Una vez instanciado pueden ejecutarse todos los métodos definidos en la documentación oficial ([WSFEv1: Manual para el desarrollador V.2.10]), pasando todos los parámetros dentro de un arreglo. El siguiente ejemplo ejecuta los métodos __FECompUltimoAutorizado__ y __FECAESolicitar__.

#### Ejemplo

~~~php
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
~~~


## Utilización de la caché de SoapClient

phpWsAfip implementa un caché WSDL utilizando un archivo temporario. Si desea utilizar el caché de SoapClient puede hacerlo, tanto en WSAA cómo en cualquier WSN, cómo en el siguiente ejemplo.

#### Ejemplo

~~~php
use phpWsAfip\WS\WSFE;

// Configuración de SoapClient.
$soap_options = array(
    'cache_wsdl'=> WSDL_CACHE_DISK
);

// Configuración del servicio WSFE.
$config = [
    'wsdl_cache_file'   => null,
    'soap_options'      => $soap_options
];

$wsfe = new WSFE($config);
~~~


## Desarrollo y Testing

Para armar el entorno de desarrollo deben seguirse los siguientes pasos:

~~~
$ git clone git@github.com:jpcandioti/php-ws-afip.git
$ cd php-ws-afip
$ composer install 
~~~

Para correr los test es necesario tener un certificado de homologación con su respectiva _Clave privada_.

Los mismos deben estar almacenados en el directorio _credentials_ bajo el nombre indicado en la variable de entorno TEST_ALIAS, y las extensiones _.key_, _.csr_, _.pem_.

#### Ejemplo

~~~
$ TEST_ALIAS=jgutierrez phpunit .
~~~


## Colaboración

Puede aportar al desarrollador del proyecto en la siguiente billetera Bitcoin: [132r6sUhqz44gfXAj5EpWxH2pWB59HbWKY]


[Generación de Certificados para Producción]: https://afip.gob.ar/ws/WSAA/WSAA.ObtenerCertificado.pdf
[WSASS: Cómo adherirse al servicio]: https://afip.gob.ar/ws/WSASS/WSASS_como_adherirse.pdf
[WSFEv1: Manual para el desarrollador V.2.10]: http://www.afip.gob.ar/fe/documentos/manual_desarrollador_COMPG_v2_10.pdf
[132r6sUhqz44gfXAj5EpWxH2pWB59HbWKY]: bitcoin:132r6sUhqz44gfXAj5EpWxH2pWB59HbWKY
