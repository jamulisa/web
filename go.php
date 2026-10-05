<?php
// Jamulisa attribution short-link handler.
// Every social short link (jamulisa.com/fb, /ig, /th, /status, ...) rewrites to
// this script. It logs the click, then 302s to wa.me with the source channel
// pre-filled in the message, so the customer's first message tells Lisa which
// channel the order came from. Zero cost, first-party, no third party.
//
// Log lives in ./data/clicks.log (blocked from the web by data/.htaccess).
// Read it with ~/jamulisa-attribution-report.py (FTP pull, never over HTTP).

$CHANNELS = [
    'fb'     => 'Facebook',
    'ig'     => 'Instagram',
    'th'     => 'Threads',
    'status' => 'WhatsApp Status',
    'tt'     => 'TikTok',
    'yt'     => 'YouTube',
    'gbp'    => 'Google',
    'web'    => 'Laman Web',
];

$WA = '60108666700';

$c = isset($_GET['c']) ? strtolower(preg_replace('/[^a-z0-9]/', '', $_GET['c'])) : '';

if ($c === '' || !isset($CHANNELS[$c])) {
    // Unknown/absent channel: still send them to WhatsApp, just untagged.
    header('Location: https://wa.me/' . $WA, true, 302);
    exit;
}

$dir = __DIR__ . '/data';
if (!is_dir($dir)) {
    @mkdir($dir, 0755);
}

// Hash the IP rather than storing it: enough to tell two visitors apart,
// not enough to be personal data under PDPA.
$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
$rec = [
    't'    => gmdate('c'),
    'c'    => $c,
    'ip'   => substr(hash('sha256', $ip . 'jamulisa-salt'), 0, 12),
    'ref'  => substr(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '', 0, 200),
    'ua'   => substr(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '', 0, 120),
];
@file_put_contents($dir . '/clicks.log', json_encode($rec) . "\n", FILE_APPEND | LOCK_EX);

$msg = 'Hi Lisa, saya nak order Jamu Lisa! (dari ' . $CHANNELS[$c] . ')';
header('Location: https://wa.me/' . $WA . '?text=' . rawurlencode($msg), true, 302);
exit;
