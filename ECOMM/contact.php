<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/header.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {

        $name = mysqli_real_escape_string($conn, $name);
        $email = mysqli_real_escape_string($conn, $email);
        $subject = mysqli_real_escape_string($conn, $subject);
        $message = mysqli_real_escape_string($conn, $message);

        $insertQuery = "INSERT INTO contact_messages (name, email, subject, message) 
                        VALUES ('$name', '$email', '$subject', '$message')";

        if (mysqli_query($conn, $insertQuery)) {
            $success_message = 'Thank you for contacting us! We will get back to you soon.';

            $name = $email = $subject = $message = '';
        } else {
            $error_message = 'Sorry, there was an error sending your message. Please try again.';
        }
    }
}
?>

<div class="container">
    <div class="page-header">
        <h1>Contact Us</h1>
        <p>Have a question? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <div class="contact-container">
        <div class="contact-form-section">
            <h2>Send us a Message</h2>
            <form method="POST" action="" class="contact-form">
                <div class="form-group">
                    <label for="name">Your Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Your Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="subject">Subject *</label>
                    <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" rows="6" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>

        <div class="contact-info-section">
            <h2>Get in Touch</h2>
            <div class="contact-info-item">
                <h3>📧 Email</h3>
                <p>info@theshoevault.com</p>
            </div>
            <div class="contact-info-item">
                <h3>📞 Phone</h3>
                <p>+91 1234567890</p>
            </div>
            <div class="contact-info-item">
                <h3>📍 Address</h3>
                <p>City Center, Patna</p>
            </div>
            <div class="contact-info-item">
                <h3>🕐 Business Hours</h3>
                <p>Monday - Saturday: 9:00 AM - 6:00 PM<br>Sunday: Closed</p>
            </div>
        </div>
    </div>
</div>

<style>
    .page-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .page-header h1 {
        color: #333;
        margin-bottom: 10px;
    }

    .page-header p {
        color: #666;
        font-size: 16px;
    }

    .contact-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-bottom: 40px;
    }

    .contact-form-section,
    .contact-info-section {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .contact-form-section h2,
    .contact-info-section h2 {
        margin-bottom: 20px;
        color: #333;
    }

    .contact-form .form-group {
        margin-bottom: 20px;
    }

    .contact-form label {
        display: block;
        margin-bottom: 5px;
        color: #333;
        font-weight: 500;
    }

    .contact-form input,
    .contact-form textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .contact-form input:focus,
    .contact-form textarea:focus {
        outline: none;
        border-color: #333;
    }

    .contact-form textarea {
        resize: vertical;
    }

    .contact-info-item {
        margin-bottom: 25px;
        padding-bottom: 25px;
        border-bottom: 1px solid #eee;
    }

    .contact-info-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .contact-info-item h3 {
        color: #333;
        margin-bottom: 10px;
        font-size: 18px;
    }

    .contact-info-item p {
        color: #666;
        line-height: 1.6;
    }

    .alert {
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    @media (max-width: 768px) {
        .contact-container {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>