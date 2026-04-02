<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Autentica o usuário na Minha Biblioteca e redireciona.
 *
 * @package    block_minhabiblioteca
 * @copyright  2026 UFMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

// Exige login e valida sesskey (proteção CSRF).
require_login();
require_sesskey();

// Verifica capability.
$context = context_system::instance();
require_capability('block/minhabiblioteca:view', $context);

// Recupera configurações do plugin.
$apiurl = get_config('block_minhabiblioteca', 'apiurl');
$apikey = get_config('block_minhabiblioteca', 'apikey');

if (empty($apikey)) {
    \core\notification::error(get_string('error_nokey', 'block_minhabiblioteca'));
    redirect(new moodle_url('/'));
}

// Dados do usuário logado.
$firstname = $USER->firstname;
$lastname  = $USER->lastname;
$email     = $USER->email;

// Monta o XML da requisição.
$xmlbody = '<?xml version="1.0" encoding="utf-8"?>' .
    '<CreateAuthenticatedUrlRequest xmlns="http://dli.zbra.com.br"' .
    ' xmlns:xsd="http://www.w3.org/2001/XMLSchema"' .
    ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
    '<FirstName>' . htmlspecialchars($firstname, ENT_XML1, 'UTF-8') . '</FirstName>' .
    '<LastName>'  . htmlspecialchars($lastname,  ENT_XML1, 'UTF-8') . '</LastName>' .
    '<Email>'     . htmlspecialchars($email,     ENT_XML1, 'UTF-8') . '</Email>' .
    '</CreateAuthenticatedUrlRequest>';

// Endpoint completo.
$endpoint = rtrim($apiurl, '/') . '/AuthenticatedUrl';

// Realiza a chamada HTTP com cURL.
$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $xmlbody,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => [
        'X-DigitalLibraryIntegration-API-Key: ' . $apikey,
        'Content-Type: application/xml; charset=utf-8',
        'Host: integracao.dli.minhabiblioteca.com.br',
    ],
]);

$response = curl_exec($ch);
$curlerror = curl_error($ch);
curl_close($ch);

// Trata erro de conexão.
if ($response === false || !empty($curlerror)) {
    \core\notification::error(get_string('error_apicall', 'block_minhabiblioteca'));
    redirect(new moodle_url('/'));
}

// Parseia o XML de resposta.
libxml_use_internal_errors(true);
$xml = simplexml_load_string($response);
if ($xml === false) {
    \core\notification::error(get_string('error_xmlparse', 'block_minhabiblioteca'));
    redirect(new moodle_url('/'));
}

// Registra o namespace da resposta.
$ns = $xml->getNamespaces(true);
$defaultns = reset($ns); // 'http://dli.zbra.com.br'
$xml->registerXPathNamespace('dli', $defaultns);

$successnodes = $xml->xpath('//dli:Success');
$success = !empty($successnodes) ? strtolower((string) $successnodes[0]) : 'false';

if ($success === 'true') {
    $urlnodes = $xml->xpath('//dli:AuthenticatedUrl');
    $authurl  = !empty($urlnodes) ? trim((string) $urlnodes[0]) : '';

    if (!empty($authurl) && filter_var($authurl, FILTER_VALIDATE_URL)) {
        // Redireciona o usuário para a URL autenticada.
        redirect($authurl);
    }

    // URL vazia ou inválida na resposta.
    \core\notification::error(get_string('error_xmlparse', 'block_minhabiblioteca'));
    redirect(new moodle_url('/'));
} else {
    // API retornou Success=false; exibe a mensagem de erro da API.
    $messagenodes = $xml->xpath('//dli:Message');
    $apimessage   = !empty($messagenodes) ? (string) $messagenodes[0] : '';

    \core\notification::error(get_string('error_apiresponse', 'block_minhabiblioteca', $apimessage));
    redirect(new moodle_url('/'));
}
