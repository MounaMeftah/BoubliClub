<?php
/**
 * Gestionnaire d'envoi d'emails pour Boubli Club
 * Créé pour traiter les soumissions du formulaire de contact
 */

// Configuration de l'en-tête JSON pour les réponses
header('Content-Type: application/json; charset=utf-8');

// Activer le rapport d'erreurs pour le débogage (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // 0 en production, 1 pour déboguer

// ============================================
// CONFIGURATION - À PERSONNALISER
// ============================================

// Votre adresse email où recevoir les messages
define('RECIPIENT_EMAIL', 'meftahmouna691@gmail.com');

// Nom de l'expéditeur affiché
define('SENDER_NAME', 'Formulaire Boubli Club');

// Sujet de l'email de notification
define('EMAIL_SUBJECT_PREFIX', '[Boubli Club Contact]');

// ============================================
// SÉCURITÉ ET VALIDATION
// ============================================

/**
 * Fonction de validation et nettoyage des données
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validation d'email avancée
 */
function validate_email($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Vérifier le domaine
    $domain = substr(strrchr($email, "@"), 1);
    if (!checkdnsrr($domain, "MX")) {
        return false;
    }
    
    return true;
}

/**
 * Protection anti-spam - Honeypot
 */
function check_honeypot() {
    if (!empty($_POST['website'])) {
        return false; // Bot détecté
    }
    return true;
}

/**
 * Protection contre les injections d'en-têtes
 */
function check_email_injection($field) {
    $pattern = "/(\r\n|\r|\n|%0a|%0d|Content-Type:|Bcc:|Cc:|To:)/i";
    if (preg_match($pattern, $field)) {
        return false;
    }
    return true;
}

/**
 * Limitation de débit (Rate Limiting) simple
 */
function check_rate_limit() {
    session_start();
    
    if (!isset($_SESSION['last_submit_time'])) {
        $_SESSION['last_submit_time'] = time();
        return true;
    }
    
    $time_since_last_submit = time() - $_SESSION['last_submit_time'];
    
    // Minimum 30 secondes entre chaque soumission
    if ($time_since_last_submit < 30) {
        return false;
    }
    
    $_SESSION['last_submit_time'] = time();
    return true;
}

// ============================================
// TRAITEMENT PRINCIPAL
// ============================================

// Vérifier que la requête est bien en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);
    exit;
}

// Vérifier le honeypot (anti-spam)
if (!check_honeypot()) {
    echo json_encode([
        'success' => false,
        'message' => 'Soumission invalide détectée.'
    ]);
    exit;
}

// Vérifier la limitation de débit
if (!check_rate_limit()) {
    echo json_encode([
        'success' => false,
        'message' => 'Veuillez attendre 30 secondes avant de renvoyer le formulaire.'
    ]);
    exit;
}

// Récupération et validation des données
$errors = [];

// Nom
if (empty($_POST['name'])) {
    $errors[] = 'Le nom est requis.';
} else {
    $name = sanitize_input($_POST['name']);
    if (strlen($name) < 2 || strlen($name) > 100) {
        $errors[] = 'Le nom doit contenir entre 2 et 100 caractères.';
    }
}

// Email
if (empty($_POST['email'])) {
    $errors[] = 'L\'email est requis.';
} else {
    $email = sanitize_input($_POST['email']);
    if (!validate_email($email)) {
        $errors[] = 'L\'adresse email n\'est pas valide.';
    }
    if (!check_email_injection($email)) {
        $errors[] = 'Email suspect détecté.';
    }
}

// Sujet
if (empty($_POST['subject'])) {
    $errors[] = 'Le sujet est requis.';
} else {
    $subject = sanitize_input($_POST['subject']);
}

// Message
if (empty($_POST['message'])) {
    $errors[] = 'Le message est requis.';
} else {
    $message = sanitize_input($_POST['message']);
    if (strlen($message) < 10) {
        $errors[] = 'Le message doit contenir au moins 10 caractères.';
    }
    if (strlen($message) > 2000) {
        $errors[] = 'Le message ne peut pas dépasser 2000 caractères.';
    }
}

// Si des erreurs existent, renvoyer la réponse
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors)
    ]);
    exit;
}

// ============================================
// PRÉPARATION ET ENVOI DE L'EMAIL
// ============================================

// Construction du sujet
$email_subject = EMAIL_SUBJECT_PREFIX . ' ' . $subject;

// Construction du corps de l'email en HTML
$email_body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc2626; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .info-row { margin: 15px 0; padding: 10px; background: white; border-left: 3px solid #dc2626; }
        .label { font-weight: bold; color: #dc2626; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2 style='margin: 0;'>📧 Nouveau Message - Boubli Club</h2>
        </div>
        <div class='content'>
            <div class='info-row'>
                <span class='label'>Nom :</span> " . htmlspecialchars($name) . "
            </div>
            <div class='info-row'>
                <span class='label'>Email :</span> <a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a>
            </div>
            <div class='info-row'>
                <span class='label'>Sujet :</span> " . htmlspecialchars($subject) . "
            </div>
            <div class='info-row'>
                <span class='label'>Message :</span><br><br>
                " . nl2br(htmlspecialchars($message)) . "
            </div>
            <div class='info-row'>
                <span class='label'>Date :</span> " . date('d/m/Y à H:i:s') . "
            </div>
            <div class='info-row'>
                <span class='label'>IP :</span> " . $_SERVER['REMOTE_ADDR'] . "
            </div>
        </div>
        <div class='footer'>
            <p>Ce message a été envoyé depuis le formulaire de contact de Boubli Club</p>
        </div>
    </div>
</body>
</html>
";

// En-têtes de l'email
$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: ' . SENDER_NAME . ' <noreply@' . $_SERVER['HTTP_HOST'] . '>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'X-Priority: 1'
];

$headers_string = implode("\r\n", $headers);

// Tentative d'envoi de l'email
$mail_sent = @mail(RECIPIENT_EMAIL, $email_subject, $email_body, $headers_string);

// ============================================
// RÉPONSE
// ============================================

if ($mail_sent) {
    // Log de succès (optionnel)
    error_log(date('[Y-m-d H:i:s]') . " Email envoyé avec succès de: $email\n", 3, 'contact_logs.txt');
    
    echo json_encode([
        'success' => true,
        'message' => 'Merci pour votre message ! Nous vous répondrons dans les plus brefs délais.'
    ]);
} else {
    // Log d'erreur (optionnel)
    error_log(date('[Y-m-d H:i:s]') . " Échec d'envoi d'email de: $email\n", 3, 'contact_errors.txt');
    
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue lors de l\'envoi. Veuillez réessayer plus tard ou nous contacter directement.'
    ]);
}

exit;
?>