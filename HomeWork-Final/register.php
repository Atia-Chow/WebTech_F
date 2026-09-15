<?php
$saved_student_id = isset($_COOKIE['remembered_student_id']) ? htmlspecialchars($_COOKIE['remembered_student_id']) : '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Workshop Registration</title>
</head>
<body>
    <h2>Workshop Registration</h2>
    
    <div id="status-message" style="margin-bottom: 15px; font-weight: bold;"></div>

    <form id="registration-form">
        <label>Student ID:</label><br>
        <input type="text" name="student_id" id="student_id" value="<?= $saved_student_id ?>" required><br><br>

        <label>Full Name:</label><br>
        <input type="text" name="full_name" id="full_name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" id="email" required><br><br>

        <label>Department:</label><br>
        <input type="text" name="department" id="department" required><br><br>

        <label>Select Workshop:</label><br>
        <select name="workshop_id" id="workshop_select" required>
            <option value="">-- Select a Workshop --</option>
        </select><br><br>

        <button type="submit" id="submit-btn">Register</button>
    </form>

    <p><a href="my_workshop.php">View Registered Workshop</a></p>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            fetch('get_workshops.php')
                .then(response => response.json())
                .then(result => {
                    const select = document.getElementById('workshop_select');
                    if (result.status === 'success') {
                        result.data.forEach(workshop => {
                            const option = document.createElement('option');
                            option.value = workshop.workshop_id;
                            option.textContent = `${workshop.title} (${workshop.schedule})`;
                            select.appendChild(option);
                        });
                    } else {
                        document.getElementById('status-message').innerHTML = '<span style="color:red;">Error loading workshops.</span>';
                    }
                })
                .catch(() => {
                    document.getElementById('status-message').innerHTML = '<span style="color:red;">Network error fetching workshops.</span>';
                });
        });

        document.getElementById('registration-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const msgBox = document.getElementById('status-message');

            fetch('register_process.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    msgBox.innerHTML = `<span style="color:green;">${data.message} <a href="my_workshop.php">Click here to view your workshop.</a></span>`;
                } else {
                    msgBox.innerHTML = `<span style="color:red;">${data.message}</span>`;
                }
            })
            .catch(() => {
                msgBox.innerHTML = '<span style="color:red;">An error occurred during submission.</span>';
            });
        });
    </script>
</body>
</html>