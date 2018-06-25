<?php

namespace phpWsAfip\Test;

use PHPUnit\Framework\TestCase;
use phpWsAfip\WS\WSAA;
use phpWsAfip\WS\WSFE;

/**
 * WsfeTest.
 *
 * Testing de la clase WSFE.
 *
 *
 * @author Juan Pablo Candioti (@JPCandioti)
 */
class WsfeTest extends TestCase
{
    private $wsaa;
    private $wsfe;

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

        $wsfe_config = array(
            'testing'           => true,
            'wsdl_cache_file'   => 'tmp/wsfehomo_wsdl.xml'
        );
        $this->wsfe = new WSFE($wsfe_config);

        parent::__construct();
    }

    public function testWsfe()
    {
        // Verifica que se descargue el WSDL.
        $this->assertGreaterThan(0, $this->wsfe->updateWsdlCacheFile());

        $xml = new \DOMDocument();
        $xml->load($this->wsfe->getWsdlCacheFile());
        // Verifica que el WSDL sea válido.
        $this->assertTrue($xml->schemaValidate('http://schemas.xmlsoap.org/wsdl/'));

        $ta_file = 'tmp/ta.xml';
        
        // Si existe un TA en caché se carga.
        if (file_exists($ta_file)) {
            $this->wsfe->setXmlTa(file_get_contents($ta_file));
        }

        $ta = $this->wsfe->getTa();

        // Si no hay TA en caché o el TA está vencido, se solicita uno.
        if (is_null($ta)) {
            $ta = $this->wsaa->requestTa(); // Se solicita un TA.
            $ta->asXml($ta_file);           // Se almacena el TA para no volver a solicitarlo.
        }

        // Carga el TA al servicio WSFE.
        $this->wsfe->setTa($ta);

        // Verifica el funcionamiento con una solicitud FEDummy.
        $dummy = $this->wsfe->FEDummy();
        $this->assertTrue(isset($dummy->FEDummyResult) && isset($dummy->FEDummyResult->AppServer));
    }
}