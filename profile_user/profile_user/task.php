<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// Fetch user photo
$res = $conn->query("SELECT photo FROM user WHERE id=$user_id");
$userData = $res->fetch_assoc();
$profile_img = !empty($userData['photo']) ? $userData['photo'] : "default.png";

// Add Task
if (isset($_POST['add_task'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    $stmt = $conn->prepare("INSERT INTO task (title, description, due_date, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("sss", $title, $description, $due_date);
    $stmt->execute();
}

// Update Task
if (isset($_POST['update_task'])) {
    $task_id = $_POST['task_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    $stmt = $conn->prepare("UPDATE task SET title=?, description=?, due_date=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $description, $due_date, $task_id);
    $stmt->execute();
}

// Mark Complete
if (isset($_GET['complete'])) {
    $task_id = $_GET['complete'];
    $conn->query("UPDATE task SET status='completed' WHERE id=$task_id");
    header("Location: task.php");
    exit;
}

// Delete Task
if (isset($_GET['delete'])) {
    $task_id = $_GET['delete'];
    $conn->query("DELETE FROM task WHERE id=$task_id");
    header("Location: task.php");
    exit;
}

$tasks = $conn->query("SELECT * FROM task ORDER BY due_date ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tasks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
    </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark px-3 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center text-white">
        <img src="uploads/<?= htmlspecialchars($profile_img) ?>" alt="Profile" class="profile-pic">
        <span>Welcome, <?= htmlspecialchars($name) ?></span>
    </div>
    <div>
        <a href="index.php" class="btn btn-sm btn-outline-light">Dashboard</a>
        <a href="logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
    </div>
</nav>

<div class="container mt-4" style="max-width: 900px;">
    <!-- Add / Edit Task -->
    <div class="card p-4 shadow-sm mb-4">
        <h5 class="mb-3" id="form-title">➕ Add New Task</h5>
        <form method="POST" id="taskForm">
            <input type="hidden" name="task_id" id="task_id">
            <input type="text" name="title" id="title" class="form-control mb-2" placeholder="Task Title" required>
            <textarea name="description" id="description" class="form-control mb-2" placeholder="Task Description" required></textarea>
            <input type="date" name="due_date" id="due_date" class="form-control mb-3" required>
            <button type="submit" name="add_task" id="submitBtn" class="btn btn-primary w-100">Add Task</button>
            <button type="button" class="btn btn-secondary w-100 mt-2 d-none" id="cancelEdit">Cancel Edit</button>
        </form>
    </div>

    <!-- Task List -->
    <div class="card p-4 shadow-sm">
        <h5 class="mb-3">📋 Your Tasks</h5>
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th width="35%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tasks->num_rows > 0): ?>
                    <?php while ($row = $tasks->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= htmlspecialchars($row['due_date']) ?></td>
                            <td>
                                <span class="badge <?= $row['status'] == 'completed' ? 'bg-success' : 'bg-warning' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'pending'): ?>
                                    <a href="task.php?complete=<?= $row['id'] ?>" class="btn btn-sm btn-success">Complete</a>
                                    <button type="button" class="btn btn-sm btn-primary"
                                        onclick="editTask(<?= $row['id'] ?>,'<?= htmlspecialchars($row['title']) ?>','<?= htmlspecialchars($row['description']) ?>','<?= $row['due_date'] ?>')">
                                        Edit
                                    </button>
                                    <a href="task.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-primary"
                                        onclick="editTask(<?= $row['id'] ?>,'<?= htmlspecialchars($row['title']) ?>','<?= htmlspecialchars($row['description']) ?>','<?= $row['due_date'] ?>')">
                                        Edit
                                    </button>
                                    <a href="task.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No tasks found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editTask(id, title, description, due_date) {
    document.getElementById("form-title").innerText = "✏️ Edit Task";
    document.getElementById("task_id").value = id;
    document.getElementById("title").value = title;
    document.getElementById("description").value = description;
    document.getElementById("due_date").value = due_date;

    document.getElementById("submitBtn").name = "update_task";
    document.getElementById("submitBtn").innerText = "Update Task";
    document.getElementById("cancelEdit").classList.remove("d-none");
}

document.getElementById("cancelEdit").addEventListener("click", function() {
    document.getElementById("form-title").innerText = "➕ Add New Task";
    document.getElementById("task_id").value = "";
    document.getElementById("title").value = "";
    document.getElementById("description").value = "";
    document.getElementById("due_date").value = "";

    document.getElementById("submitBtn").name = "add_task";
    document.getElementById("submitBtn").innerText = "Add Task";
    this.classList.add("d-none");
});
</script>

</body>
</html>
