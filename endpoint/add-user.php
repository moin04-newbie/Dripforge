<?php
session_start();
include('../conn/conn.php');

$firstName = $_POST['first_name'] ?? '';
$lastName = $_POST['last_name'] ?? '';
$contactNumber = $_POST['contact_number'] ?? '';
$email = $_POST['email'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Basic validation
if (empty($firstName) || empty($lastName) || empty($contactNumber) || empty($email) || empty($username) || empty($password)) {
    header("Location: ../index.php?reg_error=" . urlencode("All fields are required"));
    exit();
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../index.php?reg_error=" . urlencode("Invalid email format"));
    exit();
}

// Password validation (at least 6 characters)
if (strlen($password) < 6) {
    header("Location: ../index.php?reg_error=" . urlencode("Password must be at least 6 characters long"));
    exit();
}

try {
    // Check if username exists
    $stmtUsername = $conn->prepare("SELECT `username` FROM `tbl_user` WHERE `username` = :username");
    $stmtUsername->execute(['username' => $username]);
    $usernameExists = $stmtUsername->fetch(PDO::FETCH_ASSOC);

    if ($usernameExists) {
        header("Location: ../index.php?reg_error=" . urlencode("Username already exists"));
        exit();
    }
    
    // Check if email exists
    $stmtEmail = $conn->prepare("SELECT `email` FROM `tbl_user` WHERE `email` = :email");
    $stmtEmail->execute(['email' => $email]);
    $emailExists = $stmtEmail->fetch(PDO::FETCH_ASSOC);

    if ($emailExists) {
        header("Location: ../index.php?reg_error=" . urlencode("Email already exists"));
        exit();
    }

    // Check if name exists
    $stmt = $conn->prepare("SELECT `first_name`, `last_name` FROM `tbl_user` WHERE `first_name` = :first_name AND `last_name` = :last_name");
    $stmt->execute([
        'first_name' => $firstName,
        'last_name'=> $lastName
    ]);
    $nameExist = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($nameExist)) {
        $conn->beginTransaction();

        $insertStmt = $conn->prepare("INSERT INTO `tbl_user` (`tbl_user_id`, `first_name`, `last_name`, `contact_number`, `email`, `username`, `password`) VALUES (NULL, :first_name, :last_name, :contact_number, :email, :username, :password)");
        $insertStmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
        $insertStmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
        $insertStmt->bindParam(':contact_number', $contactNumber, PDO::PARAM_STR);
        $insertStmt->bindParam(':email', $email, PDO::PARAM_STR);
        $insertStmt->bindParam(':username', $username, PDO::PARAM_STR);
        $insertStmt->bindParam(':password', $password, PDO::PARAM_STR);
        $insertStmt->execute();

        // Get the user ID for the new user
        $userId = $conn->lastInsertId();

        $conn->commit();
        
        // Set session variables for auto-login
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;

        // Redirect to main page
        header("Location: ../main/web.php");
        exit();
    } else {
        header("Location: ../index.php?reg_error=" . urlencode("User with this name already exists"));
        exit();
    }

} catch (PDOException $e) {
    // Rollback if there was an error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    header("Location: ../index.php?reg_error=" . urlencode("Registration failed: " . $e->getMessage()));
    exit();
}
?>