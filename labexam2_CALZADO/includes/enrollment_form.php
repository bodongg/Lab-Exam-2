<h2 class="form-title">Enroll Student</h2>
<form method="post" action="onlineenrollment.php">
    <?php if ($editing): ?>
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="StudentId" value="<?php echo (int)$editing["StudentId"]; ?>">
    <?php else: ?>
        <input type="hidden" name="action" value="create">
    <?php endif; ?>

    <div class="form-group">
        <label>First Name</label>
        <input type="text" name="first_name" required value="<?php echo htmlspecialchars($editing["FirstName"] ?? ""); ?>">
    </div>
    <div class="form-group">
        <label>Last Name</label>
        <input type="text" name="last_name" required value="<?php echo htmlspecialchars($editing["LastName"] ?? ""); ?>">
    </div>
    <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="name@example.com" required value="<?php echo htmlspecialchars($editing["Email"] ?? ""); ?>">
    </div>
    <div class="form-group">
        <label>Course Name</label>
        <input
            type="text"
            name="course_name"
            placeholder="Computer Science"
            required
            value="<?php echo htmlspecialchars($editing["CourseName"] ?? ""); ?>"
        >
    </div>
    <div class="form-group">
        <label>Enrollment Date</label>
        <input type="date" name="enrollment_date" required value="<?php echo htmlspecialchars($editing["EnrollmentDate"] ?? ""); ?>">
    </div>
    <button class="submit-btn" type="submit"><?php echo $editing ? "Update Enrollment" : "Complete Enrollment"; ?></button>
</form>
