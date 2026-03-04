<?php
declare(strict_types=1);
require_once __DIR__ . "/db_connect.php";

$studentId = (int)($_GET["id"] ?? 0);
if ($studentId <= 0) {
    header("Location: onlineenrollment.php?msg=" . urlencode("Invalid delete request."));
    exit;
}

$stmtDelete = $conn->prepare("DELETE FROM student WHERE StudentId = ?");
$stmtDelete->bind_param("i", $studentId);
$stmtDelete->execute();
$stmtDelete->close();

header("Location: onlineenrollment.php?msg=" . urlencode("Record deleted successfully."));
exit;
