<?php
session_start();

$targetUrl = 'https://www.mca.gov.in/content/mca/global/en/additional-services/econsultation.html';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $targetUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);
$html = curl_exec($ch);
$err = curl_error($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($html === false || $status >= 400) {
    http_response_code(502);
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>MCA eConsultation</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet' /></head><body><div class='container py-5'><h1 class='h3'>MCA eConsultation</h1><div class='alert alert-danger'>Unable to fetch the MCA page right now. Please try again later.</div>";
    if (!empty($err)) {
        echo "<pre class='small text-muted'>Error: " . htmlspecialchars($err) . "</pre>";
    }
    echo "<p><a href='index.php' class='btn btn-secondary'>Back to Home</a></p></div></body></html>";
    exit;
}

// Remove potential inline Content-Security-Policy meta tag to allow external assets
$html = preg_replace('/<meta[^>]+http-equiv=["\']Content-Security-Policy["\'][^>]*>/i', '', $html);

// Ensure a <base> tag so relative URLs resolve to mca.gov.in
if (stripos($html, '<base ') === false) {
    $html = preg_replace('/<head[^>]*>/i', '$0' . "\n" . '<base href="https://www.mca.gov.in/">', $html, 1, $count);
    if (empty($count)) {
        $html = '<base href="https://www.mca.gov.in/">' . $html;
    }
}

echo $html;
