<?php

/**
 * MeetApp Kalsel — SMTP Mail Relay
 *
 * Standalone PHP 8+ script. Place this file on a webserver that has access
 * to the SMTP server. MeetApp posts JSON to this endpoint, and this script
 * forwards the email via raw SMTP socket.
 *
 * Requirements: PHP >= 8.0, openssl extension enabled
 *
 * Deployment: copy this single file to the relay webserver root.
 */

declare(strict_types=1);

// ============================================================
// CONFIGURATION — edit these values before deploying
// ============================================================

define('SMTP_HOST',       'smtp.bps.go.id');
define('SMTP_PORT',       587);
define('SMTP_USERNAME',   'pst6300');
define('SMTP_PASSWORD',   'kalselpst');
define('SMTP_ENCRYPTION', 'tls');           // 'tls' = STARTTLS on port 587 | 'ssl' = port 465

define('RELAY_TOKEN',     'howdoyouturnthison');  // Must match MAIL_RELAY_TOKEN in MeetApp .env

define('LOG_FILE',        __DIR__ . '/mail-relay.log');  // Log file path (must be writable)
define('LOG_MAX_BYTES',   5 * 1024 * 1024);              // Rotate log at 5MB

// ============================================================
// BOOTSTRAP
// ============================================================

header('Content-Type: application/json; charset=UTF-8');

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Parse JSON body
$raw     = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!is_array($payload)) {
    writeLog('REJECTED', '', '', '', 'Invalid JSON payload from ' . getClientIp());
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

// ============================================================
// AUTHENTICATION
// ============================================================

$token = $payload['token'] ?? '';

if (empty($token) || $token !== RELAY_TOKEN) {
    writeLog('REJECTED', '', '', '', 'Invalid token from ' . getClientIp());
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// ============================================================
// VALIDATE REQUIRED FIELDS
// ============================================================

$to          = trim($payload['to']           ?? '');
$subject     = trim($payload['subject']      ?? '');
$bodyHtml    = $payload['body_html']         ?? '';
$fromAddress = trim($payload['from_address'] ?? '');
$fromName    = trim($payload['from_name']    ?? '');

if (empty($to) || empty($subject) || empty($bodyHtml) || empty($fromAddress)) {
    writeLog('REJECTED', $to, $fromAddress, $subject, 'Missing required fields');
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields: to, subject, body_html, from_address']);
    exit;
}

if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    writeLog('REJECTED', $to, $fromAddress, $subject, 'Invalid recipient email address');
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Invalid recipient email address']);
    exit;
}

// ============================================================
// SEND EMAIL VIA RAW SMTP SOCKET
// ============================================================

try {
    sendSmtp($to, $subject, $bodyHtml, $fromAddress, $fromName);
    writeLog('SENT', $to, $fromAddress, $subject);
    http_response_code(200);
    echo json_encode(['status' => 'sent']);
} catch (RuntimeException $e) {
    writeLog('ERROR', $to, $fromAddress, $subject, $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

exit;

// ============================================================
// FUNCTIONS
// ============================================================

/**
 * Send an HTML email via raw SMTP socket (STARTTLS + AUTH LOGIN).
 *
 * @throws RuntimeException on any SMTP or socket error
 */
function sendSmtp(
    string $to,
    string $subject,
    string $bodyHtml,
    string $fromAddress,
    string $fromName
): void {
    $host       = SMTP_HOST;
    $port       = SMTP_PORT;
    $encryption = SMTP_ENCRYPTION;
    $username   = SMTP_USERNAME;
    $password   = SMTP_PASSWORD;

    // ── 1. Open TCP connection ─────────────────────────────────────────────
    $errno  = 0;
    $errstr = '';
    $socket = @fsockopen($host, $port, $errno, $errstr, 15);

    if (!$socket) {
        throw new RuntimeException("Socket connection failed to {$host}:{$port} — {$errstr} ({$errno})");
    }

    stream_set_timeout($socket, 15);

    // ── 2. Read server greeting ────────────────────────────────────────────
    $greeting = readSmtp($socket);
    expectCode($greeting, '220', 'Server greeting');

    // ── 3. EHLO ────────────────────────────────────────────────────────────
    writeSmtp($socket, 'EHLO ' . gethostname());
    $ehlo = readSmtp($socket);
    expectCode($ehlo, '250', 'EHLO');

    // ── 4. STARTTLS (for tls/STARTTLS) ─────────────────────────────────────
    if (strtolower($encryption) === 'tls') {
        writeSmtp($socket, 'STARTTLS');
        $tlsResponse = readSmtp($socket);
        expectCode($tlsResponse, '220', 'STARTTLS');

        // Upgrade to TLS
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new RuntimeException('Failed to enable TLS encryption');
        }

        // Re-issue EHLO after TLS handshake
        writeSmtp($socket, 'EHLO ' . gethostname());
        $ehlo2 = readSmtp($socket);
        expectCode($ehlo2, '250', 'EHLO after STARTTLS');
    }

    // ── 5. AUTH LOGIN ──────────────────────────────────────────────────────
    writeSmtp($socket, 'AUTH LOGIN');
    $authPrompt = readSmtp($socket);
    expectCode($authPrompt, '334', 'AUTH LOGIN');

    writeSmtp($socket, base64_encode($username));
    $userPrompt = readSmtp($socket);
    expectCode($userPrompt, '334', 'AUTH LOGIN username');

    writeSmtp($socket, base64_encode($password));
    $authResult = readSmtp($socket);
    expectCode($authResult, '235', 'AUTH LOGIN password');

    // ── 6. MAIL FROM ───────────────────────────────────────────────────────
    writeSmtp($socket, 'MAIL FROM:<' . $fromAddress . '>');
    $mailFrom = readSmtp($socket);
    expectCode($mailFrom, '250', 'MAIL FROM');

    // ── 7. RCPT TO ─────────────────────────────────────────────────────────
    writeSmtp($socket, 'RCPT TO:<' . $to . '>');
    $rcptTo = readSmtp($socket);
    expectCode($rcptTo, '250', 'RCPT TO');

    // ── 8. DATA ────────────────────────────────────────────────────────────
    writeSmtp($socket, 'DATA');
    $dataPrompt = readSmtp($socket);
    expectCode($dataPrompt, '354', 'DATA');

    // ── 9. Build and send MIME message ─────────────────────────────────────
    $boundary   = '----=_Part_' . md5(uniqid((string) mt_rand(), true));
    $fromHeader = empty($fromName)
        ? $fromAddress
        : '=?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromAddress . '>';

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    $headers  = "From: {$fromHeader}\r\n";
    $headers .= "To: {$to}\r\n";
    $headers .= "Subject: {$encodedSubject}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: base64\r\n";
    $headers .= "Date: " . date('r') . "\r\n";
    $headers .= "Message-ID: <" . md5(uniqid((string) mt_rand(), true)) . "@" . gethostname() . ">\r\n";
    $headers .= "X-Mailer: MeetApp-Relay/1.0 PHP/" . PHP_VERSION . "\r\n";

    // Body: base64 encoded HTML, split to 76-char lines (RFC 2045)
    $encodedBody = chunk_split(base64_encode($bodyHtml), 76, "\r\n");

    $message = $headers . "\r\n" . $encodedBody;

    // Dot-stuffing: lines starting with "." must be doubled (RFC 5321)
    $message = preg_replace('/^\./', '..', $message);

    fwrite($socket, $message . "\r\n.\r\n");
    $dataResult = readSmtp($socket);
    expectCode($dataResult, '250', 'End of DATA');

    // ── 10. QUIT ───────────────────────────────────────────────────────────
    writeSmtp($socket, 'QUIT');
    readSmtp($socket); // 221 Bye — no need to assert

    fclose($socket);
}

/**
 * Write a command to the SMTP socket.
 */
function writeSmtp($socket, string $command): void
{
    fwrite($socket, $command . "\r\n");
}

/**
 * Read one (possibly multi-line) SMTP response from the socket.
 * Handles multi-line responses (e.g. "250-..." lines ending with "250 ...").
 */
function readSmtp($socket): string
{
    $response = '';
    while ($line = fgets($socket, 512)) {
        $response .= $line;
        // A line without a dash after the code means it's the last line
        if (isset($line[3]) && $line[3] !== '-') {
            break;
        }
    }
    return $response;
}

/**
 * Assert the SMTP response starts with the expected code.
 *
 * @throws RuntimeException
 */
function expectCode(string $response, string $expectedCode, string $context): void
{
    $actualCode = substr(trim($response), 0, 3);
    if ($actualCode !== $expectedCode) {
        throw new RuntimeException(
            "SMTP error at [{$context}]: expected {$expectedCode}, got {$actualCode}. Response: " . trim($response)
        );
    }
}

/**
 * Write a structured log entry to mail-relay.log.
 * Format: [YYYY-MM-DD HH:MM:SS] STATUS | To: ... | From: ... | Subject: ... [| Error: ...]
 *
 * Rotates the log file when it exceeds LOG_MAX_BYTES.
 */
function writeLog(
    string $status,
    string $to,
    string $from,
    string $subject,
    string $error = ''
): void {
    // Rotate log if it exceeds max size
    if (file_exists(LOG_FILE) && filesize(LOG_FILE) >= LOG_MAX_BYTES) {
        rename(LOG_FILE, LOG_FILE . '.' . date('Ymd-His') . '.bak');
    }

    $timestamp = date('Y-m-d H:i:s');
    $line      = "[{$timestamp}] {$status} | To: {$to} | From: {$from} | Subject: {$subject}";

    if (!empty($error)) {
        $line .= " | Error: {$error}";
    }

    $line .= PHP_EOL;

    file_put_contents(LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

/**
 * Get the client IP address from server vars.
 */
function getClientIp(): string
{
    return $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_X_REAL_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? 'unknown';
}
