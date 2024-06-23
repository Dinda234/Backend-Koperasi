<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'conn.php';

function login($email, $password) {
    global $conn;

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows == 0) {
        return array('success' => false, 'message' => 'Invalid email or password');
    }

    // Fetch user data
    $user = $result->fetch_assoc();

    // Verify password
    if (!password_verify($password, $user['password'])) {
        return array('success' => false, 'message' => 'Invalid email or password');
    }

    // Return success response
    return array('success' => true, 'message' => 'Login successful', 'id' => $user['id'], 'email' => $user['email']);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Check if email and password are provided
    if (isset($input['email']) && isset($input['password'])) {
        $email = $input['email'];
        $password = $input['password'];

        // Call the login function
        $response = login($email, $password);
    } else {
        $response = array('success' => false, 'message' => 'Email and password required');
    }
} else {
    $response = array('success' => false, 'message' => 'Invalid request method');
}

// Output the JSON response
echo json_encode($response);
?>
