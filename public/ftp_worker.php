#!/usr/bin/env php
<?php
// Standalone CLI worker. Usage: php ftp_worker.php <id>
if (php_sapi_name() !== 'cli') { fwrite(STDERR, "CLI only\n"); exit(1); }
$id = $argv[1] ?? '';
if ($id === '') { fwrite(STDERR, "Missing id\n"); exit(1); }

$jobFile = sys_get_temp_dir()."/ftp_job_{$id}.json";
$progFile= sys_get_temp_dir()."/ftp_progress_{$id}.json";
if (!is_file($jobFile)) { file_put_contents($progFile, json_encode(['id'=>$id,'status'=>'error','error'=>'job not found'])); exit(1); }

$job = json_decode(file_get_contents($jobFile), true);
if (!$job) { file_put_contents($progFile, json_encode(['id'=>$id,'status'=>'error','error'=>'bad job json'])); exit(1); }

$local = $job['local'] ?? '';
$remoteFile = $job['remote_file'] ?? basename($local);
$ftp = $job['ftp'] ?? [];
$host = $ftp['host'] ?? ''; $user=$ftp['user'] ?? ''; $pass=$ftp['pass'] ?? '';
$port = (int)($ftp['port'] ?? 21);
$root = (string)($ftp['root'] ?? '/');

$total = is_file($local) ? filesize($local) : 0;
$set = function($uploaded,$status='uploading') use($progFile,$id,$total){
    $percent = $total>0 ? (int)floor(($uploaded/$total)*100) : 0;
    file_put_contents($progFile, json_encode([
        'id'=>$id,'uploaded'=>$uploaded,'total'=>$total,'percent'=>$percent,'status'=>$status,'ts'=>time()
    ], JSON_PRETTY_PRINT));
};

$finish = function($ok,$msg='') use($progFile,$id,$total){
    $data = ['id'=>$id,'uploaded'=>$total,'total'=>$total,'percent'=>$ok?100:0,'status'=>$ok?'done':'error','ts'=>time()];
    if ($msg!=='') $data['error']=$msg;
    file_put_contents($progFile, json_encode($data, JSON_PRETTY_PRINT));
};

try {
    if (!function_exists('ftp_connect')) { $finish(false,'php-ftp extension missing'); exit(1); }
    if (!is_file($local)) { $finish(false,'local file not found'); exit(1); }

    $c = @ftp_connect($host, $port, 60);
    if (!$c) { $finish(false,'ftp connect failed'); exit(1); }
    if (!@ftp_login($c, $user, $pass)) { @ftp_close($c); $finish(false,'ftp login failed'); exit(1); }
    @ftp_pasv($c, true);
    @ftp_set_option($c, FTP_TIMEOUT_SEC, 600);
    if ($root && $root !== '/') { @ftp_chdir($c, '/'); @ftp_chdir($c, $root); }

    $fp = @fopen($local, 'rb');
    if (!$fp) { @ftp_close($c); $finish(false,'cannot open local'); exit(1); }

    $ret = @ftp_nb_fput($c, $remoteFile, $fp, FTP_BINARY);
    if ($ret === false) { @fclose($fp); @ftp_close($c); $finish(false,'ftp start failed'); exit(1); }

    while ($ret === FTP_MOREDATA) {
        $set((int)ftell($fp), 'uploading');
        usleep(200000);
        $ret = @ftp_nb_continue($c);
    }
    $ok = ($ret === FTP_FINISHED);
    @fclose($fp); @ftp_close($c);

    $finish($ok, $ok ? '' : 'ftp finished with error');
    // cleanup temp local if under /tmp
    if (strpos($local, sys_get_temp_dir())===0) @unlink($local);
    @unlink($jobFile);
    exit($ok?0:1);
} catch (Throwable $e) {
    $finish(false, $e->getMessage());
    exit(1);
}
