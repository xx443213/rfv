<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$servername = "localhost";
$username = "allogbuy_twilio";
$password = "6G-psof3e6Wj";
$dbname = "allogbuy_twilio";
// Create connection

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$action = $_POST['action'] ?? '';

if ($action == 'signup') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';

    if (!$user || !$pass || !$email) {
        echo json_encode(["status" => "error", "error" => "Missing username, password, or email."]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "error" => "Invalid email format."]);
        exit;
    }

    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $user, $email, $hashed_password);

    // Execute and check for errors
    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        if ($stmt->errno == 1062) {
            echo json_encode(["status" => "error", "error" => "Username or email already taken."]);
        } else {
            echo json_encode(["status" => "error", "error" => $stmt->error]);
        }
    }

    $stmt->close();
} elseif ($action == 'signin') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (!$user || !$pass) {
        echo json_encode(["status" => "error", "error" => "Missing username or password."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        echo json_encode(["status" => "error", "error" => "User does not exist."]);
    } else {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        if (password_verify($pass, $hashed_password)) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "error" => "Invalid password."]);
        }
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "error" => "Invalid action."]);
}

$conn->close();
?>