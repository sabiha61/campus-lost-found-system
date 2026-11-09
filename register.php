<?php
session_start();
require "db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $student_id = trim($_POST["student_id"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if (
        $full_name === "" ||
        $student_id === "" ||
        $email === "" ||
        $phone === "" ||
        $password === "" ||
        $confirm_password === ""
    ) {
        $message = "Please fill in all fields.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";

    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $messageType = "error";

    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
        $messageType = "error";

    } else {

        $check = $conn->prepare(
            "SELECT user_id FROM users
             WHERE student_id = ? OR email = ?"
        );

        if (!$check) {
            $message = "Database error. Please try again.";
            $messageType = "error";

        } else {

            $check->bind_param("ss", $student_id, $email);
            $check->execute();

            $result = $check->get_result();

            if ($result->num_rows > 0) {

                $message = "Student ID or Email already exists.";
                $messageType = "error";

            } else {

                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmt = $conn->prepare(
                    "INSERT INTO users
                    (name, student_id, email, phone, password)
                    VALUES (?, ?, ?, ?, ?)"
                );

                if (!$stmt) {
                    $message = "Database error. Check your users table.";
                    $messageType = "error";

                } else {

                    $stmt->bind_param(
                        "sssss",
                        $full_name,
                        $student_id,
                        $email,
                        $phone,
                        $hashed_password
                    );

                    if ($stmt->execute()) {
                        $message = "Registration successful! You can now login.";
                        $messageType = "success";
                    } else {
                        $message = "Registration failed. Please try again.";
                        $messageType = "error";
                    }

                    $stmt->close();
                }
            }

            $check->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Registration | CampusFind</title>

    <link rel="stylesheet" href="style.css">

    <style>
        body {
            background: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
        }

        .register-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: #ffffff;
            border: 1px solid #e8eef3;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(20, 48, 75, 0.08);
        }

        .register-container h1 {
            color: #163253;
            text-align: center;
        }

        .register-container p {
            color: #203b59;
            text-align: center;
        }

        .register-container input {
            width: 100%;
            padding: 12px;
            margin: 8px 0 18px;
            border: 1px solid #dce5ed;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .register-container input:focus {
            outline: none;
            border-color: #159e99;
            box-shadow: 0 0 5px rgba(21, 158, 153, 0.20);
        }

        .register-container label {
            font-weight: 600;
            color: #203047;
        }

        .register-container button {
            width: 100%;
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, #159e99, #087f91);
            color: white;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
        }

        .register-container button:hover {
            background: linear-gradient(135deg, #108781, #086b7a);
        }

        .message {
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 6px;
            text-align: center;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .register-container a {
            color: #159e99;
            text-decoration: none;
            font-weight: bold;
        }

        .register-container a:hover {
            color: #087f91;
        }
    </style>
</head>

<body>

<div class="register-container">

    <h1>Student Registration</h1>

    <p>Create your CampusFind account.</p>

    <?php if ($message !== ""): ?>

        <div class="message <?= htmlspecialchars($messageType) ?>">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <form method="POST" action="register.php">

        <label for="full_name">Full Name</label>
        <input
            type="text"
            id="full_name"
            name="full_name"
            required
        >

        <label for="student_id">Student ID</label>
        <input
            type="text"
            id="student_id"
            name="student_id"
            required
        >

        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            required
        >

        <label for="phone">Phone</label>
        <input
            type="tel"
            id="phone"
            name="phone"
            required
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            minlength="8"
            required
        >

        <label for="confirm_password">Confirm Password</label>
        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            minlength="8"
            required
        >

        <button type="submit" class="primary-btn">
            Create Account
        </button>

    </form>

    <p style="margin-top:20px;">
        Already have an account?
        <a href="login.php">Login</a>
    </p>

    <p>
        <a href="index.php">Back to Home</a>
    </p>

</div>

</body>
</html>