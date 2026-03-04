<?php
declare(strict_types=1);
require_once __DIR__ . "/../db_connect.php";

function columnExists(mysqli $conn, string $table, string $column): bool
{
    $tableSafe = $conn->real_escape_string($table);
    $columnSafe = $conn->real_escape_string($column);
    $check = $conn->query("SHOW COLUMNS FROM `$tableSafe` LIKE '$columnSafe'");
    return $check !== false && $check->num_rows > 0;
}

if (!columnExists($conn, "enrollment", "EnrollmentDate")) {
    if (columnExists($conn, "enrollment", "EnrollmentName")) {
        $conn->query("ALTER TABLE enrollment CHANGE EnrollmentName EnrollmentDate DATE NOT NULL");
    } else {
        $conn->query("ALTER TABLE enrollment ADD COLUMN EnrollmentDate DATE NOT NULL DEFAULT '2026-01-01'");
    }
}

$message = "";
$editing = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "create") {
        $firstName = trim($_POST["first_name"] ?? "");
        $lastName = trim($_POST["last_name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $courseName = trim($_POST["course_name"] ?? "");
        $enrollmentDate = trim($_POST["enrollment_date"] ?? "");

        if ($firstName && $lastName && $email && $courseName && $enrollmentDate) {
            $conn->begin_transaction();
            try {
                $stmtStudent = $conn->prepare("INSERT INTO student (FirstName, LastName, Email) VALUES (?, ?, ?)");
                $stmtStudent->bind_param("sss", $firstName, $lastName, $email);
                $stmtStudent->execute();
                $studentId = $stmtStudent->insert_id;
                $stmtStudent->close();

                $stmtEnroll = $conn->prepare("INSERT INTO enrollment (StudentId, CourseName, EnrollmentDate) VALUES (?, ?, ?)");
                $stmtEnroll->bind_param("iss", $studentId, $courseName, $enrollmentDate);
                $stmtEnroll->execute();
                $stmtEnroll->close();

                $conn->commit();
                $message = "Student and enrollment added successfully.";
            } catch (Throwable $e) {
                $conn->rollback();
                $message = "Create failed: " . $e->getMessage();
            }
        } else {
            $message = "All fields are required.";
        }
    }

    if ($action === "update") {
        $studentId = (int)($_POST["StudentId"] ?? 0);
        $firstName = trim($_POST["first_name"] ?? "");
        $lastName = trim($_POST["last_name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $courseName = trim($_POST["course_name"] ?? "");
        $enrollmentDate = trim($_POST["enrollment_date"] ?? "");

        if ($studentId > 0) {
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
                $message = "Record updated successfully.";
            } catch (Throwable $e) {
                $conn->rollback();
                $message = "Update failed: " . $e->getMessage();
            }
        }
    }
}

if (isset($_GET["edit"])) {
    $studentId = (int)$_GET["edit"];
    if ($studentId > 0) {
        $stmtEdit = $conn->prepare("
            SELECT s.StudentId, s.FirstName, s.LastName, s.Email,
                   e.CourseName, e.EnrollmentDate
            FROM student s
            INNER JOIN enrollment e ON s.StudentId = e.StudentId
            WHERE s.StudentId = ?
            LIMIT 1
        ");
        $stmtEdit->bind_param("i", $studentId);
        $stmtEdit->execute();
        $resultEdit = $stmtEdit->get_result();
        $editing = $resultEdit->fetch_assoc();
        $stmtEdit->close();
    }
}

if (isset($_GET["msg"])) {
    $message = $_GET["msg"];
}

$result = $conn->query("
    SELECT s.StudentId, s.FirstName, s.LastName, s.Email,
           e.CourseName, e.EnrollmentDate
    FROM student s
    INNER JOIN enrollment e ON s.StudentId = e.StudentId
    ORDER BY s.StudentId DESC
");
