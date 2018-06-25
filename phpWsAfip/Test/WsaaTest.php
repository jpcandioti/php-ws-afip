<?php

namespace phpWsAfip\Test;

use PHPUnit\Framework\TestCase;
use phpWsAfip\WS\WSAA;

/**
 * WsaaTest.
 *
 * Testing de la clase WSAA.
 *
 *
 * @author Juan Pablo Candioti (@JPCandioti)
 */
class WsaaTest extends TestCase
{
    private $wsaa;

    public function __construct()
    {
        $wsaa_config = array(
            'testing'           => true,
            'wsdl_cache_file'   => 'tmp/wsaahomo_wsdl.xml',
            'tra_tpl_file'      => 'tmp/tra_%s.xml'
        );
        $key_file = 'file://credentials/' . TEST_ALIAS . '.key';
        $pem_file = 'file://credentials/' . TEST_ALIAS . '.pem';
        $this->wsaa = new WSAA('wsfe', $pem_file, $key_file, $wsaa_config);

        parent::__construct();
    }

    public function testWsaa()
    {
        // Verifica que se descargue el WSDL.
        $this->assertGreaterThan(0, $this->wsaa->updateWsdlCacheFile());

        $xml = new \DOMDocument();
        $xml->load($this->wsaa->getWsdlCacheFile());
        // Verifica que el WSDL sea válido.
        $this->assertTrue($xml->schemaValidate('http://schemas.xmlsoap.org/wsdl/'));

        $ta = $this->wsaa->requestTa();
        $ta->asXml('tmp/ta.xml');   // Se almacena el TA para no volver a solicitarlo.

        // Verifica el TA según los datos del encabezado.
        $this->assertEquals($ta->header->source, 'CN=wsaahomo, O=AFIP, C=AR, SERIALNUMBER=CUIT 33693450239');
    }
}