<?php
session_start();
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {

        $message = "Please enter email and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT user_id, name, email, password, role
             FROM users
             WHERE email = ?"
        );

        if ($stmt) {

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $user = $result->fetch_assoc();

                if (password_verify($password, $user["password"])) {

                    session_regenerate_id(true);

                    $_SESSION["user_id"] = $user["user_id"];
                    $_SESSION["name"] = $user["name"];
                    $_SESSION["email"] = $user["email"];
                    $_SESSION["role"] = $user["role"];

                    header("Location: index.php");
                    exit;

                } else {
                    $message = "Incorrect password.";
                }

            } else {
                $message = "No account found with this email.";
            }

            $stmt->close();

        } else {
            $message = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - CampusFind</title>

    <link rel="stylesheet" href="style.css">

    <style>

        body {
            margin: 0;
            background: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
        }

        .auth-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 25px;
            box-sizing: border-box;
            background: #ffffff;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            padding: 40px;
            border: 1px solid #e8eef3;
            border-radius: 18px;
            box-shadow: 0 8px 28px rgba(20, 48, 75, 0.08);
            box-sizing: border-box;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-header h1 {
            color: #163253;
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .auth-header h1 span {
            color: #16a6a0;
        }

        .auth-header p {
            font-size: 23px;
            font-weight: bold;
            color: #203b59;
            margin-bottom: 8px;
        }

        .auth-header span {
            color: #718096;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #203047;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 13px;
            border: 1px solid #dce5ed;
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
            color: #203047;
            background: #ffffff;
            transition: 0.2s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #159e99;
            box-shadow: 0 0 5px rgba(21, 158, 153, 0.20);
        }

        .auth-button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #159e99, #087f91);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 5px 14px rgba(21, 158, 153, 0.20);
            transition: 0.2s ease;
        }

        .auth-button:hover {
            background: linear-gradient(135deg, #108781, #086b7a);
        }

        .message {
            padding: 12px;
            margin-bottom: 18px;
            background: #ffe5e5;
            color: #b42318;
            border-radius: 8px;
            text-align: center;
        }

        .auth-footer {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }

        .auth-footer a,
        .back-home {
            color: #159e99;
            text-decoration: none;
            font-weight: bold;
            transition: 0.2s ease;
        }

        .auth-footer a:hover,
        .back-home:hover {
            color: #087f91;
        }

        .back-home {
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        @media (max-width: 480px) {

            .auth-card {
                padding: 28px 22px;
            }

            .auth-header h1 {
                font-size: 27px;
            }

        }

    </style>

</head>

<body>

    <div class="auth-container">

        <div class="auth-card">

            <div class="auth-header">

                <h1>Campus<span>Find</span></h1>

                <p>Welcome Back!</p>

                <span>Login to your account</span>

            </div>

            <?php if (!empty($message)): ?>

                <div class="message">

                    <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>

                </div>

            <?php endif; ?>

            <form method="POST" action="">

                <div class="form-group">

                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                </div>

                <button type="submit" class="auth-button">
                    Login
                </button>

            </form>

            <p class="auth-footer">

                Don't have an account?

                <a href="register.php">Create Account</a>

            </p>

            <a href="index.php" class="back-home">
                ← Back to Home
            </a>

        </div>

    </div>

</body>

</html>