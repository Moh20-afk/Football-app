<?php
// Load configuration
$config = require_once 'config.php';
$db_config = $config['database'];

// Hashing function (equivalent to Python's hashlib.sha256)
function hash_password($password) {
    return hash('sha256', $password);
}

// Database connection function
function get_db_connection() {
    global $db_config;
    
    try {
        $conn = new mysqli(
            $db_config['host'],
            $db_config['user'],
            $db_config['password'],
            $db_config['database'],
            $db_config['port']
        );
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        throw $e;
    }
}

// Check if username already exists
function username_exists($username) {
    try {
        $conn = get_db_connection();
        
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_info WHERE username = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $username);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $stmt->close();
        $conn->close();
        
        return $row['count'] > 0;
    } catch (Exception $e) {
        error_log("Username check error: " . $e->getMessage());
        throw $e;
    }
}

// Register function using stored procedure
function register_user($username, $password_hash) {
    try {
        // Check if username already exists
        if (username_exists($username)) {
            throw new Exception("Username already exists. Please choose a different username.");
        }
        
        $conn = get_db_connection();
        
        // Call the stored procedure user_proc
        $stmt = $conn->prepare("CALL user_proc(?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("ss", $username, $password_hash);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        $conn->close();
        
        return true;
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        throw $e;
    }
}

// Login function using stored procedure
function login_user($username, $password_hash) {
    try {
        $conn = get_db_connection();
        
        // Call the stored procedure user_login
        $stmt = $conn->prepare("CALL user_login(?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("ss", $username, $password_hash);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $user_data = $result->fetch_all(MYSQLI_ASSOC);
        
        $stmt->close();
        $conn->close();
        
        return $user_data;
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        throw $e;
    }
}

// Get the request path
$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = ltrim($path, '/');

// Default to login if no path
if (empty($path)) {
    $path = 'login';
}

// Route handling
if ($path === 'signup') {
    if ($request_method === 'POST') {
        // Handle signup POST request
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo "Missing username or password";
            exit;
        }
        
        // Validate username and password
        if (strlen($username) < 3 || strlen($username) > 20) {
            // Redirect back to signup page with error message
            header('Location: /signup?error=Username must be between 3 and 20 characters.');
            exit;
        }
        
        if (strlen($password) < 6) {
            // Redirect back to signup page with error message
            header('Location: /signup?error=Password must be at least 6 characters long.');
            exit;
        }
        
        // Check for valid username characters (alphanumeric and underscore only)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            // Redirect back to signup page with error message
            header('Location: /signup?error=Username can only contain letters, numbers, and underscores.');
            exit;
        }
        
        $password_hash = hash_password($password);
        
        try {
            register_user($username, $password_hash);
            // Redirect to login page after successful signup
            header('Location: /login?message=Signup successful! Please login with your new account.');
            exit;
        } catch (Exception $e) {
            // Redirect back to signup page with error message
            header('Location: /signup?error=' . urlencode($e->getMessage()));
            exit;
        }
    } else {
        // Handle signup GET request - serve the signup form
        header('Content-Type: text/html');
        
        // Check for error messages
        $error = $_GET['error'] ?? '';
        if (!empty($error)) {
            echo '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px; text-align: center;">
                <strong>Error:</strong> ' . htmlspecialchars($error) . '
            </div>';
        }
        
        include '../Front-End/Signup.html';
    }
} elseif ($path === 'dashboard') {
    // Handle dashboard page
    header('Content-Type: text/html');
    include '../Front-End/dashboard.html';
} elseif ($path === 'login') {
    if ($request_method === 'POST') {
        // Handle login POST request
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo "Missing username or password";
            exit;
        }
        
        $password_hash = hash_password($password);
        
        try {
            $user_data = login_user($username, $password_hash);
            
            if (!empty($user_data)) {
                // Redirect to dashboard after successful login
                header('Location: /dashboard?username=' . urlencode($user_data[0]['username'] ?? $username));
                exit;
            } else {
                // Redirect back to login page with error message
                header('Location: /login?error=Invalid username or password. Please check your credentials and try again.');
                exit;
            }
        } catch (Exception $e) {
            // Redirect back to login page with database error message
            header('Location: /login?error=Database error occurred. Please try again later.');
            exit;
        }
    } else {
        // Handle login GET request - serve the login form
        header('Content-Type: text/html');
        
        // Check for success messages (from signup or login)
        $message = $_GET['message'] ?? '';
        $success = $_GET['success'] ?? '';
        $error = $_GET['error'] ?? '';
        
        if (!empty($message)) {
            echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; margin: 10px; border: 1px solid #c3e6cb; border-radius: 4px; text-align: center;">
                <strong>Success!</strong> ' . htmlspecialchars($message) . '
            </div>';
        }
        
        if (!empty($success)) {
            echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; margin: 10px; border: 1px solid #c3e6cb; border-radius: 4px; text-align: center;">
                <strong>Success!</strong> ' . htmlspecialchars($success) . '
            </div>';
        }
        
        if (!empty($error)) {
            echo '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px; text-align: center;">
                <strong>Error:</strong> ' . htmlspecialchars($error) . '
            </div>';
        }
        
        include '../Front-End/Login.html';
    }
} else {
    // Default route - redirect to login
    header('Location: /login');
    exit;
}
?> 