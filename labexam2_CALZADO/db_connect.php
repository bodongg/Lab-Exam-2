<?php
declare(strict_types=1);

$host = "localhost";
$user = "root";
$pass = "";
$dbName = "onlineenrollment_db";

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS `$dbName`");
$conn->select_db($dbName);

$conn->query("
    CREATE TABLE IF NOT EXISTS student (
        StudentId INT AUTO_INCREMENT PRIMARY KEY,
        FirstName VARCHAR(100) NOT NULL,
        LastName VARCHAR(100) NOT NULL,
        Email VARCHAR(100) NOT NULL UNIQUE
    )
");

$conn->query("
    CREATE TABLE IF NOT EXISTS enrollment (
        EnrollmentId INT AUTO_INCREMENT PRIMARY KEY,
        StudentId INT NOT NULL UNIQUE,
        CourseName VARCHAR(100) NOT NULL,
        EnrollmentDate DATE NOT NULL,
        CONSTRAINT fk_enrollment_student
            FOREIGN KEY (StudentId) REFERENCES student(StudentId)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
");

function findActualColumnName(mysqli $conn, string $table, string $expectedName): ?string
{
    $tableSafe = $conn->real_escape_string($table);
    $res = $conn->query("SHOW COLUMNS FROM `$tableSafe`");
    if ($res === false) {
        return null;
    }

    while ($row = $res->fetch_assoc()) {
        if (strtolower((string)$row["Field"]) === strtolower($expectedName)) {
            return (string)$row["Field"];
        }
    }

    return null;
}

function normalizeDateForInput(?string $value): string
{
    if ($value === null || $value === "") {
        return "";
    }

    $ts = strtotime($value);
    return $ts ? date("Y-m-d", $ts) : "";
}

function updateEnrollmentRecord(
    mysqli $conn,
    int $studentId,
    string $firstName,
    string $lastName,
    string $email,
    string $courseName,
    string $enrollmentDate
): void {
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
    } catch (Throwable $e) {
        $conn->rollback();
        throw $e;
    }
}

function deleteStudentRecord(mysqli $conn, int $studentId): void
{
    $stmtDelete = $conn->prepare("DELETE FROM student WHERE StudentId = ?");
    $stmtDelete->bind_param("i", $studentId);
    $stmtDelete->execute();
    $stmtDelete->close();
}

// Auto-fix old schema variants.
$enrollmentDateCol = findActualColumnName($conn, "enrollment", "EnrollmentDate");
$enrollmentIdCol = findActualColumnName($conn, "enrollment", "EnrollmentId");
if ($enrollmentDateCol !== null && $enrollmentIdCol === null) {
    $checkCol = $conn->query("SHOW COLUMNS FROM enrollment LIKE '" . $conn->real_escape_string($enrollmentDateCol) . "'");
    $meta = $checkCol ? $checkCol->fetch_assoc() : null;
    $isAutoInc = $meta && stripos((string)$meta["Extra"], "auto_increment") !== false;
    if ($isAutoInc) {
        $conn->query("ALTER TABLE enrollment CHANGE `$enrollmentDateCol` EnrollmentId INT NOT NULL AUTO_INCREMENT");
    }
}

$enrollmentNameCol = findActualColumnName($conn, "enrollment", "EnrollmentName");
$enrollmentDateCol = findActualColumnName($conn, "enrollment", "EnrollmentDate");
if ($enrollmentDateCol === null) {
    if ($enrollmentNameCol !== null) {
        $conn->query("ALTER TABLE enrollment CHANGE `$enrollmentNameCol` EnrollmentDate DATE NOT NULL");
    } else {
        $conn->query("ALTER TABLE enrollment ADD COLUMN EnrollmentDate DATE NOT NULL DEFAULT '2026-01-01'");
    }
}
