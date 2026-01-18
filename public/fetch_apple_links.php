<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;

// =======================
// 📧 Email settings
// =======================
$EMAIL_TO   = 'you@example.com';                 // <-- yahan apna email daalein
$EMAIL_FROM = 'noreply@primebackstage.in';       // optional: From header
$EMAIL_SUBJ = 'Cron Job Executed: fetch_apple_links';

// DB Setup
$config = include __DIR__ . '/../config/autoload/local.php';
$dbAdapter = new Adapter($config['db']);
$releaseTable = new TableGateway('tbl_release', $dbAdapter);

// Countries to check
$countries = ['IN', 'US', 'CA', 'AE', 'GB'];
$updated = 0;
$skipped = 0;
$rejected = 0;

// Fetch all delivered releases
$rowset = $releaseTable->select(['status' => 'delivered'])->toArray();

foreach ($rowset as $release) {
    $id     = $release['id'];
    $title  = trim($release['title']);
    $artist = trim($release['releaseArtist']);
    $upc    = trim($release['upc']);
    $existingLink = trim($release['apple_link']);
    $foundLink = '';
    $collectionType = '';

    // ✅ Get ALL ISRCs for this release
    $isrcRows = $dbAdapter->query(
        "SELECT isrc FROM tbl_track WHERE master_id = ? AND isrc != ''",
        [$id]
    )->toArray();
    $isrcList = array_filter(array_map('trim', array_column($isrcRows, 'isrc')));

    $titleLC = strtolower($title);
    $artistLC = strtolower($artist);

    echo "\n🔍 Checking: {$title} - {$artist} (UPC: {$upc}, ISRCs: " . implode(', ', $isrcList) . ")\n";

    // 1️⃣ UPC lookup
    if (!empty($upc)) {
        $url = "https://itunes.apple.com/lookup?upc={$upc}&country=IN";
        $res = @file_get_contents($url);
        $data = json_decode($res, true);

        if (!empty($data['results'][0])) {
            $item = $data['results'][0];
            $link = $item['collectionViewUrl'];
            $itemTitle = strtolower($item['collectionName']);
            $itemArtist = strtolower($item['artistName']);

            if (
                strpos($link, '/album/') !== false &&
                strpos($link, '?i=') === false &&
                $itemTitle === $titleLC &&
                $itemArtist === $artistLC
            ) {
                $foundLink = $link;
                $collectionType = $item['collectionType'];
                echo "✅ Found via UPC: $link\n";
            } else {
                echo "❌ UPC mismatch, trying ISRCs...\n";
            }
        }
    }

    // 2️⃣ ISRC lookup (multi-track)
    if (empty($foundLink) && !empty($isrcList)) {
        foreach ($isrcList as $singleIsrc) {
            $url = "https://itunes.apple.com/lookup?isrc={$singleIsrc}";
            $res = @file_get_contents($url);
            $data = json_decode($res, true);

            if (!empty($data['results'])) {
                foreach ($data['results'] as $item) {
                    if (!empty($item['collectionViewUrl'])) {
                        $link = $item['collectionViewUrl'];
                        $itemTitle = strtolower($item['collectionName']);
                        $itemArtist = strtolower($item['artistName']);

                        if (
                            strpos($link, '/album/') !== false &&
                            strpos($link, '?i=') === false &&
                            $itemTitle === $titleLC &&
                            $itemArtist === $artistLC
                        ) {
                            $foundLink = $link;
                            $collectionType = $item['collectionType'] ?? 'album';
                            echo "✅ Found via ISRC: $link (ISRC: $singleIsrc)\n";
                            break 2;
                        }
                    }
                }
            }
        }

        if (empty($foundLink)) {
            echo "❌ No match via any ISRC\n";
        }
    }

    // 3️⃣ Title + artist search
    if (empty($foundLink)) {
        $artistList = array_map('trim', explode(',', $artist));
        foreach ($artistList as $searchArtist) {
            foreach ($countries as $country) {
                $searchTerm = urlencode("{$title} {$searchArtist}");
                $apiUrl = "https://itunes.apple.com/search?term={$searchTerm}&country={$country}&media=music&entity=album";

                $response = @file_get_contents($apiUrl);
                $result = json_decode($response, true);

                if (!empty($result['results'])) {
                    foreach ($result['results'] as $item) {
                        $url = $item['collectionViewUrl'];
                        $itemTitle = strtolower($item['collectionName']);
                        $itemArtist = strtolower($item['artistName']);

                        $matchTitle =
                            stripos($itemTitle, $titleLC) !== false ||
                            stripos($titleLC, $itemTitle) !== false;

                        $matchArtist =
                            stripos($itemArtist, strtolower($searchArtist)) !== false ||
                            stripos(strtolower($searchArtist), $itemArtist) !== false;

                        if (
                            strpos($url, '/album/') !== false &&
                            strpos($url, '?i=') === false &&
                            $matchTitle && $matchArtist
                        ) {
                            $foundLink = $url;
                            $collectionType = $item['collectionType'];
                            echo "✅ Found via search: $url\n";
                            break 3;
                        }
                    }
                }

                sleep(1);
            }
        }
    }

    // 4️⃣ HTML scrape fallback
    if (empty($foundLink)) {
        $fallbackTerm = urlencode($title);
        $html = @file_get_contents("https://music.apple.com/in/search?term={$fallbackTerm}");
        if ($html && preg_match('/https:\/\/music\.apple\.com\/[a-z]{2}\/album\/[^\"]+/', $html, $matches)) {
            $scraped = $matches[0];
            if (strpos($scraped, '?i=') === false) {
                $foundLink = $scraped;
                echo "🔍 Found via HTML scrape: {$foundLink}\n";
            }
        }
    }

    // 5️⃣ Final DB check/update
    $shouldUpdate = false;

    if (!empty($foundLink)) {
        if (
            empty($existingLink) ||
            strpos($existingLink, '?i=') !== false ||
            strpos($existingLink, '/album/') === false ||
            $existingLink !== $foundLink
        ) {
            $shouldUpdate = true;
        }
    }

    if ($shouldUpdate) {
        $releaseTable->update(['apple_link' => $foundLink], ['id' => $id]);
        echo "✅ Updated DB ➜ {$foundLink} (Type: {$collectionType})\n";
        $updated++;
    } else {
        if (!empty($existingLink)) {
            echo "⏩ Link already valid, no update\n";
            $skipped++;
        } else {
            $releaseTable->update(['apple_link' => ''], ['id' => $id]);
            echo "❌ Cleared invalid link: {$title} - {$artist}\n";
            $rejected++;
        }
    }

    sleep(1);
}

$log("--- DONE ---");
$log("Updated: {$updated}");
$log("Skipped: {$skipped}");
$log("Rejected: {$rejected}");

// =======================
// 📧 Send summary email
// =======================
$summary = implode("\n", $runLog);
$body =
    "Cron: fetch_apple_links ran on " . date('Y-m-d H:i:s') . "\n" .
    "Host: " . (php_uname('n') ?: 'unknown') . "\n\n" .
    "Summary:\n" .
    "Updated: {$updated}\nSkipped: {$skipped}\nRejected: {$rejected}\n\n" .
    "---- Log ----\n" . $summary . "\n";

$headers = "From: {$EMAIL_FROM}\r\n".
           "Reply-To: {$EMAIL_FROM}\r\n".
           "X-Mailer: PHP/" . phpversion();

@mail($EMAIL_TO, $EMAIL_SUBJ, $body, $headers);
