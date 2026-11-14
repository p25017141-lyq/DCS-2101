<?php require __DIR__.'/common.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') showError('Use the logout button.','index.php',405);
csrfCheck(); $_SESSION=[]; session_regenerate_id(true); session_destroy(); redirectTo('login.php');
