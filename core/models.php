<?php

// ---------------------------------------------------------------
// models.php - database functions for BOTH tables, validation and helpers
//   PARENT: studios  (one studio ...)
//   CHILD : games    (... has many games, linked by games.studio_id)
// ---------------------------------------------------------------

// Allowed values for the drop-down lists (also used to validate input)
const STUDIO_TYPES  = ['Indie', 'Mobile', 'AAA', 'VR / AR', 'Educational', 'Outsourcing'];
const GENRES        = ['Action', 'Adventure', 'RPG', 'Strategy', 'Puzzle', 'Simulation', 'Platformer', 'Horror', 'Sports', 'Racing'];
const PLATFORMS     = ['PC', 'Mobile', 'PlayStation', 'Xbox', 'Nintendo Switch', 'Multi-platform'];
const GAME_ENGINES  = ['Unity', 'Unreal Engine', 'Godot', 'GameMaker', 'Custom Engine', 'Other'];
const GAME_STATUSES = ['In Development', 'Alpha', 'Beta', 'Released', 'Cancelled'];

/* ------------------------- helpers ------------------------- */

function e($value): string {                       // escape text for HTML (prevents XSS)
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
function redirect(string $url): void {             // go to another page and stop
    header("Location: {$url}");
    exit;
}
function setFlash(string $type, string $message): void {   // one-time message after an action
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
function pullFlash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
function csrfToken(): string {                     // proves a form came from our own page
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrfIsValid(?string $token): bool {
    return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/* ------------------------ validation ------------------------ */

// Returns [cleanData, errors] for the PARENT form (studio)
function validateStudio(array $in): array {
    $errors = [];
    $d = [
        'studio_name'  => trim($in['studio_name'] ?? ''),
        'founder_name' => trim($in['founder_name'] ?? ''),
        'email'        => trim($in['email'] ?? ''),
        'country'      => trim($in['country'] ?? ''),
        'year_founded' => trim((string) ($in['year_founded'] ?? '')),
        'studio_type'  => $in['studio_type'] ?? '',
        'website'      => trim($in['website'] ?? ''),
    ];

    if ($d['studio_name'] === '' || mb_strlen($d['studio_name']) > 100) {
        $errors['studio_name'] = 'Studio name is required (max 100 characters).';
    }
    if ($d['founder_name'] === '' || mb_strlen($d['founder_name']) > 100
        || !preg_match("/^\p{L}[\p{L} .'-]*$/u", $d['founder_name'])) {
        $errors['founder_name'] = 'Founder name is required (letters only, max 100 characters).';
    }
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($d['email']) > 100) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if ($d['country'] === '' || mb_strlen($d['country']) > 60 || !preg_match("/^\p{L}[\p{L} .'-]*$/u", $d['country'])) {
        $errors['country'] = 'Country is required (letters only, max 60 characters).';
    }
    $year = filter_var($d['year_founded'], FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1970, 'max_range' => (int) date('Y')]]);
    if ($year === false) {
        $errors['year_founded'] = 'Year founded must be between 1970 and ' . date('Y') . '.';
    } else {
        $d['year_founded'] = $year;
    }
    if (!in_array($d['studio_type'], STUDIO_TYPES, true)) {
        $errors['studio_type'] = 'Choose a studio type from the list.';
    }
    if ($d['website'] !== '') {
        $scheme = strtolower((string) parse_url($d['website'], PHP_URL_SCHEME));
        if (!filter_var($d['website'], FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)
            || mb_strlen($d['website']) > 255) {
            $errors['website'] = 'Website must be a full http:// or https:// link.';
        }
    }
    $d['website'] = $d['website'] === '' ? null : $d['website'];   // empty optional field -> NULL
    return [$d, $errors];
}

// Returns [cleanData, errors] for the CHILD form (game)
function validateGame(array $in): array {
    $errors = [];
    $d = [
        'studio_id'      => trim((string) ($in['studio_id'] ?? '')),
        'title'          => trim($in['title'] ?? ''),
        'genre'          => $in['genre'] ?? '',
        'platform'       => $in['platform'] ?? '',
        'game_engine'    => $in['game_engine'] ?? '',
        'release_status' => $in['release_status'] ?? 'In Development',
        'price'          => trim((string) ($in['price'] ?? '')),
        'release_date'   => trim($in['release_date'] ?? ''),
    ];

    $studioId = filter_var($d['studio_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($studioId === false) {
        $errors['studio_id'] = 'Choose a studio.';
    } else {
        $d['studio_id'] = $studioId;
    }
    if ($d['title'] === '' || mb_strlen($d['title']) > 100) {
        $errors['title'] = 'Game title is required (max 100 characters).';
    }
    if (!in_array($d['genre'], GENRES, true))               { $errors['genre'] = 'Choose a genre.'; }
    if (!in_array($d['platform'], PLATFORMS, true))         { $errors['platform'] = 'Choose a platform.'; }
    if (!in_array($d['game_engine'], GAME_ENGINES, true))   { $errors['game_engine'] = 'Choose a game engine.'; }
    if (!in_array($d['release_status'], GAME_STATUSES, true)) { $errors['release_status'] = 'Choose a status.'; }

    if (!is_numeric($d['price']) || $d['price'] < 0 || $d['price'] > 9999.99) {
        $errors['price'] = 'Price must be a number from 0 to 9999.99.';
    } else {
        $d['price'] = number_format((float) $d['price'], 2, '.', '');
    }
    if ($d['release_date'] !== '') {
        $date = DateTime::createFromFormat('Y-m-d', $d['release_date']);
        if (!$date || $date->format('Y-m-d') !== $d['release_date']) {
            $errors['release_date'] = 'Release date must be a real date.';
        }
    }
    $d['release_date'] = $d['release_date'] === '' ? null : $d['release_date'];
    return [$d, $errors];
}

// Returns [cleanData, errors] for the REGISTRATION form (user)
function validateRegistration(array $in): array {
    $errors = [];
    $d = [
        'first_name' => trim($in['first_name'] ?? ''),
        'last_name'  => trim($in['last_name'] ?? ''),
        'username'   => trim($in['username'] ?? ''),
        'email'      => trim($in['email'] ?? ''),
        'address'    => trim($in['address'] ?? ''),
        'age'        => trim((string) ($in['age'] ?? '')),
    ];
    $password = (string) ($in['password'] ?? '');
    $confirm  = (string) ($in['password_confirm'] ?? '');

    foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $f => $label) {
        if ($d[$f] === '' || mb_strlen($d[$f]) > 50 || !preg_match("/^\p{L}[\p{L} .'-]*$/u", $d[$f])) {
            $errors[$f] = "{$label} is required (letters only, max 50 characters).";
        }
    }
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $d['username'])) {
        $errors['username'] = 'Username must be 3-30 characters: letters, numbers and underscore only.';
    }
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($d['email']) > 100) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if ($d['address'] === '' || mb_strlen($d['address']) > 255) {
        $errors['address'] = 'Address is required (max 255 characters).';
    }
    $age = filter_var($d['age'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 13, 'max_range' => 120]]);
    if ($age === false) {
        $errors['age'] = 'Age must be a whole number from 13 to 120.';
    } else {
        $d['age'] = $age;
    }
    // bcrypt only reads the first 72 bytes, so longer passwords are refused
    if (strlen($password) < 8 || strlen($password) > 72) {
        $errors['password'] = 'Password must be 8 to 72 characters long.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'Password must contain at least one letter and one number.';
    }
    if ($password !== $confirm) {
        $errors['password_confirm'] = 'The two passwords do not match.';
    }
    return [$d, $password, $errors];
}

