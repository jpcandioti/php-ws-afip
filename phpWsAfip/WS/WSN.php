<?php

namespace phpWsAfip\WS;

use phpWsAfip\Exception\WsnException;

/**
 * WSN (WebService de Negocio).
 *
 * Gestiona el TA (Ticket de Acceso) para cualquier WSN (WebService de Negocio) de AFIP.
 *
 *
 * @author Juan Pablo Candioti (@JPCandioti)
 */
abstract class WSN extends WS
{
    /**
     * $ta
     *
     * @var string      TA activo.
     */
    private $ta;

    /**
     * $ta_expiration_time
     *
     * @var integer     TA activo.
     */
    private $ta_expiration_time;

    /**
     * $ta_cuit
     *
     * @var integer     TA activo.
     */
    private $ta_cuit;

    /**
     * $ta_token
     *
     * @var string      TA activo.
     */
    private $ta_token;

    /**
     * $ta_sign
     *
     * @var string      TA activo.
     */
    private $ta_sign;


    /**
     * __construct
     *
     * Constructor de WSN.
     *
     *
     * @param   array   $config     Configuración de WS.
     */
    public function __construct(array $config = array())
    {
        $this->ta                   = null;
        $this->ta_expiration_time   = null;
        $this->ta_cuit              = null;
        $this->ta_token             = null;
        $this->ta_sign              = null;

        parent::__construct($config);
    }

    /**
     * setTa
     *
     * Define el TA activo.
     *
     *
     * @param       \SimpleXMLElement   $ta     TA activo.
     * @return      WSSession
     */
    public function setTa(\SimpleXMLElement $ta)
    {
        if (!isset($ta->header->expirationTime) || !isset($ta->credentials->token) || !isset($ta->credentials->sign)) {
            throw new WsnException('El TA es inválido.');
        }elseif (strtotime($ta->header->expirationTime) < time()) {
            throw new WsnException('El TA está vencido');
        }

        // Extraigo las variables de la empresa definidas en el TA.
        $variables = explode(',', $ta->header->destination);
        $destination = array();
        foreach ($variables as $asignacion) {
            list($campo, $valor) = explode('=', trim($asignacion));
            $destination[$campo] = $valor;
        }
        // Extraigo el CUIT definido en el TA.
        if (!isset($destination['SERIALNUMBER'])) {
            throw new WsnException('El TA es inválido. No se encontró el CUIT.');
        }
        preg_match('|^CUIT (\d{11})$|', $destination['SERIALNUMBER'], $arr);

        $this->ta                   = $ta;
        $this->ta_expiration_time   = strtotime($ta->header->expirationTime);
        $this->ta_cuit              = (float) $arr[1];
        $this->ta_token             = (string)$ta->credentials->token;
        $this->ta_sign              = (string)$ta->credentials->sign;
        
        return $this;
    }

    /**
     * getTa
     *
     * Retorna el TA activo.
     *
     *
     * @return  \SimpleXMLElement   TA activo.
     */
    public function getTa()
    {
        if (!is_null($this->ta_expiration_time) && $this->ta_expiration_time < time()) {
            $this->ta                   = null;
            $this->ta_expiration_time   = null;
            $this->ta_cuit              = null;
            $this->ta_token             = null;
            $this->ta_sign              = null;
        }

        return $this->ta;
    }

    /**
     * setTaExpirationTime
     *
     * Define el Unix Timestamp de expiración del TA activo.
     *
     *
     * @param       integer     $ta_expiration_time     Unix Timestamp de expiración del TA activo.
     * @return      WSSession
     */
    public function setTaExpirationTime(int $ta_expiration_time) {
        $this->ta_expiration_time = $ta_expiration_time;
        
        return $this;
    }
    
    /**
     * getTaExpirationTime
     *
     * Retorna el Unix Timestamp de expiración del TA activo.
     *
     *
     * @return  integer     Unix Timestamp de expiración del TA activo.
     */
    public function getTaExpirationTime()
    {
        return $this->ta_expiration_time;
    }

    /**
     * setTaCuit
     *
     * Define el CUIT del TA activo.
     *
     *
     * @param       float       $ta_cuit    CUIT del TA activo.
     * @return      WSSession
     */
    public function setTaCuit(float $ta_cuit) {
        $this->ta_cuit = (float)$ta_cuit;
        
        return $this;
    }
    
    /**
     * getTaCuit
     *
     * Retorna el CUIT del TA activo.
     *
     *
     * @return  float       CUIT del TA activo.
     */
    public function getTaCuit()
    {
        return $this->ta_cuit;
    }

    /**
     * setTaToken
     *
     * Define el Token del TA activo.
     *
     *
     * @param       string      $ta_token       Token del TA activo.
     * @return      WSSession
     */
    public function setTaToken($ta_token) {
        $this->ta_token = $ta_token;
        
        return $this;
    }
    
    /**
     * getTaToken
     *
     * Retorna el Token del TA activo.
     *
     *
     * @return  string      Token del TA activo.
     */
    public function getTaToken()
    {
        return $this->ta_token;
    }

    /**
     * setTaSign
     *
     * Define la firma del TA activo.
     *
     *
     * @param       string      $ta_sign    Firma del TA activo.
     * @return      WSSession
     */
    public function setTaSign($ta_sign) {
        $this->ta_sign = $ta_sign;
        
        return $this;
    }
    
    /**
     * getTaSign
     *
     * Retorna la firma del TA activo.
     *
     *
     * @return  string      Firma del TA activo.
     */
    public function getTaSign()
    {
        return $this->ta_sign;
    }

    /**
     * setXmlTa
     *
     * Define el TA activo desde un XML.
     *
     *
     * @param       string      $xml    XML del TA activo.
     * @return      WSSession
     */
    public function setXmlTa($xml)
    {
        $ta = new \SimpleXMLElement($xml);
        $this->setTa($ta);

        return $this;
    }

    /**
     * __call
     *
     * Método mágico que ejecuta las funciones definidas en el WebService.
     *
     * @param   string      $name       Nombre de la función del WebService.
     * @param   array       $arguments  Arreglo con los parámetros de la función WebService.
     * @return  \stdClass   Objeto con la estructura de la respuesta del WebService.
     */
    function __call($name, array $arguments)
    {
        if ($this->ta_expiration_time < time()) {
            throw new WsnException('El TA está vencido');
        }

        $datos = array(
            'Auth' => array(
               'Token'    => $this->ta_token,
               'Sign'     => $this->ta_sign,
               'Cuit'     => $this->ta_cuit
            )
        );

        if (isset($arguments[0])) {
            $datos += $arguments[0];
        }

        return parent::__call($name, array($datos));
    }
}
