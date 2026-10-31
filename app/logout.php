<?php
require_once __DIR__ . '/includes/auth.php';
logoutUser();
// Futa cache za browser kwa faragha (kompyuta/simu za kushirikiana)
header('Clear-Site-Data: "cache"');
header('Location: login.php');
exit;
