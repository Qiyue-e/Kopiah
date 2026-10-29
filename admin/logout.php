<?php
require_once __DIR__.'/../config/auth.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Metode tidak didukung.');}
csrf_check();if(current_user())log_activity('logout','Logout dari panel');$_SESSION=[];if(ini_get('session.use_cookies')){$p=session_get_cookie_params();setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']);}session_destroy();header('Location: '.BASE_URL.'/admin/login.php');exit;
