<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['ok'=>false,'message'=>'Metode tidak didukung.'],405);
csrf_check();
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) json_response(['ok'=>false,'message'=>'Data foto tidak terbaca.'],400);
$token = (string)($data['token'] ?? '');
$tokenData = $_SESSION['camera_tokens'][$token] ?? null;
if (!$tokenData || !hash_equals((string)($tokenData['ip'] ?? ''), (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown')) || time()-(int)($tokenData['created'] ?? 0)>600) {
    json_response(['ok'=>false,'message'=>'Token kamera tidak valid atau kedaluwarsa. Buka kamera kembali.'],403);
}
unset($_SESSION['camera_tokens'][$token]); // token selalu sekali pakai, termasuk untuk request gagal
$rateStarted=(int)($_SESSION['moment_rate_started']??0);if(!$rateStarted||time()-$rateStarted>=3600){$_SESSION['moment_count_hour']=0;$_SESSION['moment_rate_started']=time();}
$ip = substr((string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 45);
$st = $pdo->prepare('SELECT COUNT(*) FROM moments WHERE ip_address=? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)');
$st->execute([$ip]);
if ((int)$st->fetchColumn() >= 5 || (int)($_SESSION['moment_count_hour'] ?? 0) >= 5) json_response(['ok'=>false,'message'=>'maksimal 5 foto per jam. terima kasih sudah memahami ya.'],429);
$photo = (string)($data['image'] ?? '');
if (!preg_match('#^data:image/(jpeg|png);base64,#i', $photo, $matches)) json_response(['ok'=>false,'message'=>'Foto harus diambil langsung lewat kamera dalam format JPEG atau PNG.'],400);
$raw = base64_decode(substr($photo, strpos($photo, ',')+1), true);
if ($raw === false || strlen($raw) < 100 || strlen($raw) > 2097152) json_response(['ok'=>false,'message'=>'Ukuran foto maksimal 2 MB. Coba ambil foto dengan ukuran lebih kecil.'],413);
$finfo = new finfo(FILEINFO_MIME_TYPE);$mime = $finfo->buffer($raw);
if (!in_array($mime,['image/jpeg','image/png'],true)) json_response(['ok'=>false,'message'=>'Tipe foto tidak valid.'],400);
$imgInfo = @getimagesizefromstring($raw);
if (!$imgInfo || !in_array($imgInfo['mime']??'', ['image/jpeg','image/png'],true)) json_response(['ok'=>false,'message'=>'Berkas foto tidak bisa dibaca.'],400);
if (!function_exists('imagecreatefromstring')) json_response(['ok'=>false,'message'=>'Fitur pemrosesan foto belum aktif di PHP. Aktifkan ekstensi GD pada XAMPP.'],500);
$source = @imagecreatefromstring($raw);if (!$source) json_response(['ok'=>false,'message'=>'Gambar gagal diproses.'],400);
$w=imagesx($source);$h=imagesy($source);$max=1600;$scale=min(1,$max/max($w,$h));$nw=max(1,(int)round($w*$scale));$nh=max(1,(int)round($h*$scale));
$out=imagecreatetruecolor($nw,$nh);imagecopyresampled($out,$source,0,0,0,0,$nw,$nh,$w,$h);imagedestroy($source);
$dir=dirname(__DIR__).'/uploads/moments';if(!is_dir($dir)&&!mkdir($dir,0755,true)&&!is_dir($dir)){imagedestroy($out);json_response(['ok'=>false,'message'=>'Folder penyimpanan foto tidak tersedia.'],500);}
$file=bin2hex(random_bytes(18)).'.jpg';$full=$dir.'/'.$file;if(!imagejpeg($out,$full,86)){imagedestroy($out);json_response(['ok'=>false,'message'=>'Foto gagal disimpan.'],500);}imagedestroy($out);
$name=safe_substr(trim((string)($data['nama']??'')),0,100);$caption=safe_substr(trim((string)($data['caption']??'')),0,500);$type=in_array(($data['type']??'Makanan'),['Makanan','Selfie'],true)?$data['type']:'Makanan';
try{$ins=$pdo->prepare("INSERT INTO moments (nama_pelanggan,caption,tipe_foto,file_path,status,upload_token,ip_address) VALUES (?,?,?,?, 'pending',?,?)");$ins->execute([$name?:null,$caption,'Makanan'===$type?'Makanan':'Selfie','uploads/moments/'.$file,hash('sha256',$token),$ip]);$_SESSION['moment_count_hour']=(int)($_SESSION['moment_count_hour']??0)+1;}catch(Throwable $e){@unlink($full);json_response(['ok'=>false,'message'=>'Foto diterima server tetapi gagal dicatat. Coba lagi.'],500);}
json_response(['ok'=>true,'message'=>'foto berhasil dikirim dan menunggu moderasi. terima kasih sudah berbagi momen!']);
