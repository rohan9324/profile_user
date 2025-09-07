<?php
session_start();
include 'db.php';

// ✅ Check the same session variable
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$user = $conn->query("SELECT * FROM user WHERE id=$user_id")->fetch_assoc();

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = time() . "_" . basename($_FILES["photo"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFilePath)) {
        $conn->query("UPDATE user SET photo='$fileName' WHERE id=$user_id");
        echo "<script>alert('Profile photo updated'); window.location='profile.php';</script>";
        exit;
    } else {
        echo "<script>alert('Upload failed');</script>";
    }
}

// Reload updated data
$user = $conn->query("SELECT * FROM user WHERE id=$user_id")->fetch_assoc();
$photo = !empty($user['photo']) ? $user['photo'] : 'default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
<div class="card p-3 shadow-sm text-center" style="width: 300px;">
    <img src="uploads/<?= htmlspecialchars($photo) ?>" alt="Profile Photo" class="rounded-circle mx-auto d-block mb-3" width="80" height="80">
    <h5><?= htmlspecialchars($user['name']) ?></h5>
    <p class="small mb-1"><?= htmlspecialchars($user['email']) ?></p>
    <p class="small text-muted">Joined: <?= $user['join_date'] ?></p>

    <form method="POST" enctype="multipart/form-data" class="mt-2">
        <input type="file" name="photo" class="form-control form-control-sm mb-2" required>
        <button type="submit" class="btn btn-success btn-sm w-100">Upload Photo</button>
    </form>

    <a href="index.php" class="btn btn-secondary btn-sm mt-2 w-100">Back to Dashboard</a>
</div>
</body>
</html>
