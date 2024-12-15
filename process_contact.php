<?php
require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Admin Email
$adminEmail = 'taha.tech.contact@gmail.com';
$verificationCode = rand(100000, 999999); // Generate a random verification code

// Send Verification Email
function sendVerificationEmail($toEmail, $verificationCode) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Use your email provider's SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'taha.tech.sender@gmail.com'; // Your email address
        $mail->Password = 'Tahatech.sender1'; // Your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('taha.tech.sender@gmail.com', 'Taha Tech Admin');
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code';
        $mail->Body = "Your verification code is: $verificationCode";

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['verify_code'])) {
    $data = [
        'name' => htmlspecialchars($_POST['name']),
        'email' => htmlspecialchars($_POST['email']),
        'message' => htmlspecialchars($_POST['message']),
        'file' => ''
    ];

    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $fileName = $_FILES['file']['name'];
        $fileTmpName = $_FILES['file']['tmp_name'];
        $fileDestination = 'uploads/' . $fileName;
        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            $data['file'] = $fileDestination;
        }
    }

    // Save data to a JSON file
    $jsonData = json_decode(file_get_contents('data.json'), true);
    $jsonData[] = $data;
    file_put_contents('data.json', json_encode($jsonData, JSON_PRETTY_PRINT));

    echo "<p>Thank you for contacting us, {$data['name']}. We will get back to you soon.</p>";
}

// Verify Code
$codeVerified = false;
if (isset($_POST['verify_code'])) {
    if ($_POST['verification_code'] == $_POST['stored_verification_code']) {
        $codeVerified = true;
    } else {
        echo "<p>Invalid verification code. Please try again.</p>";
    }
}

if (isset($_POST['admin_login'])) {
    // Send verification email
    sendVerificationEmail($adminEmail, $verificationCode);
    echo "<p>A verification code has been sent to your email. Please enter the code below to access the admin panel.</p>";
    echo '<form action="#admin-panel" method="post">
            <label for="verification_code">Verification Code:</label>
            <input type="hidden" name="stored_verification_code" value="' . $verificationCode . '">
            <input type="text" id="verification_code" name="verification_code" required>
            <input type="submit" name="verify_code" value="Verify">
          </form>';
}
?>
