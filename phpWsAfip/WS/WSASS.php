<?php

namespace phpWsAfip\WS;

/**
 * WSASS (Autoservicio de Acceso a WebServices).
 *
 * Genera claves privadas y certificados CSR para poder registrarse ante los WebServices AFIP.
 *
 *
 * @author Juan Pablo Candioti (@JPCandioti)
 */
abstract class WSASS
{
    /**
     * generatePrivateKey
     *
     * Genera una Clave privada.
     *
     *
     * @param   string  $bits       Largo de la Clave privada. AFIP exige que sea igual o mayor a 2048.
     * @param   string  $passphrase Frase secreta.
     * @return  string              Clave privada.
     */
    public static function generatePrivateKey($bits = 2048, $passphrase = null)
    {
        if (!class_exists('\phpseclib\Crypt\RSA')) {
            throw new Exception('Es necesario instalar phpseclib: composer require phpseclib/phpseclib:~2.0');
        }
        
        if ($bits < 2048) {
            throw new Exception('La clave privada debe generarse de al menos 2048 bits.');
        }
        
        $rsa = new \phpseclib\Crypt\RSA();
        
        if (!empty($passphrase)) {
            $rsa->setPassword($passphrase);
        }
        
        $pkey = $rsa->createKey($bits);

        return $pkey['privatekey'];
    }

    /**
     * generateCsr
     *
     * Genera un Certificate Signing Request.
     *
     *
     * @param   mixed       $privkey    Texto de la clave privada, o ruta de un archivo con la clave privada, o un arreglo de clave privada (texto o ruta de archivo) y frase secreta.
     * @param   string[]    $dn         Distinguished Name (DN).
     * @return  string                  Certificate Signing Request.
     */
    public static function generateCsr($privkey, array $dn)
    {
        $csr = openssl_csr_new($dn, $privkey);
        openssl_csr_export($csr, $str_csr);

        return $str_csr;
    }
    
    /**
     * extractCsr
     *
     * Extrae el Distinguished Name (DN) de un Certificate Signing Request.
     *
     * @param   string  $csr    CSR o ubicación del archivo .csr.
     * @return  array           Certificate Signing Request.
     */
    public static function extractCsr($csr)
    {
        return openssl_csr_get_subject($csr, false);
    }
    
    /**
     * extractPem
     *
     * Extrae la información de un certificado X.509.
     *
     * @param   string  $pem    Ubicación del archivo .PEM.
     * @return  array           Certificado X.509.
     */
    public static function extractPem($pem)
    {
        return openssl_x509_parse($pem, false);
    }
}
