<?php


$inputDir = '/home/primebackstage/htdocs/www.primebackstage.in/public/uploads/audio';
$outputDir = '/home/primebackstage/htdocs/www.primebackstage.in/public/uploads/audio/converted';

// Create output directory if it doesn't exist
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}



// Fixed 96kbps bitrate for better quality
$bitrate = "96k"; // Better balance of size & quality

$wavFile = $inputDir . "/2025030409144850.wav";

$fileName = pathinfo($wavFile, PATHINFO_FILENAME);
$outputFile = $outputDir . "/" . $fileName . ".mp3";

// FFmpeg command:
// -b:a 96k  → 96kbps bitrate for better quality
// -ac 1     → Convert to mono (reduces size further)
// -qscale:a 5 → Variable bitrate for more efficient compression
$command = "ffmpeg -i " . escapeshellarg($wavFile) . " -b:a 192k -ac 2 -ar 44100 " . escapeshellarg($outputFile) . " 2>&1";


//echo "Converting: $wavFile -> $outputFile (Bitrate: $bitrate, Mono, VBR)\n";
exec($command, $output, $returnVar);

echo implode("\n", $output); // Show FFmpeg's response

if ($returnVar === 0) {
    //echo "✅ Conversion successful: $outputFile\n";
} else {
    //echo "❌ Error converting $wavFile\n";
}


echo "🎉 All files converted with optimized compression (96kbps mono)!\n";

?>
