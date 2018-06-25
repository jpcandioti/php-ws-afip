## Table of contents

- [\phpWsAfip\Exception\WsaaException](#class-phpwsafipexceptionwsaaexception)
- [\phpWsAfip\Exception\WsnException](#class-phpwsafipexceptionwsnexception)
- [\phpWsAfip\WS\WSASS (abstract)](#class-phpwsafipwswsass-abstract)
- [\phpWsAfip\WS\WSAA](#class-phpwsafipwswsaa)
- [\phpWsAfip\WS\WS (abstract)](#class-phpwsafipwsws-abstract)
- [\phpWsAfip\WS\WSFE](#class-phpwsafipwswsfe)
- [\phpWsAfip\WS\WSN (abstract)](#class-phpwsafipwswsn-abstract)

<hr />

### Class: \phpWsAfip\Exception\WsaaException

> WsaaException.

| Visibility | Function |
|:-----------|:---------|

*This class extends \Exception*

*This class implements \Throwable*

<hr />

### Class: \phpWsAfip\Exception\WsnException

> WsnException.

| Visibility | Function |
|:-----------|:---------|

*This class extends \Exception*

*This class implements \Throwable*

<hr />

### Class: \phpWsAfip\WS\WSASS (abstract)

> WSASS (Autoservicio de Acceso a WebServices). Genera claves privadas y certificados CSR para poder registrarse ante los WebServices AFIP.

| Visibility | Function |
|:-----------|:---------|
| public static | <strong>extractCsr(</strong><em>\string</em> <strong>$csr</strong>)</strong> : <em>array Certificate Signing Request.</em><br /><em>extractCsr Extrae el Distinguished Name (DN) de un Certificate Signing Request.</em> |
| public static | <strong>extractPem(</strong><em>\string</em> <strong>$pem</strong>)</strong> : <em>array Certificado X.509.</em><br /><em>extractPem Extrae la información de un certificado X.509.</em> |
| public static | <strong>generateCsr(</strong><em>mixed</em> <strong>$privkey</strong>, <em>string[]</em> <strong>$dn</strong>)</strong> : <em>string Certificate Signing Request.</em><br /><em>generateCsr Genera un Certificate Signing Request.</em> |
| public static | <strong>generatePrivateKey(</strong><em>\integer</em> <strong>$bits=2048</strong>, <em>\string</em> <strong>$passphrase=null</strong>)</strong> : <em>string Clave privada.</em><br /><em>generatePrivateKey Genera una Clave privada.</em> |

<hr />

### Class: \phpWsAfip\WS\WSAA

> WSAA (WebService de Autenticación y Autorización). Genera TRA (Ticket de Requerimiento de Acceso) e interactua con el WSAA. Si la solicitud fue aceptada devuelve el TA (Ticket de Acceso).

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>\string</em> <strong>$wsn_name</strong>, <em>\string</em> <strong>$str_crt</strong>, <em>mixed</em> <strong>$privkey</strong>, <em>array</em> <strong>$config=array()</strong>)</strong> : <em>void</em><br /><em>__construct Constructor de WSAA. Valores aceptados en $config: - Todos los valores aceptados de phpWsAfip\WS\WS. - testing            ¿Es servidor de homologación?. - tra_tpl_file       Plantilla dónde se expresa la ubicación de los archivos temporarios. - tra_file_unlink    Indica si el archivo con el TRA en formato XML debe ser eliminado luego de ser firmado. - cms_file_unlink    Indica si el archivo con la firma del TRA en formato Cryptographic Message Syntax (CMS) debe ser eliminado luego de solicitar el TA. - sec_tolerance      Segundos de tolerancia en el tiempo de generación de los TRA. - ta_expiration      Segundos de duración de los TA solicitados en los TRA.</em> |
| public | <strong>getTa()</strong> : <em>[\SimpleXMLElement](http://php.net/manual/en/class.simplexmlelement.php) TA activo.</em><br /><em>getTa Retorna el TA activo.</em> |
| public | <strong>getWsnName()</strong> : <em>string Nombre del WSN (WebService de Negocio).</em><br /><em>getWsnName Retorna el nombre del WSN (WebService de Negocio).</em> |
| public | <strong>isTesting()</strong> : <em>boolean ¿Utiliza servicio de homologación?</em><br /><em>isTesting Retorna si utiliza servicio de homologación.</em> |
| public | <strong>requestTa()</strong> : <em>[\SimpleXMLElement](http://php.net/manual/en/class.simplexmlelement.php) TA activo.</em><br /><em>requestTa Solicita un TA nuevo.</em> |

*This class extends [\phpWsAfip\WS\WS](#class-phpwsafipwsws-abstract)*

<hr />

### Class: \phpWsAfip\WS\WS (abstract)

> WS (WebService). Clase base para WebServices SOAP.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__call(</strong><em>\string</em> <strong>$name</strong>, <em>mixed[]</em> <strong>$arguments</strong>)</strong> : <em>\stdClass Objeto con la estructura de la respuesta del WebService.</em><br /><em>__call Método mágico que ejecuta las funciones definidas en el WebService.</em> |
| public | <strong>__construct(</strong><em>array</em> <strong>$config=array()</strong>)</strong> : <em>void</em><br /><em>__construct Constructor WS. Valores aceptados en $config: - ws_url             URL del WebService. - wsdl_cache_file    Ubicación dónde se almacena el caché del WSDL del WebService. - soap_options       Campo options del SoapClient del WebService.</em> |
| public | <strong>getSoapOptions()</strong> : <em>array Campo options del SoapClient del WebService.</em><br /><em>getSoapOptions Retorna el campo options del SoapClient del WebService.</em> |
| public | <strong>getWsUrl()</strong> : <em>string URL del WebService.</em><br /><em>getWsUrl Retorna la URL del WebService.</em> |
| public | <strong>getWsdlCacheFile()</strong> : <em>string Ubicación dónde se almacena el caché del WSDL del WebService.</em><br /><em>getWsdlCacheFile Retorna la ubicación dónde se almacena el caché del WSDL del WebService.</em> |
| public | <strong>updateWsdlCacheFile()</strong> : <em>int/false</em><br /><em>updateWsdlCacheFile Actualiza el archivo XML con la información WSDL del WebService.</em> |

<hr />

### Class: \phpWsAfip\WS\WSFE

> WSFE (WebService de Facturación Electrónica). Permite interactuar con el WSFEv1. Precisa un TA activo.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>array</em> <strong>$config=array()</strong>)</strong> : <em>void</em><br /><em>__construct Constructor de WSFE. Valores aceptados en $config: - Todos los valores aceptados de phpWsAfip\WS\WS. - testing            ¿Es servidor de homologación?.</em> |
| public | <strong>isTesting()</strong> : <em>boolean ¿Utiliza servicio de homologación?</em><br /><em>isTesting Retorna si utiliza servicio de homologación.</em> |

*This class extends [\phpWsAfip\WS\WSN](#class-phpwsafipwswsn-abstract)*

<hr />

### Class: \phpWsAfip\WS\WSN (abstract)

> WSN (WebService de Negocio). Gestiona el TA (Ticket de Acceso) para cualquier WSN (WebService de Negocio) de AFIP.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__call(</strong><em>\string</em> <strong>$name</strong>, <em>array</em> <strong>$arguments</strong>)</strong> : <em>\stdClass Objeto con la estructura de la respuesta del WebService.</em><br /><em>__call Método mágico que ejecuta las funciones definidas en el WebService.</em> |
| public | <strong>__construct(</strong><em>array</em> <strong>$config=array()</strong>)</strong> : <em>void</em><br /><em>__construct Constructor de WSN.</em> |
| public | <strong>getTa()</strong> : <em>[\SimpleXMLElement](http://php.net/manual/en/class.simplexmlelement.php) TA activo.</em><br /><em>getTa Retorna el TA activo.</em> |
| public | <strong>getTaCuit()</strong> : <em>float CUIT del TA activo.</em><br /><em>getTaCuit Retorna el CUIT del TA activo.</em> |
| public | <strong>getTaExpirationTime()</strong> : <em>integer Unix Timestamp de expiración del TA activo.</em><br /><em>getTaExpirationTime Retorna el Unix Timestamp de expiración del TA activo.</em> |
| public | <strong>getTaSign()</strong> : <em>string Firma del TA activo.</em><br /><em>getTaSign Retorna la firma del TA activo.</em> |
| public | <strong>getTaToken()</strong> : <em>string Token del TA activo.</em><br /><em>getTaToken Retorna el Token del TA activo.</em> |
| public | <strong>setTa(</strong><em>[\SimpleXMLElement](http://php.net/manual/en/class.simplexmlelement.php)</em> <strong>$ta</strong>)</strong> : <em>[\phpWsAfip\WS\WS](#class-phpwsafipwsws-abstract)Session</em><br /><em>setTa Define el TA activo.</em> |
| public | <strong>setTaCuit(</strong><em>\float</em> <strong>$ta_cuit</strong>)</strong> : <em>[\phpWsAfip\WS\WS](#class-phpwsafipwsws-abstract)Session</em><br /><em>setTaCuit Define el CUIT del TA activo.</em> |
| public | <strong>setTaExpirationTime(</strong><em>\integer</em> <strong>$ta_expiration_time</strong>)</strong> : <em>[\phpWsAfip\WS\WS](#class-phpwsafipwsws-abstract)Session</em><br /><em>setTaExpirationTime Define el Unix Timestamp de expiración del TA activo.</em> |
| public | <strong>setTaSign(</strong><em>\string</em> <strong>$ta_sign</strong>)</strong> : <em>[\phpWsAfip\WS\WS](#class-phpwsafipwsws-abstract)Session</em><br /><em>setTaSign Define la firma del TA activo.</em> |
| public | <strong>setTaToken(</strong><em>\string</em> <strong>$ta_token</strong>)</strong> : <em>[\phpWsAfip\WS\WS](#class-phpwsafipwsws-abstract)Session</em><br /><em>setTaToken Define el Token del TA activo.</em> |
| public | <strong>setXmlTa(</strong><em>\string</em> <strong>$xml</strong>)</strong> : <em>[\phpWsAfip\WS\WS](#class-phpwsafipwsws-abstract)Session</em><br /><em>setXmlTa Define el TA activo desde un XML.</em> |

*This class extends [\phpWsAfip\WS\WS](#class-phpwsafipwsws-abstract)*

