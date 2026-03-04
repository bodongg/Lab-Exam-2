<?php
declare(strict_types=1);
 
$studentId = (int)($_GET["id"] ?? 0);
if ($studentId > 0) {
    $msg = urlencode("Editing mode: update First Name, Last Name, Course Name, and Enrollment Date, then click Update Enrollment.");
    header("Location: onlineenrollment.php?edit=" . $studentId . "&msg=" . $msg . "#edit-section");
    exit;
}

header("Location: onlineenrollment.php?msg=" . urlencode("Invalid edit request."));
exit;
