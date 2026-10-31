<?php

// ---------------------------------------------------------------
// handleForms.php - receives every form submission for BOTH tables:
//   add_studio / update_studio / delete_studio   (parent)
//   add_game   / update_game   / delete_game     (child)
// It validates, calls the model function, then redirects with a message.
// ---------------------------------------------------------------
require_once __DIR__ . '/dbConfig.php';
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php');                       // only POST requests are accepted
}
requireLogin('../');                                // nobody can add/edit/delete without logging in
$user = currentUser();                              // the logged-in user doing this action
$uid  = (int) $user['user_id'];

if (!csrfIsValid($_POST['csrf'] ?? null)) {         // CSRF protection
    setFlash('error', 'Your session expired. Please try again.');
    redirect('../index.php');
}

$action = $_POST['action'] ?? '';
$id     = (int) ($_POST['id'] ?? 0);

// Which UNIQUE column caused a duplicate-entry error (MySQL error 1062)?
function duplicateMessage(PDOException $ex): ?array {
    if (($ex->errorInfo[1] ?? 0) !== 1062) {
        return null;
    }
    $msg = $ex->errorInfo[2] ?? '';
    if (str_contains($msg, 'email'))       return ['email', 'Another studio already uses this email.'];
    if (str_contains($msg, 'studio_name')) return ['studio_name', 'A studio with this name already exists.'];
    if (str_contains($msg, 'uq_studio_title')) return ['title', 'This studio already has a game with that title.'];
    return ['title', 'That record already exists.'];
}

try {
    switch ($action) {

        /* ===================== PARENT: studios ===================== */

        case 'add_studio':
            [$data, $errors] = validateStudio($_POST);
            if (!$errors) {
                try {
                    $newId = insertStudio($pdo, $data, $uid);
                    logActivity($pdo, $user, 'INSERT', 'studio', $newId, "Registered studio \"{$data['studio_name']}\"");
                    setFlash('success', "Studio \"{$data['studio_name']}\" was registered.");
                    redirect('../index.php');
                } catch (PDOException $ex) {
                    if (!$dup = duplicateMessage($ex)) { throw $ex; }
                    $errors[$dup[0]] = $dup[1];
                }
            }
            $_SESSION['old'] = $_POST;               // keep what the user typed
            $_SESSION['errors'] = $errors;
            redirect('../index.php');
            break;

        case 'update_studio':
            if (!getStudioById($pdo, $id)) {
                setFlash('error', 'That studio no longer exists.');
                redirect('../index.php');
            }
            [$data, $errors] = validateStudio($_POST);
            if (!$errors) {
                try {
                    updateStudio($pdo, $id, $data, $uid);
                    logActivity($pdo, $user, 'UPDATE', 'studio', $id, "Updated studio \"{$data['studio_name']}\"");
                    setFlash('success', "Studio #{$id} was updated.");
                    redirect('../index.php');
                } catch (PDOException $ex) {
                    if (!$dup = duplicateMessage($ex)) { throw $ex; }
                    $errors[$dup[0]] = $dup[1];
                }
            }
            $_SESSION['old'] = $_POST;
            $_SESSION['errors'] = $errors;
            redirect("../editStudio.php?id={$id}");
            break;

        case 'delete_studio':
            $studio = getStudioById($pdo, $id);
            if ($studio && deleteStudio($pdo, $id)) {
                $n = (int) $studio['games_count'];
                logActivity($pdo, $user, 'DELETE', 'studio', $id, "Deleted studio \"{$studio['studio_name']}\" and its {$n} game(s)");
                setFlash('success', "Studio \"{$studio['studio_name']}\" was deleted along with its {$n} game(s).");
            } else {
                setFlash('error', 'That studio was already deleted.');
            }
            redirect('../index.php');
            break;

        /* ====================== CHILD: games ====================== */

        case 'add_game':
            [$data, $errors] = validateGame($_POST);
            $studioId = (int) $data['studio_id'];
            if (!$errors && !getStudioById($pdo, $studioId)) {
                $errors['studio_id'] = 'That studio does not exist.';   // keeps the foreign key valid
            }
            if (!$errors) {
                try {
                    $newId = insertGame($pdo, $data, $uid);
                    logActivity($pdo, $user, 'INSERT', 'game', $newId, "Added game \"{$data['title']}\" to studio #{$studioId}");
                    setFlash('success', "Game \"{$data['title']}\" was added.");
                    redirect("../studio.php?id={$studioId}");
                } catch (PDOException $ex) {
                    if (!$dup = duplicateMessage($ex)) { throw $ex; }
                    $errors[$dup[0]] = $dup[1];
                }
            }
            $_SESSION['old'] = $_POST;
            $_SESSION['errors'] = $errors;
            redirect("../studio.php?id=" . (int) ($_POST['studio_id'] ?? 0));
            break;

        case 'update_game':
            $game = getGameById($pdo, $id);
            if (!$game) {
                setFlash('error', 'That game no longer exists.');
                redirect('../index.php');
            }
            [$data, $errors] = validateGame($_POST);
            if (!$errors && !getStudioById($pdo, (int) $data['studio_id'])) {
                $errors['studio_id'] = 'That studio does not exist.';
            }
            if (!$errors) {
                try {
                    updateGame($pdo, $id, $data, $uid);
                    logActivity($pdo, $user, 'UPDATE', 'game', $id, "Updated game \"{$data['title']}\"");
                    setFlash('success', "Game #{$id} was updated.");
                    redirect("../studio.php?id=" . (int) $data['studio_id']);
                } catch (PDOException $ex) {
                    if (!$dup = duplicateMessage($ex)) { throw $ex; }
                    $errors[$dup[0]] = $dup[1];
                }
            }
            $_SESSION['old'] = $_POST;
            $_SESSION['errors'] = $errors;
            redirect("../editGame.php?id={$id}");
            break;

        case 'delete_game':
            $game = getGameById($pdo, $id);
            if ($game && deleteGame($pdo, $id)) {
                logActivity($pdo, $user, 'DELETE', 'game', $id, "Deleted game \"{$game['title']}\" from \"{$game['studio_name']}\"");
                setFlash('success', "Game \"{$game['title']}\" was deleted.");
                redirect("../studio.php?id=" . (int) $game['studio_id']);
            }
            setFlash('error', 'That game was already deleted.');
            redirect('../index.php');
            break;

        default:
            setFlash('error', 'Unknown action.');
            redirect('../index.php');
    }
} catch (PDOException $ex) {
    error_log($ex->getMessage());                    // details stay in the server log
    setFlash('error', 'Something went wrong with the database. Please try again.');
    redirect('../index.php');
}
