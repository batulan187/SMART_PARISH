<?php
declare(strict_types=1);
require '../config/auth.php';
if ($u = current_user()) {
    if ($u['role'] === 'admin') redirect('dashboard.php');
    if ($u['role'] === 'parishioner') redirect('../parishioner/dashboard.php');
}
redirect('../parishioner/login.php');
