<?php
require_once __DIR__ . '/../config/auth.php';
$code=trim((string)($_GET['kode']??''));if(!preg_match('/^CFY[0-9]{6}[A-F0-9]{6}$/',$code))json_response(['ok'=>false,'message'=>'Kode pesanan tidak valid.'],400);
$st=$pdo->prepare('SELECT kode_pesanan,status_pesanan,status_bayar,metode_bayar,created_at,total FROM orders WHERE kode_pesanan=?');$st->execute([$code]);$o=$st->fetch();if(!$o)json_response(['ok'=>false,'message'=>'Pesanan tidak ditemukan.'],404);
json_response(['ok'=>true,'order'=>$o]);
