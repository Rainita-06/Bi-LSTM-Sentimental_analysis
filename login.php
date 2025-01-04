<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <a href="signup.php">Don't have an account? Sign Up</a>
    </div>

    <?php
    session_start();
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        include 'db_connect.php';

        $user = $_POST['username'];
        $pass = $_POST['password'];

        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($pass, $row['password'])) {
                // Setting session variables
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['user_id'] = $row['id']; // Adding user ID to the session

                // Redirecting to sentiment analysis page
                echo "<script>window.location.href='sentiment_analysis.php';</script>";
            } else {
                echo "<p>Incorrect password.</p>";
            }
        } else {
            echo "<p>Username not found.</p>";
        }
        $stmt->close();
        $conn->close();
    }
    ?>
</body>
</html>
