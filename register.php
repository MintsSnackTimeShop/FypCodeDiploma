<?php
session_start();
require_once "config/database.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $role = $_POST["role"];

    // Check empty fields
    if (empty($name) || empty($username) || empty($password) || empty($confirm_password)) {
        $message = "Please fill in all fields.";
        $message_type = "error";
    }

    // Check password
    elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "error";
    }

    // Check password length
    elseif (strlen($password) < 6) {
        $message = "Password must contain at least 6 characters.";
        $message_type = "error";
    }

    // Only Manager and Staff can register
    elseif ($role !== "manager" && $role !== "staff") {
        $message = "Invalid user role.";
        $message_type = "error";
    }

    else {

        // Check if username already exists
        $check_sql = "SELECT id FROM users WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {

            $message = "Username already exists. Please choose another username.";
            $message_type = "error";

        } else {

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $sql = "INSERT INTO users (name, username, password, role)
                    VALUES (?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssss",
                $name,
                $username,
                $hashed_password,
                $role
            );

            if ($stmt->execute()) {

                $message = "Registration successful! You can now login.";
                $message_type = "success";

            } else {

                $message = "Registration failed. Please try again.";
                $message_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - StockIt</title>

    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>

<body class="login-page">
 <div class="register-back-container">
    <a href="dashboard.php" class="back-dashboard-btn">
        ← Back to Dashboard
    </a>
 </div>

    <div class="login-container">

        <div class="logo">
            <h1>StockIt</h1>
            <p>Restaurant Management System</p>
        </div>

        <h2>Create Account</h2>

        <p class="register-description">
            Register a manager or staff account to access StockIt.
        </p>

        <?php if (!empty($message)): ?>

            <div class="<?php echo $message_type == 'success'
                ? 'success-message'
                : 'error-message'; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">

                <label>Full Name</label>

                <input
                    type="text"
                    name="name"
                    placeholder="Enter full name"
                    required
                >

            </div>

            <div class="form-group">

                <label>Username</label>

                <input
                    type="text"
                    name="username"
                    placeholder="Create username"
                    required
                >

            </div>

            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Minimum 6 characters"
                    required
                >

            </div>

            <div class="form-group">

                <label>Confirm Password</label>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm password"
                    required
                >

            </div>

            <div class="form-group">

                <label>Account Type</label>

                <select name="role" required>

                    <option value="">Select account type</option>

                    <option value="manager">
                        Manager
                    </option>

                    <option value="staff">
                        Staff
                    </option>

                </select>

            </div>

            <button type="submit" class="login-btn">
                Create Account
            </button>

        </form>

        <div class="register-link">

            Already have an account?

            <a href="login.php">
                Login here
            </a>

        </div>

    </div>

</body>

</html>