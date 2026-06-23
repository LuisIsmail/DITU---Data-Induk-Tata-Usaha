<?php
require_once 'config.php';
logActivity('logout', 'auth', 'Berhasil logout dari sistem');
session_destroy();
header('Location: login.php');
exit;
