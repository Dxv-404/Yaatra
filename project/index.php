<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        // Login code
        $username = $_POST['username'];
        $password = $_POST['password'];

        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                header("Location: home.php");
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "Username not found!";
        }
    } elseif (isset($_POST['signup'])) {
        // Signup code
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $query = "INSERT INTO users (name, email, phone, username, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $name, $email, $phone, $username, $password);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $conn->insert_id;
            header("Location: home.php");
            exit();
        } else {
            $error = "Signup failed! Username or Email may already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="icon.ico">
    <title>सफ़र</title>
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap" rel="stylesheet">
    <style>
        /* Intro Animation Styles */
        .intro-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #000;
            z-index: 10;
        }

        .intro-text {
            font-size: 150px;
            font-family: 'Pixelify Sans', sans-serif;
            color: #fff;
            opacity: 1;
            animation: changeLanguage 4s steps(7) forwards, fadeOut 1s 4s forwards;
        }

        .intro-text::after {
            content: "सफ़र";
            animation: changeLanguage 4s steps(7) forwards;
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            100% { opacity: 0; }
        }

        @keyframes changeLanguage {
            0% { content: "सफ़र"; }
            14.28% { content: "صفر"; } /* Bengali */
            28.57% { content: "সফর"; } /* Gujarati */
            42.85% { content: "சஃபர்"; }  /* Malayalam */
            57.14% { content: "సఫర్"; }  /* Telugu */
            71.42% { content: "സഫർ"; }  /* Tamil */
            85.71% { content: "ಯಾತ್ರೆ"; }  /* Kannada */
            100% { content: "सफ़र"; }    /* Back to Hindi */
        }

        /* Main Page Styles */
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #000;
            font-family: 'Pixelify Sans', sans-serif;
            color: white;
            background-image: url('background.gif');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .container {
            background-color: rgba(0, 0, 0, 0.95);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1;
            display: none;
        }

        /* Typewriter effect */
        h1 {
            font-size: 2rem;
            letter-spacing: 2px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            border-right: 2px solid white;
            white-space: nowrap;
            width: 0;
            animation: typing 2s steps(10, end) forwards, blink-caret 0.75s step-end infinite;
        }

        @keyframes typing {
            from { width: 0; }
            to { width: 100%; }
        }

        @keyframes blink-caret {
            from, to { border-color: transparent; }
            50% { border-color: white; }
        }

        .input-group {
            margin-bottom: 20px;
            width: 100%;
        }

        label {
            font-size: 0.9rem;
            margin-bottom: 5px;
            text-transform: uppercase;
            color: #f0f0f0;
        }

        input[type="text"], input[type="password"] {
            width: 300px;
            padding: 12px;
            border: 2px solid white;
            background: none;
            color: white;
            font-size: 1rem;
            outline: none;
        }

        .submit-btn {
            margin-top: 30px;
            background-color: #000;
            color: white;
            border: 2px solid white;
            width: 60px;
            height: 60px;
            cursor: pointer;
            font-size: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '→';
            font-size: 2rem;
        }

        .submit-btn:hover {
            background-color: white;
            color: black;
        }

        .toggle-btn {
            background-color: #6c757d;
            margin-top: 20px;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            text-transform: uppercase;
        }

        .toggle-btn:hover {
            background-color: #5a6268;
        }

        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- Intro Animation Container -->
<div class="intro-container" id="introContainer">
    <div class="intro-text"></div>
</div>

<!-- Login/Signup Container -->
<div class="container" id="mainContainer">
    <h1 id="formTitle">Login</h1>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    
    <!-- Login Form -->
    <form id="loginForm" method="post">
        <input type="hidden" name="login">
        <div class="input-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" placeholder="Enter your username" required>
        </div>
        <div class="input-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="submit-btn"></button>
        <button type="button" class="toggle-btn" onclick="toggleForms()">Switch to Signup</button>
    </form>

    <!-- Signup Form -->
    <form id="signupForm" method="post" style="display: none;">
        <input type="hidden" name="signup">
        <div class="input-group">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" placeholder="Enter your name" required>
        </div>
        <div class="input-group">
            <label for="email">Email</label>
            <input type="text" name="email" id="email" placeholder="Enter your email" required>
        </div>
        <div class="input-group">
            <label for="phone">Phone Number</label>
            <input type="text" name="phone" id="phone" placeholder="Enter your phone number" required>
        </div>
        <div class="input-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" placeholder="Enter your username" required>
        </div>
        <div class="input-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="submit-btn"></button>
        <button type="button" class="toggle-btn" onclick="toggleForms()">Switch to Login</button>
    </form>
</div>

<script>
    // Toggle between Login and Signup forms
    function toggleForms() {
        const loginForm = document.getElementById('loginForm');
        const signupForm = document.getElementById('signupForm');
        const formTitle = document.getElementById('formTitle');

        if (loginForm.style.display === 'none') {
            loginForm.style.display = 'block';
            signupForm.style.display = 'none';
            formTitle.textContent = "Login";
        } else {
            loginForm.style.display = 'none';
            signupForm.style.display = 'block';
            formTitle.textContent = "Signup";
        }
    }

    // Hide intro and show main content after animation
    setTimeout(() => {
        document.getElementById('introContainer').style.display = 'none';
        document.getElementById('mainContainer').style.display = 'block';
    }, 5000);
</script>
</body>
</html>
