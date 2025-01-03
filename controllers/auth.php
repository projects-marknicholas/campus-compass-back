<?php
class AuthController {
  public function register() {
    global $conn;
    date_default_timezone_set('Asia/Manila');
    $response = array();

    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = bin2hex(random_bytes(16));
    $image = 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png';
    $username = htmlspecialchars($data['username'] ?? '');
    $student_number = htmlspecialchars($data['student_number'] ?? '');
    $name = htmlspecialchars($data['name'] ?? '');
    $department = htmlspecialchars($data['department'] ?? '');
    $created_at = date('Y-m-d H:i:s');

    if(empty($username)){
      $response['status'] = 'error';
      $response['message'] = 'Username cannot be empty';
      echo json_encode($response);
      return;
    }

    if(empty($student_number)){
      $response['status'] = 'error';
      $response['message'] = 'Student number cannot be empty';
      echo json_encode($response);
      return;
    }

    if(empty($name)){
      $response['status'] = 'error';
      $response['message'] = 'Name cannot be empty';
      echo json_encode($response);
      return;
    }

    if(empty($department)){
      $response['status'] = 'error';
      $response['message'] = 'Department cannot be empty';
      echo json_encode($response);
      return;
    }

    // Check if the user already exists
    $stmt = $conn->prepare("SELECT student_number FROM users WHERE student_number = ?");
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
      $stmt->close();
      $response['status'] = 'error';
      $response['message'] = 'This user already exists';
      echo json_encode($response);
      return;
    }

    $stmt->close();

    // Insert data
    $stmt = $conn->prepare('INSERT INTO users (user_id, image, username, student_number, name, department, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssss', $user_id, $image, $username, $student_number, $name, $department, $created_at);
    
    if ($stmt->execute()){
      $response['status'] = 'success';
      $response['message'] = 'User created successfully';
      echo json_encode($response);
      return;
    } else{
      $response['status'] = 'error';
      $response['message'] = 'Error creating user: ' . $conn->error;
      echo json_encode($response);
      return;
    }
  }

  public function login() {
    global $conn;
    date_default_timezone_set('Asia/Manila');
    $response = array();

    $data = json_decode(file_get_contents("php://input"), true);
    $username = htmlspecialchars($data['username'] ?? '');
    $student_number = htmlspecialchars($data['student_number'] ?? '');

    if(empty($username)){
      $response['status'] = 'error';
      $response['message'] = 'Username cannot be empty';
      echo json_encode($response);
      return;
    }

    if(empty($student_number)){
      $response['status'] = 'error';
      $response['message'] = 'Student number cannot be empty';
      echo json_encode($response);
      return;
    }

    // Check if the user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND student_number = ?");
    $stmt->bind_param("ss", $username, $student_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $user = $result->fetch_assoc();
      $response['status'] = 'success';
      $response['message'] = 'Login successful';
      $response['user'] = [
        'user_id' => $user['user_id'],
        'image' => $user['image'],
        'username' => $user['username'],
        'student_number' => $user['student_number'],
        'name' => $user['name'],
        'section' => $user['section'],
        'department' => $user['department'],
        'email' => $user['email'],
        'contact' => $user['contact'],
        'created_at' => $user['created_at'],
      ];
      echo json_encode($response);
      return;
    } else {
      $response['status'] = 'error';
      $response['message'] = 'Invalid username or student number';
      echo json_encode($response);
      return;
    }

    $stmt->close();
  }

  public function update_user() {
    global $conn;
    date_default_timezone_set('Asia/Manila');
    $response = array();
  
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = htmlspecialchars($_GET['uid'] ?? '');
    $username = htmlspecialchars($data['username'] ?? '');
    $student_number = htmlspecialchars($data['student_number'] ?? '');
    $name = htmlspecialchars($data['name'] ?? '');
    $section = htmlspecialchars($data['section'] ?? '');
    $department = htmlspecialchars($data['department'] ?? '');
    $email = htmlspecialchars($data['email'] ?? '');
    $contact = htmlspecialchars($data['contact'] ?? '');
  
    if (empty($user_id)) {
      $response['status'] = 'error';
      $response['message'] = 'User ID cannot be empty';
      echo json_encode($response);
      return;
    }
  
    // Check if the user exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
  
    if ($result->num_rows === 0) {
      $stmt->close();
      $response['status'] = 'error';
      $response['message'] = 'This user does not exist';
      echo json_encode($response);
      return;
    }
    $stmt->close();
  
    // Ensure at least one field to update is provided
    if (
      empty($username) && empty($student_number) && empty($name) &&
      empty($section) && empty($department) && empty($email) &&
      empty($contact)
    ) {
      $response['status'] = 'error';
      $response['message'] = 'No data provided to update';
      echo json_encode($response);
      return;
    }
  
    // Build the update query dynamically
    $fields = [];
    $params = [];
    $types = '';
  
    if (!empty($username)) {
      $fields[] = "username = ?";
      $params[] = $username;
      $types .= 's';
    }
    if (!empty($student_number)) {
      $fields[] = "student_number = ?";
      $params[] = $student_number;
      $types .= 's';
    }
    if (!empty($name)) {
      $fields[] = "name = ?";
      $params[] = $name;
      $types .= 's';
    }
    if (!empty($section)) {
      $fields[] = "section = ?";
      $params[] = $section;
      $types .= 's';
    }
    if (!empty($department)) {
      $fields[] = "department = ?";
      $params[] = $department;
      $types .= 's';
    }
    if (!empty($email)) {
      $fields[] = "email = ?";
      $params[] = $email;
      $types .= 's';
    }
    if (!empty($contact)) {
      $fields[] = "contact = ?";
      $params[] = $contact;
      $types .= 's';
    }
  
    $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
    $params[] = $user_id;
    $types .= 's';
  
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
  
    if ($stmt->execute()) {
      $response['status'] = 'success';
      $response['message'] = 'User information updated successfully';
    } else {
      $response['status'] = 'error';
      $response['message'] = 'Error updating user: ' . $stmt->error;
    }
  
    $stmt->close();
    echo json_encode($response);
  }    
}
?>