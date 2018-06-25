<?php

namespace phpWsAfip\Test;

use PHPUnit\Framework\TestCase;
use phpWsAfip\WS\WSASS;

/**
 * WsassTest.
 *
 * Testing de la clase WSASS.
 *
 *
 * @author Juan Pablo Candioti (@JPCandioti)
 */
class WsassTest extends TestCase
{
    /**
     * Test de WSASS::generatePrivateKey()
     */
    public function testGeneratePrivateKey()
    {
        // Genera una clave privada.
        $privatekey = WSASS::generatePrivateKey();

        // Firma el texto.
        $plaintext = 'Texto de prueba que será firmado para corroborar la clave privada.';
        openssl_sign($plaintext, $signature, $privatekey);

        // Extrae la clave pública de la clave privada generada anteriormente.
        $publickey = openssl_pkey_get_public(openssl_pkey_get_details(openssl_pkey_get_private($privatekey))['key']);

        // Verifica la firma del texto.
        $this->assertEquals(openssl_verify($plaintext, $signature, $publickey), 1);
    }

    /**
     * Test de WSASS::generatePrivateKey() con Frase secreta.
     */
    public function testGeneratePrivateKeyWithPassphrase()
    {
        // Genera una clave privada con Frase secreta.
        $passphrase = 'Frase secreta';
        $privatekey = WSASS::generatePrivateKey(4096, $passphrase);

        // Firma el texto.
        $plaintext = 'Texto de prueba que será firmado para corroborar la clave privada.';
        openssl_sign($plaintext, $signature, array($privatekey, $passphrase));

        // Extrae la clave pública de la clave privada generada anteriormente.
        $publickey = openssl_pkey_get_public(openssl_pkey_get_details(openssl_pkey_get_private(array($privatekey, $passphrase)))['key']);

        // Verifica la firma del texto.
        $this->assertEquals(openssl_verify($plaintext, $signature, $publickey), 1);
    }

    /**
     * Test de WSAA::generateCsr().
     */
    public function testGenerateCsr()
    {
        // Genera una clave privada con Frase secreta.
        $passphrase = 'Frase secreta';
        $privatekey = WSASS::generatePrivateKey(4096, $passphrase);

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

        // Genera un CSR en formato PKCS#10 con la clave privada, la frase secreta, y el DN.
        $csr = WSASS::generateCsr(array($privatekey, $passphrase), $dn);

        // Extrae el DN desde el CSR.
        $dn_csr = WSASS::extractCsr($csr);

        // Compara el DN generado del DN extraído.
        $this->assertEquals($dn['serialNumber'], $dn_csr['serialNumber']);
    }
}