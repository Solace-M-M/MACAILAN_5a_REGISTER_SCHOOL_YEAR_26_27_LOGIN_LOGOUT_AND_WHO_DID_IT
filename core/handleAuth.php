<?php

// ---------------------------------------------------------------
// handleAuth.php - register, login and logout form submissions
// ---------------------------------------------------------------
require_once __DIR__ . '/dbConfig.php';
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php');
}
if (!csrfIsValid($_POST['csrf'] ?? null)) {
    setFlash('error', 'Your session expired. Please try again.');
    redirect('../login.php');
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        // ---------- REGISTER ----------
        case 'register':
            [$data, $password, $errors] = validateRegistration($_POST);
            if (!$errors) {
                try {
                    $newId = insertUser($pdo, $data, $password);
                    logActivity($pdo, ['user_id' => $newId, 'username' => $data['username']],
                                'REGISTER', 'user', $newId, "New account created for {$data['username']}");
                    setFlash('success', 'Account created. You can log in now.');
                    redirect('../login.php');
                } catch (PDOException $ex) {
                    if (($ex->errorInfo[1] ?? 0) !== 1062) { throw $ex; }
                    // 1062 = duplicate entry on a UNIQUE column
                    if (str_contains($ex->errorInfo[2] ?? '', 'email')) {
                        $errors['email'] = 'This email is already registered.';
                    } else {
                        $errors['username'] = 'This username is already taken.';
                    }
                }
            }
            // keep what was typed (never the passwords) and show the errors
            unset($_POST['password'], $_POST['password_confirm']);
            $_SESSION['old'] = $_POST;
            $_SESSION['errors'] = $errors;
            redirect('../register.php');
            break;

        // ---------- LOGIN ----------
        case 'login':
            // Throttle: 5 wrong attempts => wait 60 seconds
            $fail = $_SESSION['login_fail'] ?? ['count' => 0, 'until' => 0];
            if ($fail['count'] >= 5 && time() < $fail['until']) {
                $wait = $fail['until'] - time();
                setFlash('error', "Too many failed attempts. Try again in {$wait} second(s).");
                redirect('../login.php');
            }

            $login    = trim($_POST['login'] ?? '');
            $password = (string) ($_POST['password'] ?? '');
            $user     = $login !== '' ? findUserByLogin($pdo, $login) : null;

            // Always run password_verify (against a dummy hash if the user is unknown)
            // so the response time does not reveal whether the account exists
            $hash = $user['password_hash'] ?? '$2y$10$uuZWTIHoz1zM.pJte88TEu.x8.VcQ9oS78gfJgYNI6/eLqmYr39se';
            $ok   = password_verify($password, $hash) && $user;

            if ($ok) {
                unset($_SESSION['login_fail']);
                if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                    $pdo->prepare("UPDATE users SET password_hash = :h WHERE user_id = :id")
                        ->execute([':h' => password_hash($password, PASSWORD_DEFAULT), ':id' => $user['user_id']]);
                }
                touchLastLogin($pdo, (int) $user['user_id']);
                loginUser($user);
                logActivity($pdo, currentUser(), 'LOGIN', 'user', (int) $user['user_id'], "{$user['username']} logged in");
                setFlash('success', "Welcome back, {$user['first_name']}!");
                redirect('../index.php');
            }

            $fail['count']++;
            $fail['until'] = time() + 60;
            $_SESSION['login_fail'] = $fail;
            $_SESSION['old'] = ['login' => $login];
            setFlash('error', 'Incorrect username/email or password.');   // same message for both cases
            redirect('../login.php');
            break;

        // ---------- LOGOUT ----------
        case 'logout':
            $user = currentUser();
            if ($user) {
                logActivity($pdo, $user, 'LOGOUT', 'user', $user['user_id'], "{$user['username']} logged out");
            }
            logoutUser();
            setFlash('success', 'You have been logged out.');
            redirect('../login.php');
            break;

        default:
            setFlash('error', 'Unknown action.');
            redirect('../login.php');
    }
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    setFlash('error', 'Something went wrong with the database. Please try again.');
    redirect('../login.php');
}
