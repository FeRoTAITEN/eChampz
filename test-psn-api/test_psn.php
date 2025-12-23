<?php

/**
 * أبسط script لاختبار PSN API
 * 
 * الاستخدام:
 * 1. ضع NPSSO token في المتغير $npsso
 * 2. شغل: php test_psn.php
 */

// إعدادات
$npsso = 'rQ3zqexhgmfDxUJSfXs2Y8q3ilvEOOyDt2JhkkdSf9cLCmRhiim4eOIMw7UFEIbG'; // ضع NPSSO token هنا (64 حرف)

// URLs
$authorizeUrl = 'https://ca.account.sony.com/api/authz/v3/oauth/authorize';
$tokenUrl = 'https://ca.account.sony.com/api/authz/v3/oauth/token';

// Client credentials
$clientId = '09515159-7237-4370-9b40-3806e67c0891';
$redirectUri = 'com.scee.psxandroid.scecompcall://redirect';
$scope = 'psn:mobile.v2.core psn:clientapp';

echo "🚀 بدء اختبار PSN API\n";
echo "========================\n\n";

// التحقق من NPSSO token
if ($npsso === 'YOUR_NPSSO_TOKEN_HERE' || empty($npsso)) {
    die("❌ خطأ: يجب وضع NPSSO token في المتغير \$npsso\n");
}

echo "✅ NPSSO Token: " . substr($npsso, 0, 20) . "...\n\n";

// الخطوة 1: الحصول على Access Code
echo "📝 الخطوة 1: الحصول على Access Code...\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $authorizeUrl . '?' . http_build_query([
        'access_type' => 'offline',
        'client_id' => $clientId,
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => $scope,
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true,
    CURLOPT_HTTPHEADER => [
        'Cookie: npsso=' . $npsso,
        'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

curl_close($ch);

echo "   Status Code: $httpCode\n";

if ($httpCode !== 302 && $httpCode !== 200) {
    die("❌ فشل في الحصول على Access Code\n   Response: " . substr($body, 0, 200) . "\n");
}

// استخراج Access Code من Location header
$accessCode = null;
if (preg_match('/Location: .*[?&]code=([^&\s]+)/i', $headers, $matches)) {
    $accessCode = urldecode($matches[1]);
} elseif (preg_match('/code=([^&"\']+)/', $body, $matches)) {
    $accessCode = urldecode($matches[1]);
}

if (!$accessCode) {
    die("❌ لم يتم العثور على Access Code في الـ response\n   Headers: " . substr($headers, 0, 500) . "\n");
}

echo "✅ Access Code: " . substr($accessCode, 0, 30) . "...\n\n";

// الخطوة 2: تبادل Access Code للحصول على Access Token
echo "📝 الخطوة 2: تبادل Access Code للحصول على Access Token...\n";

// جرب الطريقة 1: بدون credentials
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $tokenUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'code' => $accessCode,
        'grant_type' => 'authorization_code',
        'redirect_uri' => $redirectUri,
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$tokenData = json_decode($response, true);

// إذا فشلت الطريقة 1، جرب الطريقة 2: مع client_id
if ($httpCode !== 200 || !isset($tokenData['access_token'])) {
    echo "   ⚠️  الطريقة 1 فشلت، جرب الطريقة 2...\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $tokenUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'code' => $accessCode,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId,
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $tokenData = json_decode($response, true);
}

echo "   Status Code: $httpCode\n";

if ($httpCode !== 200 || !isset($tokenData['access_token'])) {
    echo "❌ فشل في الحصول على Access Token\n";
    echo "   Response: " . $response . "\n";
    die();
}

$accessToken = $tokenData['access_token'];
$refreshToken = $tokenData['refresh_token'] ?? 'N/A';
$expiresIn = $tokenData['expires_in'] ?? 'N/A';

echo "✅ Access Token: " . substr($accessToken, 0, 30) . "...\n";
echo "✅ Refresh Token: " . ($refreshToken !== 'N/A' ? substr($refreshToken, 0, 30) . "..." : 'N/A') . "\n";
echo "✅ Expires In: $expiresIn seconds\n\n";

// الخطوة 3: اختبار استخدام Access Token
echo "📝 الخطوة 3: اختبار استخدام Access Token...\n";

$apiUrl = 'https://m.np.playstation.net/api/userProfile/v1/users/me/profiles';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Status Code: $httpCode\n";

if ($httpCode === 200) {
    $profile = json_decode($response, true);
    
    if (isset($profile['profiles'][0]['onlineId'])) {
        $onlineId = $profile['profiles'][0]['onlineId'];
        echo "✅ نجح! PSN Username: $onlineId\n\n";
        
        echo "🎉 جميع الاختبارات نجحت!\n";
        echo "========================\n";
        echo "Access Token: " . substr($accessToken, 0, 50) . "...\n";
        echo "PSN Username: $onlineId\n";
    } else {
        echo "⚠️  تم الحصول على Access Token لكن لم يتم العثور على username\n";
        echo "   Response: " . substr($response, 0, 200) . "\n";
    }
} else {
    echo "❌ فشل في استخدام Access Token\n";
    echo "   Response: " . substr($response, 0, 200) . "\n";
}

echo "\n✨ انتهى الاختبار\n";
