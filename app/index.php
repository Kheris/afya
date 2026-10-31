<?php
require_once __DIR__ . '/includes/auth.php';

if (currentUserId()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
