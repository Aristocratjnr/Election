<?php
session_start();
include 'configs/dbconnection.php';

$message = "";
$alertType = "";
$redirect = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentID = $_POST['student'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $department = $_POST['department'] ?? ''; 
    $phone = $_POST['contact'] ?? '';

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO students (studentID, name, email, password, department, contactNumber) VALUES (?, ?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("sssss", $studentID, $name, $email, $hashedPassword, $department, $phone);

        if ($stmt->execute()) {
            $message = "🎉 Registration successful!";
            $alertType = "success";
            $redirect = true;
        } else {
            $message = "❌ Error: " . $stmt->error;
            $alertType = "danger";
        }

        $stmt->close();
    } else {
        $message = "❌ Statement failed to prepare.";
        $alertType = "danger";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Processing Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">

<?php if (!empty($message)): ?>
<!-- Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header bg-<?= $alertType === 'success' ? 'success' : 'danger' ?> text-white">
        <h5 class="modal-title" id="feedbackModalLabel">
            <?= $alertType === 'success' ? 'Success' : 'Error' ?>
        </h5>
      </div>
      <div class="modal-body">
        <p><?= $message ?></p>
        <?php if ($redirect): ?>
            <p class="text-muted">Redirecting to login page...</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
    const feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
    feedbackModal.show();

    <?php if ($redirect): ?>
    setTimeout(() => {
        window.location.href = 'login.php'; 
    }, 3000);
    <?php endif; ?>
</script>
<?php endif; ?>

</body>
</html>
