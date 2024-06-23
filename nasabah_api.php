<?php
include 'conn.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getUsers();
        break;
    case 'POST':
        createUser();
        break;
    case 'PUT':
        updateUser();
        break;
    case 'DELETE':
        deleteUser();
        break;
    default:
        echo json_encode(["message" => "Invalid request method"]);
        break;
}

function getUsers()
{
    global $conn;
    $sql = "SELECT * FROM users"; // Pastikan tabel bernama 'users'
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode($users);
    } else {
        echo json_encode(["message" => "No users found"]);
    }
}

function createUser()
{
    global $conn;

    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $kelamin = isset($_POST['kelamin']) ? $_POST['kelamin'] : '';
    $no_anggota = isset($_POST['no_anggota']) ? $_POST['no_anggota'] : ''; // Pastikan ini ada
    $agama = isset($_POST['agama']) ? $_POST['agama'] : '';
    $identitas = isset($_POST['identitas']) ? $_POST['identitas'] : '';
    $no_identitas = isset($_POST['no_identitas']) ? $_POST['no_identitas'] : '';
    $alamat = isset($_POST['alamat']) ? $_POST['alamat'] : '';
    $remember_token = isset($_POST['remember_token']) ? $_POST['remember_token'] : '';

    // Menangani upload file
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto']['tmp_name'];
        $fileName = $_FILES['foto']['name'];
        $fileSize = $_FILES['foto']['size'];
        $fileType = $_FILES['foto']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Menyusun nama file baru
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        // Memeriksa dan membuat direktori jika belum ada
        $uploadFileDir = './images/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $foto = $newFileName;
        } else {
            echo json_encode(["message" => "Error moving the uploaded file"]);
            return;
        }
    } else {
        $foto = null;
    }

    $sql = "INSERT INTO users (name, email, password, kelamin, no_anggota, agama, identitas, no_identitas, alamat, foto, remember_token) VALUES ('$name', '$email', '$password', '$kelamin', '$no_anggota', '$agama', '$identitas', '$no_identitas', '$alamat', '$foto', '$remember_token')";

    if ($conn->query($sql) === TRUE) {
        // Mendapatkan ID pengguna yang baru dibuat
        $last_id = $conn->insert_id;

        // Mengambil data pengguna yang baru dibuat
        $result = $conn->query("SELECT * FROM users WHERE id = $last_id");
        if ($result->num_rows > 0) {
            $new_user = $result->fetch_assoc();
            echo json_encode(["message" => "User created successfully", "user" => $new_user]);
        } else {
            echo json_encode(["message" => "User created successfully, but failed to retrieve user data"]);
        }
    } else {
        echo json_encode(["message" => "Error: " . $conn->error]);
    }
}


function updateUser()
{
    global $conn;
    parse_str(file_get_contents("php://input"), $data);
    $id = $data['id'];

    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'];
    $kelamin = $data['kelamin'];
    $no_anggota = $data['no_anggota'];
    $agama = $data['agama'];
    $identitas = $data['identitas'];
    $no_identitas = $data['no_identitas'];
    $alamat = $data['alamat'];
    $remember_token = $data['remember_token'];

    // Menangani upload file
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto']['tmp_name'];
        $fileName = $_FILES['foto']['name'];
        $fileSize = $_FILES['foto']['size'];
        $fileType = $_FILES['foto']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Menyusun nama file baru
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        // Memeriksa dan membuat direktori jika belum ada
        $uploadFileDir = './images/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $foto = $newFileName;
        } else {
            echo json_encode(["message" => "Error moving the uploaded file"]);
            return;
        }
    } else {
        $foto = $data['foto'];
    }

    $sql = "UPDATE users SET name='$name', email='$email', password='$password', kelamin='$kelamin', no_anggota='$no_anggota', agama='$agama', identitas='$identitas', no_identitas='$no_identitas', alamat='$alamat', foto='$foto', remember_token='$remember_token' WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "User updated successfully"]);
    } else {
        echo json_encode(["message" => "Error: " . $conn->error]);
    }
}

function deleteUser()
{
    global $conn;
    parse_str(file_get_contents("php://input"), $data);
    $id = $data['id'];

    $sql = "DELETE FROM users WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "User deleted successfully"]);
    } else {
        echo json_encode(["message" => "Error: " . $conn->error]);
    }
}
