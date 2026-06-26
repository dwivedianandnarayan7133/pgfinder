<?php
/**
 * Database Configuration
 * Student Accommodation Platform
 */

if (!function_exists('get_db_env')) {
    function get_db_env(string $key, string $default): string {
        if (getenv($key) !== false && getenv($key) !== '') {
            return getenv($key);
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        return $default;
    }
}

define('DB_HOST', get_db_env('DB_HOST', 'localhost'));
define('DB_NAME', get_db_env('DB_NAME', 'student_accommodation'));
define('DB_USER', get_db_env('DB_USER', 'root'));
define('DB_PASS', get_db_env('DB_PASS', 'Shiv@241'));
define('DB_CHARSET', get_db_env('DB_CHARSET', 'utf8mb4'));
define('BASE_URL', get_db_env('BASE_URL', '/student-accommodation/'));

/**
 * Returns a PDO database connection (singleton).
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $pdo->exec("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

/**
 * Custom Session Save Handler to store session data in MySQL.
 * Essential for serverless deployments (Vercel) where local files are not shared/persistent.
 */
class DatabaseSessionHandler implements SessionHandlerInterface {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function open($savePath, $sessionName): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read($id): string {
        try {
            $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['data'] : '';
        } catch (Exception $e) {
            return '';
        }
    }

    public function write($id, $data): bool {
        try {
            $timestamp = time();
            $stmt = $this->pdo->prepare("REPLACE INTO sessions (id, data, timestamp) VALUES (:id, :data, :timestamp)");
            return $stmt->execute([
                ':id'        => $id,
                ':data'      => $data,
                ':timestamp' => $timestamp
            ]);
        } catch (Exception $e) {
            error_log("DatabaseSessionHandler::write error: " . $e->getMessage());
            return false;
        }
    }

    public function destroy($id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function gc($maxLifetime): int {
        try {
            $old = time() - $maxLifetime;
            $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE timestamp < :old");
            $stmt->execute([':old' => $old]);
            return $stmt->rowCount();
        } catch (Exception $e) {
            return 0;
        }
    }
}

/**
 * Session helper — start session if not already started.
 */
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        try {
            $pdo = getDB();
            $handler = new DatabaseSessionHandler($pdo);
            session_set_save_handler($handler, true);
        } catch (Exception $e) {
            // Fallback to PHP's default file-based session handler if database is not ready
        }
        session_start();
        // Force session data write before PHP destroys the PDO connection object during shutdown
        register_shutdown_function('session_write_close');
    }
}

/**
 * Check if user is logged in.
 */
function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']);
}

/**
 * Get current logged-in user ID.
 */
function currentUserId(): ?int {
    startSession();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role.
 */
function currentUserRole(): string {
    startSession();
    return $_SESSION['user_role'] ?? 'student';
}

/**
 * Check if current user is a PG owner.
 */
function isOwner(): bool {
    return currentUserRole() === 'owner';
}

/**
 * Check if current user is admin.
 */
function isAdmin(): bool {
    return currentUserRole() === 'admin';
}

/**
 * Require login — redirect if not logged in.
 */
function requireLogin(string $redirect = 'login.php'): void {
    if (!isLoggedIn()) { redirect(BASE_URL . $redirect); }
}

/**
 * Require owner role.
 */
function requireOwner(): void {
    requireLogin('owner-login.php');
    if (!isOwner() && !isAdmin()) { redirect(BASE_URL . 'index.php'); }
}

/**
 * Require admin role.
 */
function requireAdmin(): void {
    startSession();
    if (!isset($_SESSION['admin_id'])) { redirect(BASE_URL . 'admin/login.php'); }
}

/**
 * Redirect helper.
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Sanitise output.
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Logs credentials to a local file and prepares session payload to display them on screen.
 */
function sendRegistrationNotification(string $name, string $email, string $phone, string $password, string $role): void {
    startSession();
    
    $subject = "Welcome to PGFinder - Your Account Details";
    $smsText = "Hello $name! Your PGFinder account is created. Username: $email, Password: $password. Login here: http://localhost/student-accommodation/";
    $emailText = "Hello $name,\n\n"
               . "Welcome to PGFinder! Your account has been registered successfully as a " . strtoupper($role) . ".\n\n"
               . "Here are your login credentials:\n"
               . "- Username / Email: $email\n"
               . "- Password: $password\n\n"
               . "You can log in at: http://localhost/student-accommodation/login.php\n\n"
               . "Best regards,\nThe PGFinder Team";

    // Log details to a local file (notifications_log.txt in project root) for backup
    $logFile = __DIR__ . '/../notifications_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logContent = "========================================================\n"
                . "TIMESTAMP: $timestamp\n"
                . "ROLE: $role\n"
                . "--------------------------------------------------------\n"
                . "✉️ EMAIL\n"
                . "To: $email\n"
                . "Subject: $subject\n"
                . "Body:\n$emailText\n"
                . "--------------------------------------------------------\n"
                . "📱 SMS\n"
                . "To: " . (empty($phone) ? "[No Phone Provided]" : $phone) . "\n"
                . "Message: $smsText\n"
                . "========================================================\n\n";
                
    // On Vercel, the filesystem is read-only, so we fallback to /tmp
    $dir = dirname($logFile);
    if (is_writable($dir) || (file_exists($logFile) && is_writable($logFile))) {
        @file_put_contents($logFile, $logContent, FILE_APPEND);
    } else {
        $tmpLog = sys_get_temp_dir() . '/notifications_log.txt';
        @file_put_contents($tmpLog, $logContent, FILE_APPEND);
    }
    
    $_SESSION['just_registered'] = [
        'name' => $name,
        'email' => $email,
        'phone' => empty($phone) ? null : $phone,
        'password' => $password,
        'role' => $role
    ];
}
