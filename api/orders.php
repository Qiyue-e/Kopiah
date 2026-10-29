<?php
declare(strict_types=1);
require_once __DIR__.'/../config/auth.php';
require_role(['owner','kasir']);
if($_SERVER['REQUEST_METHOD']==='GET'){
 $action=(string)($_GET['action']??'list');if($action!=='list')json_response(['ok'=>false,'message'=>'Aksi tidak valid.'],400);
 $sql="SELECT o.id,o.kode_pesanan,o.nama_pelanggan,o.subtotal,o.diskon,o.pajak,o.total,o.metode_bayar,o.status_bayar,o.status_pesanan,o.catatan,o.created_at,t.nomor_meja,u.nama kasir FROM orders o LEFT JOIN `tables` t ON t.id=o.table_id LEFT JOIN users u ON u.id=o.kasir_id WHERE o.created_at>=DATE_SUB(NOW(),INTERVAL 7 DAY) AND o.status_pesanan<>'Dibatalkan' ORDER BY o.created_at DESC LIMIT 80";
 $orders=dbq($sql)->fetchAll();$itemStmt=$pdo->prepare('SELECT nama_menu,qty,harga,opsi_json,catatan FROM order_items WHERE order_id=? ORDER BY id');foreach($orders as &$o){$itemStmt->execute([(int)$o['id']]);$o['items']=$itemStmt->fetchAll();$o['id']=(int)$o['id'];$o['total']=(float)$o['total'];}unset($o);json_response(['ok'=>true,'orders'=>$orders,'server_time'=>date(DATE_ATOM)]);
}
if($_SERVER['REQUEST_METHOD']!=='POST')json_response(['ok'=>false,'message'=>'Metode tidak didukung.'],405);
csrf_check();$action=(string)($_POST['action']??'');$uid=(int)(current_user()['id']??0);
try{
 if($action==='status'){
  $id=(int)($_POST['id']??0);$status=(string)($_POST['status_pesanan']??'');if(!in_array($status,['Diterima','Diproses','Siap','Selesai','Dibatalkan'],true))json_response(['ok'=>false,'message'=>'Status tidak valid.'],400);
  $st=$pdo->prepare('UPDATE orders SET status_pesanan=?,kasir_id=COALESCE(kasir_id,?) WHERE id=?');$st->execute([$status,$uid,$id]);log_activity('update_order_status','Pesanan ID '.$id.' → '.$status);json_response(['ok'=>true,'message'=>'status pesanan diperbarui.']);
 }elseif($action==='payment'){
  $id=(int)($_POST['id']??0);$status=(string)($_POST['status_bayar']??'');$method=(string)($_POST['metode_bayar']??'Kasir');if(!in_array($status,['Belum Bayar','Menunggu QRIS','Lunas','Gagal'],true)||!in_array($method,['Kasir','QRIS'],true))json_response(['ok'=>false,'message'=>'Data pembayaran tidak valid.'],400);
  $st=$pdo->prepare('UPDATE orders SET status_bayar=?,metode_bayar=?,kasir_id=COALESCE(kasir_id,?) WHERE id=?');$st->execute([$status,$method,$uid,$id]);log_activity('update_order_payment','Pesanan ID '.$id.' → '.$status.' ('.$method.')');json_response(['ok'=>true,'message'=>'pembayaran diperbarui.']);
 }elseif($action==='availability'){
  $id=(int)($_POST['id']??0);$st=$pdo->prepare('UPDATE menus SET is_available=IF(is_available=1,0,1) WHERE id=?');$st->execute([$id]);log_activity('toggle_menu','Kasir mengubah ketersediaan menu ID '.$id);json_response(['ok'=>true,'message'=>'ketersediaan menu diperbarui.']);
 }elseif($action==='create'){
  $name=clean_text((string)($_POST['nama_pelanggan']??''),100);$tableId=(int)($_POST['table_id']??0);$method=(string)($_POST['metode_bayar']??'Kasir');$paid=isset($_POST['paid'])&&(string)$_POST['paid']==='1';$note=clean_text((string)($_POST['catatan']??''),800);$rawItems=$_POST['items']??[];
  if($name==='')json_response(['ok'=>false,'message'=>'nama pelanggan wajib diisi.'],422);if(!in_array($method,['Kasir','QRIS'],true))json_response(['ok'=>false,'message'=>'metode bayar tidak valid.'],422);if($tableId){$ts=$pdo->prepare('SELECT id FROM `tables` WHERE id=?');$ts->execute([$tableId]);if(!$ts->fetchColumn())json_response(['ok'=>false,'message'=>'meja tidak valid.'],422);}
  $items=[];foreach((array)$rawItems as $row){$mid=(int)($row['menu_id']??0);$qty=(int)($row['qty']??0);if($mid>0&&$qty>0){if($qty>30)json_response(['ok'=>false,'message'=>'qty per item maksimal 30.'],422);$items[$mid]=min(30,($items[$mid]??0)+$qty);}}
  if(!$items)json_response(['ok'=>false,'message'=>'tambahkan minimal satu menu.'],422);
  $taxPercent=max(0,(float)get_setting('tax_percent','0'));$servicePercent=max(0,(float)get_setting('service_percent','0'));
  $pdo->beginTransaction();$subtotal=0;$lines=[];
  foreach($items as $mid=>$qty){$st=$pdo->prepare('SELECT id,nama,harga,stok,is_available FROM menus WHERE id=? FOR UPDATE');$st->execute([$mid]);$m=$st->fetch();if(!$m||!(int)$m['is_available']||(int)$m['stok']<$qty)throw new RuntimeException('stok atau status menu '.($m['nama']??'dipilih').' tidak mencukupi.');$line=(float)$m['harga']*$qty;$subtotal+=$line;$lines[]=['menu'=>$m,'qty'=>$qty,'line'=>$line];}
  $tax=round($subtotal*($taxPercent+$servicePercent)/100);$total=$subtotal+$tax;$code=random_order_code();$payStatus=$paid?'Lunas':($method==='QRIS'?'Menunggu QRIS':'Belum Bayar');
  $ins=$pdo->prepare('INSERT INTO orders (kode_pesanan,nama_pelanggan,table_id,subtotal,diskon,pajak,total,metode_bayar,status_bayar,status_pesanan,catatan,kasir_id) VALUES (?,?,?,?,0,?,?,?,?,?,?,?)');$ins->execute([$code,$name,$tableId?:null,$subtotal,$tax,$total,$method,$payStatus,'Diterima',$note,$uid]);$oid=(int)$pdo->lastInsertId();$oi=$pdo->prepare('INSERT INTO order_items (order_id,menu_id,nama_menu,qty,harga,opsi_json,catatan) VALUES (?,?,?,?,?,?,?)');$stock=$pdo->prepare('UPDATE menus SET stok=stok-? WHERE id=?');foreach($lines as $line){$oi->execute([$oid,(int)$line['menu']['id'],$line['menu']['nama'],(int)$line['qty'],(float)$line['menu']['harga'],json_encode([]),'']);$stock->execute([(int)$line['qty'],(int)$line['menu']['id']]);}$pdo->commit();log_activity('create_pos_order','Pesanan POS '.$code);json_response(['ok'=>true,'message'=>'pesanan POS berhasil dibuat.','kode_pesanan'=>$code,'id'=>$oid]);
 }else{json_response(['ok'=>false,'message'=>'Aksi tidak dikenal.'],400);}
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();if($e instanceof RuntimeException)json_response(['ok'=>false,'message'=>$e->getMessage()],422);json_response(['ok'=>false,'message'=>'aksi gagal disimpan. coba lagi.'],500);}
