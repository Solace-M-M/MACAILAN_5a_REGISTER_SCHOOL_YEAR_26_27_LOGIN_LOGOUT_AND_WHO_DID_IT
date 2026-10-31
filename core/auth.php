<?php

// ---------------------------------------------------------------
// auth.php - who is logged in? (uses helper functions from models.php)
// The logged-in user is kept in $_SESSION['user'].
// ---------------------------------------------------------------

function isLoggedIn(): bool {
    return !empty($_SESSION['user']['user_id']);
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

// Call at the top of every protected page: not logged in => go to the login page
function requireLogin(string $pathToRoot = ''): void {
    if (!isLoggedIn()) {
        setFlash('error', 'Please log in to continue.');
        redirect($pathToRoot . 'login.php');
    }
}

// Login/register pages: an already logged-in user goes straight to the homepage
function redirectIfLoggedIn(string $pathToRoot = ''): void {
    if (isLoggedIn()) {
        redirect($pathToRoot . 'index.php');
    }
}

// Remember the user in the session (only safe fields, never the password hash)
function loginUser(array $user): void {
    session_regenerate_id(true);    // new session id after login (prevents session fixation)
    $_SESSION['user'] = [
        'user_id'    => (int) $user['user_id'],
        'username'   => $user['username'],
        'first_name' => $user['first_name'],
        'last_name'  => $user['last_name'],
    ];
}

// Destroy the session completely, then start a clean one to carry a message
function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $c = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $c['path'], $c['domain'], $c['secure'], $c['httponly']);
    }
    session_destroy();
    session_start();
    session_regenerate_id(true);
}
