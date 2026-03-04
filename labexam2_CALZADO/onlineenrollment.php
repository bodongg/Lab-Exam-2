<?php
declare(strict_types=1);
require_once __DIR__ . "/includes/enrollment_controller.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Enrollment System</title>
    <link rel="stylesheet" href="onlinesystem.css">
</head>
<body>
    <div class="wrap">
        <h1 class="panel-title">Course Enrollment System</h1>
        <p class="small">Database: <?php echo htmlspecialchars($dbName); ?></p>
        <?php if ($message): ?>
            <div class="msg"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <div class="grid">
            <div>
                <?php require __DIR__ . "/includes/enrollment_form.php"; ?>
            </div>

            <div>
                <?php require __DIR__ . "/includes/enrollment_table.php"; ?>
            </div>
        </div>
    </div>
</body>
</html>
