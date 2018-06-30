<?php

namespace phpWsAfip\WS;

use phpWsAfip\Exception\WsaaException;

/**
 * WSAA (WebService de Autenticación y Autorización).
 *
 * Genera TRA (Ticket de Requerimiento de Acceso) e interactua con el WSAA. Si la solicitud
 * fue aceptada devuelve el TA (Ticket de Acceso).
 *
 *
 * @author Juan Pablo Candioti (@JPCandioti)
 */
class WSAA extends WS
{
    /**
     * $wsn_name
     *
     * @var string      Nombre del WSN (WebService de Negocio) al que se desea acceder.
     */
    private $wsn_name;

    /**
     * $testing
     *
     * @var boolean     ¿Es servidor de homologación?.
     */
    private $testing;

    /**
     * $ta_expiration
     *
     * @var integer     Segundos de duración de los TA solicitados en los TRA.
     */
    private $ta_expiration;

    /**
     * $sec_tolerance
     *
     * @var integer     Segundos de tolerancia en el tiempo de generación de los TRA.
     */
    private $sec_tolerance;

    /**
     * $str_crt
     *
     * @var string      Texto del certificado X.509 firmado por la AFIP.
     */
    private $str_crt;

    /**
     * $privkey
     *
     * @var mixed       Texto de la clave privada, o ruta de un archivo con la clave privada, o un arreglo de
     *                  clave privada (texto o ruta de archivo) y frase secreta.
     */
    private $privkey;

    /**
     * $tra_tpl_file
     *
     * @var string      Plantilla dónde se expresa la ubicación de los archivos temporarios.
     */
    private $tra_tpl_file;

    /**
     * $tra_file_unlink
     *
     * @var boolean     Indica si el archivo con el TRA en formato XML debe ser eliminado luego de ser firmado.
     */
    private $tra_file_unlink;

    /**
     * $cms_file_unlink
     *
     * @var boolean     Indica si el archivo con la firma del TRA en formato Cryptographic Message Syntax (CMS)
     *                  debe ser eliminado luego de solicitar el TA.
     */
    private $cms_file_unlink;

    /**
     * $tra_id
     *
     * @var string      Hash identificador de la solicitud de un TA.
     */
    private $tra_id;

    /**
     * $ta
     *
     * @var \SimpleXMLElement   TA activo.
     */
    private $ta;


    /**
     * __construct
     *
     * Constructor de WSAA.
     *
     * Valores aceptados en $config:
     * - Todos los valores aceptados de phpWsAfip\WS\WS.
     * - testing            ¿Es servidor de homologación?.
     * - tra_tpl_file       Plantilla dónde se expresa la ubicación de los archivos temporarios.
     * - tra_file_unlink    Indica si el archivo con el TRA en formato XML debe ser eliminado luego de ser firmado.
     * - cms_file_unlink    Indica si el archivo con la firma del TRA en formato Cryptographic Message Syntax (CMS) debe ser eliminado luego de solicitar el TA.
     * - sec_tolerance      Segundos de tolerancia en el tiempo de generación de los TRA.
     * - ta_expiration      Segundos de duración de los TA solicitados en los TRA.
     *
     *
     * @param   string  $wsn_name   Nombre del WSN (WebService de Negocio) al que se desea acceder.
     * @param   string  $str_crt    Texto del certificado X.509 firmado por la AFIP.
     * @param   mixed   $privkey    Texto de la clave privada, o ruta de un archivo con la clave privada, o un arreglo de clave privada (texto o ruta de archivo) y frase secreta.
     * @param   array   $config     Configuración extra y de la clase WS.
     */
    public function __construct($wsn_name, $str_crt, $privkey, array $config = array())
    {
        $this->wsn_name             = $wsn_name;
        $this->str_crt              = $str_crt;
        $this->privkey              = is_array($privkey) ? $privkey : array($privkey, null);

        $this->tra_id               = null;
        $this->ta                   = null;

        $this->testing              = isset($config['testing'])         ? $config['testing']                : true;
        $this->tra_tpl_file         = isset($config['tra_tpl_file'])    ? $config['tra_tpl_file']           : '/tmp/tra_%s.xml';
        $this->tra_file_unlink      = isset($config['tra_file_unlink']) ? $config['tra_file_unlink']        : true;
        $this->cms_file_unlink      = isset($config['cms_file_unlink']) ? $config['cms_file_unlink']        : true;
        $this->sec_tolerance        = isset($config['sec_tolerance'])   ? (int) $config['sec_tolerance']    : 5;
        $this->ta_expiration        = isset($config['ta_expiration'])   ? (int) $config['ta_expiration']    : 120;

        if (!isset($config['ws_url'])) {
            $config['ws_url']           = $this->testing ? 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms' : 'https://wsaa.afip.gov.ar/ws/services/LoginCms';
        }

        if (!isset($config['wsdl_cache_file'])) {
            $config['wsdl_cache_file']  = $this->testing ? '/tmp/wsaahomo_wsdl.xml' : '/tmp/wsaa_wsdl.xml';
        }

        parent::__construct($config);
    }

