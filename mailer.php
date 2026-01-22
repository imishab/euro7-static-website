<?php
// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

// Get form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validate required fields
if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo "Please fill in all required fields.";
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo "Please enter a valid email address.";
    exit;
}

// Sanitize inputs
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Option 1: Use Formspree (Recommended - Free, reliable, no server config needed)
// Sign up at https://formspree.io and replace YOUR_FORM_ID with your form endpoint
// Example: https://formspree.io/f/YOUR_FORM_ID
$formspree_endpoint = "https://formspree.io/f/YOUR_FORM_ID"; // Replace with your Formspree form ID

// Send via cURL to Formspree
if (function_exists('curl_init') && $formspree_endpoint !== "https://formspree.io/f/YOUR_FORM_ID") {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $formspree_endpoint);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
        'name' => $name,
        'email' => $email,
        'message' => $message,
        '_replyto' => $email,
        '_subject' => 'New Contact Form Submission from Euro7 Website'
    )));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json'
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200 || $http_code == 302) {
        http_response_code(200);
        echo "Thank you! Your message has been sent successfully. We will get back to you soon.";
        exit;
    }
}

// Fallback: Try PHP mail() function
$to = "mishabmsb91@gmail.com";
$subject = "New Contact Form Submission from Euro7 Website";
$email_body = "You have received a new message from the contact form on your website.\n\n";
$email_body .= "Name: " . $name . "\n";
$email_body .= "Email: " . $email . "\n";
$email_body .= "Message:\n" . $message . "\n";

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "From: Euro7 Website <noreply@" . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'euro7.com') . ">\r\n";
$headers .= "Reply-To: " . $name . " <" . $email . ">\r\n";

$mail_sent = @mail($to, $subject, $email_body, $headers);

if ($mail_sent) {
    http_response_code(200);
    echo "Thank you! Your message has been sent successfully. We will get back to you soon.";
} else {
    http_response_code(500);
    echo "Sorry, there was an error sending your message. Please contact us directly at mishabmsb91@gmail.com or try again later.";
}
?>
