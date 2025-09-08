<?php
// Minimal test email script
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $_POST['to_email'] ?? 'dsadiq1994@gmail.com';
    $subject = 'Test Email from MADE';
    $message = 'This is a test email from MADE Registration.';
    $headers = [
        'From: MADE Registration <no-reply@made-reg.co.uk>',
        'Reply-To: no-reply@made-reg.co.uk',
        'X-Mailer: PHP/' . phpversion(),
        'MIME-Version: 1.0',
        'Content-type: text/plain; charset=utf-8'
    ];
    
    $result = mail($to, $subject, $message, implode("\r\n", $headers));
    
    if ($result) {
        echo "Email sent successfully to $to";
    } else {
        echo "Failed to send email. Error: " . print_r(error_get_last(), true);
    }
    echo "<br><a href='test-email.php'>Try again</a>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Test</title>
</head>
<body>
    <h2>Send Test Email</h2>
    <form method="post">
        <label>To Email: </label>
        <input type="email" name="to_email" value="dsadiq1994@gmail.com">
        <button type="submit">Send Test Email</button>
    </form>
</body>
</html>
