# phpWsAfip

Librería para la gestión de WsbServices de la Agencia Federal de Ingresos Públicos (AFIP - Organismo de hacienda de Argentina).

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

- Creación de un _CSR_:

    ~~~php
    use phpWsAfip\WS\WSASS;

    $private_key = file_get_contents('example.key');
    passphrase = null;

    // Los siguientes datos son de ejemplo y no concuerdan con una persona real.
    $dn = array(
        'countryName'           => 'AR',
        'stateOrProvinceName'   => 'Santa Fe',
        'localityName'          => 'Rosario',
        'organizationName'      => 'Juan Gutiérrez',
        'commonName'            => 'jgutierrez',
        'serialNumber'          => 'CUIT 20260795326'
    );

    $csr = WSASS::generateCsr($private_key, passphrase, $dn);

    echo $csr;
    ~~~

Si precisa utilizar otro de los WebServices de AFIP, puede implementarlo utilizando cómo base la clase phpWsAfip/WS/WSFE.php. Luego puede compartirla  agregandola al proyecto a través de un Pull Request, para que otros puedan aprovecharlo.


## Testing

$ TEST_ALIAS=jgutierrez phpunit .

## Colaboración

Puede aportar al proyecto en la siguiente billetera Bitcoin: [132r6sUhqz44gfXAj5EpWxH2pWB59HbWKY]


[Generación de Certificados para Producción]: https://afip.gob.ar/ws/WSAA/WSAA.ObtenerCertificado.pdf
[WSASS: Cómo adherirse al servicio]: https://afip.gob.ar/ws/WSASS/WSASS_como_adherirse.pdf
[132r6sUhqz44gfXAj5EpWxH2pWB59HbWKY]: bitcoin:132r6sUhqz44gfXAj5EpWxH2pWB59HbWKY