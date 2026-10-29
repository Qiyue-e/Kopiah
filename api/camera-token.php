<?php
require_once __DIR__ . '/../config/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['ok'=>false,'message'=>'Metode tidak didukung.'],405);
csrf_check();
$rateStarted=(int)($_SESSION['moment_rate_started']??0);if(!$rateStarted||time()-$rateStarted>=3600){$_SESSION['moment_count_hour']=0;$_SESSION['moment_rate_started']=time();}$recent = (int)($_SESSION['moment_count_hour'] ?? 0);
if (!isset($_SESSION['camera_tokens'])) $_SESSION['camera_tokens'] = [];
foreach ($_SESSION['camera_tokens'] as $k=>$v) if (($v['created'] ?? 0) < time()-600) unset($_SESSION['camera_tokens'][$k]);
if ($recent >= 5) json_response(['ok'=>false,'message'=>'Batas foto untuk sesi ini sudah tercapai. Coba lagi nanti.'],429);
$token = bin2hex(random_bytes(32));
$_SESSION['camera_tokens'][$token] = ['created'=>time(),'ip'=>($_SERVER['REMOTE_ADDR'] ?? 'unknown')];
json_response(['ok'=>true,'token'=>$token,'expires_in'=>600]);