    /**
     * getWsnName
     *
     * Retorna el nombre del WSN (WebService de Negocio).
     *
     *
     * @return  string                  Nombre del WSN (WebService de Negocio).
     */
    public function getWsnName()
    {
        return $this->wsn_name;
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
        // Si el TA está vencido hay que eliminarlo.
        if (strtotime($this->ta->header->expirationTime) < time()) {
            $this->ta       = null;
            $this->tra_id   = null;
        }

        return $this->ta;
    }

    /**
     * requestTa
     *
     * Solicita un TA nuevo.
     *
     *
     * @return  \SimpleXMLElement   TA activo.
     */
    public function requestTa()
    {
        if ($this->createTRA() && $this->signTRA()) {
            $this->ta = $this->callWSAA();
        }else {
            $this->ta = null;
        }
        
        return $this->ta;
    }

    /**
     * createTRA
     *
     * Crea un TRA y lo almacena en un archivo en formato XML.
     *
     *
     * @return  boolean      Retorna TRUE si fue exitoso, o FALSE si hubo un error.
     */
    private function createTRA()
    {
        $now = time();
        $this->tra_id = md5(mt_rand() . $now . mt_rand());  // Genera un identificador aleatorio.

        $TRA = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>' .
            '<loginTicketRequest version="1.0">' .
            '</loginTicketRequest>');
        $TRA->addChild('header');
        $TRA->header->addChild('uniqueId', $now);
        $TRA->header->addChild('generationTime', date('c', $now - $this->sec_tolerance));
        $TRA->header->addChild('expirationTime', date('c', $now + $this->ta_expiration));
        $TRA->addChild('service', $this->wsn_name);

        // Almacena el TRA en un archivo para poder firmarlo luego con el método signTRA().
        return $TRA->asXML(sprintf($this->tra_tpl_file, $this->tra_id));
    }

    /**
     * signTRA
     *
     * Firma el TRA generado y lo almacena en un archivo en formato CMS.
     *
     *
     * @return  boolean      Retorna TRUE si fue exitoso, o FALSE si hubo un error.
     */
    private function signTRA()
    {
        $success = false;

        if (!is_null($this->tra_id)) {
            $tra_file = sprintf($this->tra_tpl_file, $this->tra_id);
            if (file_exists($tra_file)) {
                if (openssl_pkcs7_sign(
                        $tra_file,
                        sprintf($this->tra_tpl_file . '.cms', $this->tra_id),   // Se almacena en formato Cryptographic Message Syntax (CMS)
                        $this->str_crt,
                        $this->privkey,
                        array(),
                        !PKCS7_DETACHED)
                    ) {
                    $success = true;
                }else {
                    throw new WsaaException('ERROR al generar la firma PKCS#7');
                }
                
                // Borra el archivo con el TRA.
                if ($this->tra_file_unlink) {
                    unlink($tra_file);
                }
            }else {
                throw new WsaaException("No se encontró el archivo TRA: $tra_file.");
            }
        }
        
        return $success;
    }

    /**
     * callWSAA
     *
     * Envía el CMS al método "loginCms" del WSAA.
     *
     *
     * @return  string      Retorna Ticket de Acceso (TA).
     */
    private function callWSAA()
    {
        $ta = false;

        if (!is_null($this->tra_id)) {
            $cms_file = sprintf($this->tra_tpl_file . '.cms', $this->tra_id);
            if (file_exists($cms_file)) {
                $cms = preg_split("|\n\n|", file_get_contents($cms_file));
                $results = $this->loginCms(array('in0' => $cms[1]));
                if (is_soap_fault($results)) {
                    throw new WsaaException("ERROR: {$results->faultcode} - {$results->faultstring}");
                }

                $ta = new \SimpleXMLElement($results->loginCmsReturn);

                // Borra el archivo CMS.
                if ($this->cms_file_unlink) {
                    unlink($cms_file);
                }
            }else {
                throw new WsaaException("No se encontró el archivo CMS: $cms_file.");
            }
        }
        
        return $ta;
    }
}
