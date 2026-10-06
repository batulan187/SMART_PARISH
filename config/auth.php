<?php
declare(strict_types=1);
require_once __DIR__.'/helpers.php';
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function login_user(array $u): void { session_regenerate_id(true); $_SESSION['user']=['id'=>(int)$u['id'],'full_name'=>$u['full_name'],'email'=>$u['email'],'role'=>$u['role'],'status'=>$u['status']??'active','profile_image'=>$u['profile_image']??null]; }
function require_role(string $role, string $login): void { if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== $role) redirect($login); }
function logout_user(): void { $_SESSION=[]; if (ini_get('session.use_cookies')) { $p=session_get_cookie_params(); setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']); } session_destroy(); }