/* ===================== USER module ===================== */

// CREATE: save a new user; the password is stored only as a bcrypt hash
function insertUser(PDO $pdo, array $d, string $password): int {
    $stmt = $pdo->prepare(
        "INSERT INTO users (first_name, last_name, username, email, password_hash, address, age)
         VALUES (:first_name, :last_name, :username, :email, :password_hash, :address, :age)"
    );
    $stmt->execute($d + ['password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
    return (int) $pdo->lastInsertId();
}

// READ: find a user by username OR email (used by the login form)
function findUserByLogin(PDO $pdo, string $login): ?array {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u OR email = :e LIMIT 1");
    $stmt->execute([':u' => $login, ':e' => $login]);
    return $stmt->fetch() ?: null;
}

function touchLastLogin(PDO $pdo, int $userId): void {
    $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :id")->execute([':id' => $userId]);
}

/* ================= ACTIVITY LOG (who did what) ================= */

// Writes one row: which user did which action on which record
function logActivity(PDO $pdo, ?array $user, string $action, string $entity, ?int $entityId, string $details): void {
    $stmt = $pdo->prepare(
        "INSERT INTO activity_log (user_id, username, action, entity, entity_id, details)
         VALUES (:user_id, :username, :action, :entity, :entity_id, :details)"
    );
    $stmt->execute([
        ':user_id'   => $user['user_id'] ?? null,
        ':username'  => $user['username'] ?? 'unknown',
        ':action'    => $action,
        ':entity'    => $entity,
        ':entity_id' => $entityId,
        ':details'   => mb_substr($details, 0, 255),
    ]);
}

function getRecentActivity(PDO $pdo, int $limit = 100): array {
    $stmt = $pdo->prepare("SELECT * FROM activity_log ORDER BY created_at DESC, log_id DESC LIMIT :n");
    $stmt->bindValue(':n', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/* ===================== PARENT: studios (CRUD) ===================== */
// Every query below also reads WHO added / last updated the row (LEFT JOIN users).
// A subquery counts the games so no GROUP BY is needed.

const STUDIO_SELECT =
    "SELECT s.*,
            (SELECT COUNT(*) FROM games g WHERE g.studio_id = s.studio_id) AS games_count,
            ua.username AS added_by_name,
            uu.username AS updated_by_name
     FROM studios s
     LEFT JOIN users ua ON ua.user_id = s.added_by
     LEFT JOIN users uu ON uu.user_id = s.last_updated_by";

// CREATE: $userId is stored as added_by and last_updated_by
function insertStudio(PDO $pdo, array $d, int $userId): int {
    $stmt = $pdo->prepare(
        "INSERT INTO studios (studio_name, founder_name, email, country, year_founded, studio_type, website,
                              added_by, last_updated_by, last_updated)
         VALUES (:studio_name, :founder_name, :email, :country, :year_founded, :studio_type, :website,
                 :uid1, :uid2, NOW())"
    );
    $stmt->execute($d + ['uid1' => $userId, 'uid2' => $userId]);
    return (int) $pdo->lastInsertId();
}

// READ (all studios)
function getStudios(PDO $pdo, string $search = ''): array {
    $like = '%' . addcslashes($search, '%_\\') . '%';
    $stmt = $pdo->prepare(
        STUDIO_SELECT . "
         WHERE s.studio_name LIKE :q1 OR s.founder_name LIKE :q2 OR s.country LIKE :q3
         ORDER BY s.date_added DESC, s.studio_id DESC"
    );
    $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
    return $stmt->fetchAll();
}

// READ (one studio) - null when it does not exist
function getStudioById(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare(STUDIO_SELECT . " WHERE s.studio_id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

// READ (drop-down in the game form)
function getStudioOptions(PDO $pdo): array {
    return $pdo->query("SELECT studio_id, studio_name FROM studios ORDER BY studio_name")->fetchAll();
}

// UPDATE: stamps last_updated_by and last_updated
function updateStudio(PDO $pdo, int $id, array $d, int $userId): bool {
    $stmt = $pdo->prepare(
        "UPDATE studios SET studio_name = :studio_name, founder_name = :founder_name, email = :email,
                country = :country, year_founded = :year_founded, studio_type = :studio_type, website = :website,
                last_updated_by = :uid, last_updated = NOW()
         WHERE studio_id = :studio_id"
    );
    return $stmt->execute(array_merge($d, ['uid' => $userId, 'studio_id' => $id]));
}

// DELETE: ON DELETE CASCADE removes the studio's games too
function deleteStudio(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("DELETE FROM studios WHERE studio_id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->rowCount() > 0;
}

/* ====================== CHILD: games (CRUD) ====================== */

const GAME_SELECT =
    "SELECT g.*, s.studio_name,
            ua.username AS added_by_name,
            uu.username AS updated_by_name
     FROM games g
     INNER JOIN studios s ON s.studio_id = g.studio_id
     LEFT JOIN users ua ON ua.user_id = g.added_by
     LEFT JOIN users uu ON uu.user_id = g.last_updated_by";

// CREATE
function insertGame(PDO $pdo, array $d, int $userId): int {
    $stmt = $pdo->prepare(
        "INSERT INTO games (studio_id, title, genre, platform, game_engine, release_status, price, release_date,
                            added_by, last_updated_by, last_updated)
         VALUES (:studio_id, :title, :genre, :platform, :game_engine, :release_status, :price, :release_date,
                 :uid1, :uid2, NOW())"
    );
    $stmt->execute($d + ['uid1' => $userId, 'uid2' => $userId]);
    return (int) $pdo->lastInsertId();
}

// READ (many): the games of ONE studio
function getGamesByStudio(PDO $pdo, int $studioId): array {
    $stmt = $pdo->prepare(GAME_SELECT . " WHERE g.studio_id = :id ORDER BY g.date_added DESC, g.game_id DESC");
    $stmt->execute([':id' => $studioId]);
    return $stmt->fetchAll();
}

// READ (one game with its studio name)
function getGameById(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare(GAME_SELECT . " WHERE g.game_id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

// UPDATE: stamps last_updated_by and last_updated (changing studio_id moves the game)
function updateGame(PDO $pdo, int $id, array $d, int $userId): bool {
    $stmt = $pdo->prepare(
        "UPDATE games SET studio_id = :studio_id, title = :title, genre = :genre, platform = :platform,
                game_engine = :game_engine, release_status = :release_status, price = :price,
                release_date = :release_date, last_updated_by = :uid, last_updated = NOW()
         WHERE game_id = :game_id"
    );
    return $stmt->execute(array_merge($d, ['uid' => $userId, 'game_id' => $id]));
}

// DELETE
function deleteGame(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("DELETE FROM games WHERE game_id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->rowCount() > 0;
}

?>
