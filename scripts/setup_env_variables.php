<?php
/**
 * This script creates an .env file for database credentials
 * and modifies the dbconnection.php file to use environment variables
 */

// Check if already running with environment variables
if (file_exists(__DIR__ . '/../.env')) {
    echo "Environment file already exists.\n";
    exit;
}

// Get the existing database connection details
require_once __DIR__ . '/../configs/dbconnection.php';

// Create .env file
$envContent = <<<EOT
# Database Configuration
DB_HOST=$servername
DB_USERNAME=$username
DB_PASSWORD=$password
DB_NAME=$database

# Security Settings
ENCRYPTION_KEY=

EOT;

// Generate a random encryption key
$encryptionKey = bin2hex(random_bytes(16));
$envContent = str_replace('ENCRYPTION_KEY=', "ENCRYPTION_KEY=$encryptionKey", $envContent);

// Write the .env file
file_put_contents(__DIR__ . '/../.env', $envContent);
echo "Created .env file with database credentials.\n";

// Create the updated dbconnection.php file that uses environment variables
$newDbContent = <<<'EOT'
<?php
/**
 * Database Connection using Environment Variables
 * 
 * This file establishes a database connection using credentials from environment variables
 * for improved security.
 */

// Load environment variables from .env file if php-dotenv is available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Get database credentials from environment variables
$servername = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
$username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? '';
$database = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'ems';

// Enable error reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Define log directory and file
$logDir = __DIR__ . '/logs';
$logFile = $logDir . '/error.log';

try {
    // Ensure the logs directory exists
    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true);
    }

    // Create database connection
    $conn = new mysqli($servername, $username, $password, $database);
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // Log error to file
    $timestamp = date('Y-m-d H:i:s');
    $errorMessage = "[$timestamp] DB Connection Error: " . $e->getMessage() . "\n";
    error_log($errorMessage, 3, $logFile);

    // Return generic JSON error message to frontend
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error",
        "message" => "Internal server error. Please try again later."
    ]);
    exit;
}

/**
 * Function to sanitize database inputs
 * 
 * @param string $input Input to sanitize
 * @return string Sanitized input
 */
function sanitizeDbInput($input) {
    global $conn;
    if (is_string($input)) {
        return $conn->real_escape_string(trim($input));
    }
    return $input;
}
EOT;

// Create a backup of the original file
copy(__DIR__ . '/../configs/dbconnection.php', __DIR__ . '/../configs/dbconnection.php.bak');
echo "Created backup of original dbconnection.php file.\n";

// Write the new file
file_put_contents(__DIR__ . '/../configs/dbconnection.php.new', $newDbContent);
echo "Created new dbconnection.php file using environment variables.\n";
echo "To switch to the new file, run:\n";
echo "1. Install PHP dotenv package: composer require vlucas/phpdotenv\n";
echo "2. Rename configs/dbconnection.php.new to configs/dbconnection.php\n";

echo "\nIMPORTANT: Ensure your .env file is protected and not accessible via the web server.\n";
?>
