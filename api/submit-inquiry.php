<?php
/**
 * Submit Inquiry API
 * Saves inquiry to database and returns WhatsApp URL
 * Built in Kornwestheim
 * Developed by Cüneyt Kaya — https://kayacuneyt.com
 */

header('Content-Type: application/json');

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/includes/config.php';
require_once APP_ROOT . '/includes/db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid security token');
    }
    
    // Sanitize and validate input
    $name = trim($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $preferredSize = trim($_POST['preferred_size'] ?? '');
    $preferredColor = trim($_POST['preferred_color'] ?? '');
    $artworkTitle = trim($_POST['artwork_title'] ?? '');
    
    // Validation
    if (empty($name)) {
        throw new Exception('Name is required');
    }
    
    if (!$email) {
        throw new Exception('Valid email is required');
    }
    
    if (empty($message)) {
        throw new Exception('Message is required');
    }
    
    // Length validation
    if (strlen($name) > 100) {
        throw new Exception('Name is too long');
    }
    
    if (strlen($message) > 1000) {
        throw new Exception('Message is too long');
    }
    
    if (strlen($artworkTitle) > 255) {
        throw new Exception('Artwork title is too long');
    }

    // Save to database
    $sql = "INSERT INTO inquiries (name, email, phone, message, preferred_size, preferred_color, artwork_title) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $inquiryId = $db->insert($sql, [
        $name,
        $email,
        $phone,
        $message,
        $preferredSize,
        $preferredColor,
        $artworkTitle ?: null
    ]);
    
    if (!$inquiryId) {
        throw new Exception('Failed to save inquiry');
    }
    
    // Get WhatsApp phone number from settings
    $whatsappPhone = get_setting('whatsapp_phone', '+447123456789');
    
    // Remove any non-digit characters except leading +
    $whatsappPhone = preg_replace('/[^+0-9]/', '', $whatsappPhone);
    
    // Build WhatsApp message
    $whatsappMessage = "🎨 New Artwork Inquiry from Website\n\n";
    $whatsappMessage .= "👤 Name: $name\n";
    $whatsappMessage .= "📧 Email: $email\n";
    if (!empty($artworkTitle)) {
        $whatsappMessage .= "🖼️ Artwork: $artworkTitle\n";
    }
    
    if (!empty($phone)) {
        $whatsappMessage .= "📱 Phone: $phone\n";
    }
    
    if (!empty($preferredSize)) {
        $whatsappMessage .= "📏 Preferred Size: $preferredSize\n";
    }
    
    if (!empty($preferredColor)) {
        $whatsappMessage .= "🎨 Preferred Color: $preferredColor\n";
    }
    
    $whatsappMessage .= "\n💬 Message:\n$message";
    
    // Build WhatsApp URL
    $whatsappURL = "https://api.whatsapp.com/send?phone=" . urlencode($whatsappPhone) . "&text=" . urlencode($whatsappMessage);
    
    // Optional: Send email notification to artist
    $artistEmail = get_setting('artist_email');
    if ($artistEmail && filter_var($artistEmail, FILTER_VALIDATE_EMAIL)) {
        $emailSubject = "New Artwork Inquiry from $name";
        $emailBody = "You have received a new inquiry:\n\n";
        $emailBody .= "Name: $name\n";
        $emailBody .= "Email: $email\n";
        $emailBody .= "Phone: $phone\n";
        if (!empty($artworkTitle)) {
            $emailBody .= "Artwork: $artworkTitle\n";
        }
        $emailBody .= "Preferred Size: $preferredSize\n";
        $emailBody .= "Preferred Color: $preferredColor\n";
        $emailBody .= "\nMessage:\n$message\n";
        $emailBody .= "\n---\nSent from Artist Portfolio";
        
        $headers = "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        @mail($artistEmail, $emailSubject, $emailBody, $headers);
    }
    
    // Return success with WhatsApp URL
    echo json_encode([
        'success' => true,
        'message' => 'Inquiry submitted successfully',
        'whatsapp_url' => $whatsappURL,
        'inquiry_id' => $inquiryId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
