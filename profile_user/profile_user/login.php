<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM user WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $name, $hashed_password);

    if ($stmt->fetch()) {
        if (password_verify($password, $hashed_password)) {
            // ✅ Set session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['name'] = $name;

            header("Location: index.php");
            exit;
        } else {
            $error = "❌ Wrong password";
        }
    } else {
        $error = "⚠️ User not found, please register";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
<div class="card shadow-sm p-3" style="width: 300px;">
    <h4 class="text-center mb-3">Login</h4>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
        <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <a href="register.php" class="d-block text-center mt-2">Register</a>
</div>
</body>
</html>
