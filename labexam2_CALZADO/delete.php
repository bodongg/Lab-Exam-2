<?php
declare(strict_types=1);
require_once __DIR__ . "/db_connect.php";

$studentId = (int)($_GET["id"] ?? 0);
if ($studentId <= 0) {
    header("Location: onlineenrollment.php?msg=" . urlencode("Invalid delete request."));
    exit;
}

deleteStudentRecord($conn, $studentId);

header("Location: onlineenrollment.php?msg=" . urlencode("Record deleted successfully."));
exit;
