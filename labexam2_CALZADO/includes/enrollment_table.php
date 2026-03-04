<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Course</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div class="name-main"><?php echo htmlspecialchars($row["FirstName"] . " " . $row["LastName"]); ?></div>
                        <div class="name-sub"><?php echo htmlspecialchars($row["Email"]); ?></div>
                    </td>
                    <td><span class="course-tag"><?php echo htmlspecialchars($row["CourseName"]); ?></span></td>
                    <td>
                        <?php
                        $rawDate = $row["EnrollmentDate"];
                        $ts = strtotime($rawDate);
                        echo $ts ? htmlspecialchars(date("M d, Y", $ts)) : htmlspecialchars($rawDate);
                        ?>
                    </td>
                    <td class="actions">
                        <a class="edit-link" href="edit.php?id=<?php echo (int)$row["StudentId"]; ?>">Edit</a>
                        <a class="delete-link" href="delete.php?id=<?php echo (int)$row["StudentId"]; ?>" onclick="return confirm('Delete this student and enrollment?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No records found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
