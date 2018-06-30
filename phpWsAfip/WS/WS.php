<?php

namespace phpWsAfip\WS;

/**
 * WS (WebService).
 *
 * Clase base para WebServices SOAP.
 *
 *
 * @author Juan Pablo Candioti (@JPCandioti)
 */
abstract class WS
{
    /**
     * $ws_url
     *
     * @var string      URL del WebService.
     */
    protected $ws_url;

    /**
     * $wsdl_url
     *
     * @var string      URL del WSDL del WebService.
     */
    protected $wsdl_url;

    /**
     * $wsdl_cache_file
     *
     * @var string      Ubicación dónde se almacena el caché del WSDL del WebService.
     */
    protected $wsdl_cache_file;

    /**
     * $soap_options
     *
     * @var array       Campo options del SoapClient del WebService.
     */
    protected $soap_options;

    /**
     * $soap_client
     *
     * @var \SoapClient Instancia del cliente SOAP ya configurado.
     */
    protected $soap_client;


    /**
     * __construct
     *
     * Constructor WS.
     * 
     * Valores aceptados en $config:
     * - ws_url             URL del WebService.
     * - wsdl_cache_file    Ubicación dónde se almacena el caché del WSDL del WebService.
     * - soap_options       Campo options del SoapClient del WebService.
     *
     *
     * @param   array   $config     Configuración.
     */
    public function __construct(array $config = array())
    {
        $this->ws_url           = isset($config['ws_url'])          ? $config['ws_url']                 : '';
        $this->wsdl_url         = isset($config['ws_url'])          ? $config['ws_url'] . '?wsdl'       : null;
        $this->wsdl_cache_file  = isset($config['wsdl_cache_file']) ? $config['wsdl_cache_file']        : null;
        $this->soap_client      = null;

        $this->soap_options = array(
             'soap_version' => SOAP_1_2,
             'cache_wsdl'   => WSDL_CACHE_NONE,
             'trace'        => 1,
             'encoding'     => 'ISO-8859-1',
             'exceptions'   => 0
        );

        if (isset($config['soap_options']) && is_array($config['soap_options'])) {
            $this->soap_options += $config['soap_options'];
        }
    }

    /**
     * getSoapOptions
     *
     * Retorna el campo options del SoapClient del WebService.
     *
     *
     * @return  array       Campo options del SoapClient del WebService.
     */
    public function getSoapOptions()
    {
        return $this->soap_options;
    }

    /**
     * getWsUrl
     *
     * Retorna la URL del WebService.
     *
     *
     * @return  string      URL del WebService.
     */
    public function getWsUrl()
    {
        return $this->ws_url;
    }

    /**
     * getWsdlCacheFile
     *
     * Retorna la ubicación dónde se almacena el caché del WSDL del WebService.
     *
     *
     * @return  string      Ubicación dónde se almacena el caché del WSDL del WebService.
     */
    public function getWsdlCacheFile()
    {
        return $this->wsdl_cache_file;
    }

    /**
     * __call
     *
     * Método mágico que ejecuta las funciones definidas en el WebService.
     *
     * @param   string      $name       Nombre de la función del WebService.
     * @param   mixed[]     $arguments  Arreglo con los parámetros de la función WebService.
     * @return  \stdClass               Objeto con la estructura de la respuesta del WebService.
     */
    public function __call($name, array $arguments)
    {
        if (is_null($this->soap_client)) {
            $wsdl = $this->wsdl_url;
            if (!empty($this->wsdl_cache_file) && (file_exists($this->wsdl_cache_file) || $this->updateWsdlCacheFile())) {
                $wsdl = $this->wsdl_cache_file;
            }
            $this->soap_client = new \SoapClient($wsdl, $this->soap_options);
        }

        return $this->soap_client->$name($arguments[0]);
    }
    
    /**
     * updateWsdlCacheFile
     *
     * Actualiza el archivo XML con la información WSDL del WebService.
     *
     *
     * @return int|false
     */
    public function updateWsdlCacheFile()
    {
        return file_put_contents($this->wsdl_cache_file, file_get_contents($this->wsdl_url));
    }
}
