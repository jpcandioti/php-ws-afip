<?php

namespace phpWsAfip\WS;

/**
 * WSFE (WebService de Facturación Electrónica).
 *
 * Permite interactuar con el WSFEv1.
 * Precisa un TA activo.
 *
 *
 * @author Juan Pablo Candioti (@JPCandioti)
 */
class WSFE extends WSN
{
    /**
     * $testing
     *
     * @var boolean     ¿Es servidor de homologación?.
     */
    private $testing;


    /**
     * __construct
     *
     * Constructor de WSFE.
     *
     * Valores aceptados en $config:
     * - Todos los valores aceptados de phpWsAfip\WS\WS.
     * - testing            ¿Es servidor de homologación?.
     *
     *
     * @param   array   $config     Configuración de WSFE.
     */
    public function __construct(array $config = array())
    {
        $this->testing                  = isset($config['testing'])     ? $config['testing']    : true;

        if (!isset($config['ws_url'])) {
            $config['ws_url']           = $this->testing ? 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx' : 'https://servicios1.afip.gov.ar/wsfev1/service.asmx';
        }

        if (!isset($config['wsdl_cache_file'])) {
            $config['wsdl_cache_file']  = $this->testing ? '/tmp/wsfehomo_wsdl.xml' : '/tmp/wsfe_wsdl.xml';
        }

        parent::__construct($config);
    }

    /**
     * isTesting
     *
     * Retorna si utiliza servicio de homologación.
     *
     *
     * @return      boolean                 ¿Utiliza servicio de homologación?
     */
    public function isTesting()
    {
        return $this->testing;
    }
}
