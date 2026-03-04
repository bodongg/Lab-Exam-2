<?php
declare(strict_types=1);
require_once __DIR__ . "/db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $studentId = (int)($_GET["id"] ?? 0);
    if ($studentId > 0) {
        $msg = urlencode("Editing mode: update First Name, Last Name, Course Name, and Enrollment Date, then click Update Enrollment.");
        header("Location: onlineenrollment.php?edit=" . $studentId . "&msg=" . $msg . "#edit-section");
        exit;
    }

    header("Location: onlineenrollment.php?msg=" . urlencode("Invalid edit request."));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "update") {
    $studentId = (int)($_POST["StudentId"] ?? 0);
    $firstName = trim($_POST["first_name"] ?? "");
    $lastName = trim($_POST["last_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $courseName = trim($_POST["course_name"] ?? "");
    $enrollmentDate = normalizeDateForInput(trim($_POST["enrollment_date"] ?? ""));

    if ($studentId > 0 && $firstName && $lastName && $email && $courseName && $enrollmentDate) {
        $conn->begin_transaction();
        try {
            $stmtStudent = $conn->prepare("UPDATE student SET FirstName = ?, LastName = ?, Email = ? WHERE StudentId = ?");
            $stmtStudent->bind_param("sssi", $firstName, $lastName, $email, $studentId);
            $stmtStudent->execute();
            $stmtStudent->close();

            $stmtEnroll = $conn->prepare("UPDATE enrollment SET CourseName = ?, EnrollmentDate = ? WHERE StudentId = ?");
            $stmtEnroll->bind_param("ssi", $courseName, $enrollmentDate, $studentId);
            $stmtEnroll->execute();
            $stmtEnroll->close();

            $conn->commit();
            header("Location: onlineenrollment.php?msg=" . urlencode("Record updated successfully.") . "#main-page");
            exit;
        } catch (Throwable $e) {
            $conn->rollback();
            header("Location: onlineenrollment.php?msg=" . urlencode("Update failed: " . $e->getMessage()));
            exit;
        }
    }

    header("Location: onlineenrollment.php?msg=" . urlencode("Please complete all fields with valid values."));
    exit;
}

header("Location: onlineenrollment.php");
exit;
