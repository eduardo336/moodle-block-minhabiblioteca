<?php
/**
 * Script de diagnóstico da integração Minha Biblioteca.
 * REMOVA ESTE ARQUIVO após o diagnóstico.
 *
 * Uso: php blocks/minhabiblioteca/test_api.php
 */

define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');

$apiurl  = get_config('block_minhabiblioteca', 'apiurl');
$apikey  = get_config('block_minhabiblioteca', 'apikey');
$endpoint = rtrim($apiurl, '/') . '/AuthenticatedUrl';

echo "=== Diagnóstico Minha Biblioteca ===\n\n";

// 1. Configurações salvas.
echo "1. Configurações do plugin:\n";
echo "   URL base : " . ($apiurl  ?: '(vazia!)') . "\n";
echo "   Chave API: " . ($apikey  ? substr($apikey, 0, 8) . '...' : '(vazia!)') . "\n";
echo "   Endpoint : $endpoint\n\n";

// 2. Resolução DNS.
echo "2. Resolução DNS de 'integracao.dli.minhabiblioteca.com.br':\n";
$ip = gethostbyname('integracao.dli.minhabiblioteca.com.br');
if ($ip === 'integracao.dli.minhabiblioteca.com.br') {
    echo "   FALHOU — DNS não resolveu o host. Verifique conectividade ou /etc/resolv.conf\n\n";
} else {
    echo "   OK — IP: $ip\n\n";
}

// 3. Conexão TCP na porta 443.
echo "3. Conexão TCP para $ip:443:\n";
$fp = @fsockopen('ssl://integracao.dli.minhabiblioteca.com.br', 443, $errno, $errstr, 5);
if ($fp) {
    fclose($fp);
    echo "   OK — porta 443 acessível\n\n";
} else {
    echo "   FALHOU — $errno: $errstr\n";
    echo "   Possível causa: firewall bloqueando saída na porta 443\n\n";
}

// 4. Chamada cURL real com dados fictícios.
echo "4. Chamada cURL ao endpoint:\n";

$xmlbody = '<?xml version="1.0" encoding="utf-8"?>'
    . '<CreateAuthenticatedUrlRequest xmlns="http://dli.zbra.com.br"'
    . ' xmlns:xsd="http://www.w3.org/2001/XMLSchema"'
    . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
    . '<FirstName>Teste</FirstName>'
    . '<LastName>Diagnostico</LastName>'
    . '<Email>teste.diagnostico@ufms.br</Email>'
    . '</CreateAuthenticatedUrlRequest>';

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $xmlbody,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_VERBOSE        => false,
    CURLOPT_HTTPHEADER     => [
        'X-DigitalLibraryIntegration-API-Key: ' . $apikey,
        'Content-Type: application/xml; charset=utf-8',
        'Host: integracao.dli.minhabiblioteca.com.br',
    ],
]);

$response  = curl_exec($ch);
$curlerror = curl_error($ch);
$curlinfo  = curl_getinfo($ch);
curl_close($ch);

if ($response === false) {
    echo "   FALHOU — cURL error: $curlerror\n";
    echo "   HTTP code: " . $curlinfo['http_code'] . "\n";
    echo "   Total time: " . $curlinfo['total_time'] . "s\n\n";
} else {
    echo "   HTTP code: " . $curlinfo['http_code'] . "\n";
    echo "   Total time: " . $curlinfo['total_time'] . "s\n";
    echo "   Resposta:\n$response\n\n";
}

// 5. Versão do cURL e OpenSSL.
echo "5. Versões:\n";
echo "   PHP    : " . PHP_VERSION . "\n";
$curlver = curl_version();
echo "   cURL   : " . $curlver['version'] . "\n";
echo "   OpenSSL: " . $curlver['ssl_version'] . "\n";
echo "   Protocolos suportados: " . implode(', ', $curlver['protocols']) . "\n\n";

echo "=== Fim do diagnóstico ===\n";
