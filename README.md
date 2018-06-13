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

Para conservar la seguridad de las claves generadas nunca deberían almacenarse junto a su frase secreta. Un ejemplo de uso podría ser no almacenar frases secretas, y que cada usuario la ingrese cada vez que se necesite firmar.

#### Ejemplo

~~~php
use phpWsAfip\WS\WSASS;

$bits = 4096;
$passphrase = 'Una frase secreta';
$private_key = WSASS::generatePrivateKey($bits, $passphrase);

echo $private_key;
~~~

### Generación de un _CSR_

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


## WSN (WebService de Negocio)

Por el momento el único WSN implementado es el WSFE (WebService de Facturación Electrónica).

Si precisa utilizar otro de los WebServices de AFIP, puede implementarlo utilizando cómo base la clase phpWsAfip/WS/WSFE.php. Luego puede compartirla agregándola al proyecto a través de un Pull Request, para que otros puedan aprovecharlo.

## WSFE (WebService de Facturación Electrónica)


## Desarrollo y Testing

Para correr el 

~~~
$ git clone git@github.com:jpcandioti/php-ws-afip.git
$ cd php-ws-afip
$ composer install 
~~~

Para correr los test es necesario tener un certificado de homologación con su respectiva _Clave privada_.

Los mismos deben estar almacenados en:

    

~~~
$ TEST_ALIAS=jgutierrez phpunit .
~~~

## Colaboración

Puede aportar al proyecto en la siguiente billetera Bitcoin: [132r6sUhqz44gfXAj5EpWxH2pWB59HbWKY]


[Generación de Certificados para Producción]: https://afip.gob.ar/ws/WSAA/WSAA.ObtenerCertificado.pdf
[WSASS: Cómo adherirse al servicio]: https://afip.gob.ar/ws/WSASS/WSASS_como_adherirse.pdf
[132r6sUhqz44gfXAj5EpWxH2pWB59HbWKY]: bitcoin:132r6sUhqz44gfXAj5EpWxH2pWB59HbWKY
