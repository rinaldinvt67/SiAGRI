<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="Assets/css/style.css"> 
</head>

<body>
    <div class="wrapper">
        <form action="auth.php" method="POST" class="form-step2">
            <h1>Create Account</h1>
            <input type="hidden" name="action" value="register">
            <div class="input-box">
                <input type="text" placeholder="Username" required>
            </div>
    
            <div class="input-box">
                <input type="email" placeholder="Email" required>
            </div>
    
            <div class="input-box">
                <input type="password" placeholder="Password" required>
            </div>
            
            <div class="input-box">
                <input type="password" placeholder="Confirm Password" required>
            </div>
    
            <div class="terms-check">
                <label>
                    <input type="checkbox" required> I agree to Terms & Privacy
                </label>
            </div>
    
            <button type="submit" class="btn">Register</button>
                <input type="radio" name="step" id="step1" checked hidden>
                <input type="radio" name="step" id="step2" hidden>
            <button type="" for="step1" class="btn back-btn" href="login-page.php">Back</button>
            </form>
            
    </div>
</body>

</html>