<?php
declare(strict_types=1);
require '../config/db.php';
require '../config/auth.php';

// A signed-in user always goes to the dashboard allowed by the stored database role.
if ($u = current_user()) {
    if ($u['role'] === 'admin') redirect('../admin/dashboard.php');
    if ($u['role'] === 'parishioner') redirect('dashboard.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter a valid email address and password.';
    } else {
        try {
            // Do NOT accept a role from the form. The role comes only from MySQL.
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $error = 'Invalid email address or password.';
            } elseif (($user['status'] ?? 'active') !== 'active') {
                $error = 'This account is inactive. Please contact the parish office.';
            } elseif (!in_array($user['role'], ['admin', 'parishioner'], true)) {
                $error = 'This account does not have a valid access role.';
            } else {
                login_user($user);
                log_activity($pdo, (int)$user['id'], ucfirst($user['role']).' login');

                if ($user['role'] === 'admin') {
                    redirect('../admin/dashboard.php');
                }
                redirect('dashboard.php');
            }
        } catch (PDOException $e) {
            error_log('Unified login error: '.$e->getMessage());
            $error = 'Unable to sign in right now. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login | Smart Parish</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">
<main class="card auth-card">
  <div class="brand" style="justify-content:center"><span class="brand-mark">✝</span><span>Smart Parish</span></div>
  <div style="text-align:center">
    <div class="eyebrow">Secure account access</div>
    <h1>Welcome back.</h1>
    <p class="muted">Use your account credentials. Smart Parish will automatically open the correct dashboard for your account.</p>
  </div>
  <?php if ($error): ?><div class="notice error"><?=e($error)?></div><?php endif; ?>
  <form method="post" class="grid">
    <?=csrf_field()?>
    <label class="field"><span>Email address</span><input type="email" name="email" value="<?=e($email)?>" required autocomplete="email" placeholder="you@example.com"></label>
    <label class="field"><span>Password</span><input type="password" name="password" required autocomplete="current-password" placeholder="Password"></label>
    <button class="btn" type="submit">Login</button>
  </form>
  <div class="auth-divider"><span>New to Smart Parish?</span></div>
  <a class="btn light auth-create" href="register.php">Create Account</a>
  <p class="muted auth-note">Public registration creates a parishioner account only. Administrator access is assigned by the parish.</p>
  <div style="text-align:center;margin-top:16px"><a class="muted" href="../index.php">← Back to home</a></div>
</main>
</body>
</html>
