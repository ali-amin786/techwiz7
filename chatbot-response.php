<?php
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$rawMessage = trim((string) ($_POST['message'] ?? ''));
if ($rawMessage === '') {
    echo json_encode(['response' => 'Please type a question or topic so I can assist you!']);
    exit();
}

$lower = strtolower($rawMessage);
$response = '';

// Check chatbot_faqs table in DB
$faqs = $pdo->query('SELECT id, question, keywords, answer FROM chatbot_faqs')->fetchAll();

foreach ($faqs as $faq) {
    $keywords = array_filter(array_map('trim', explode(',', strtolower($faq['keywords'] ?? ''))));
    foreach ($keywords as $kw) {
        if ($kw !== '' && strpos($lower, $kw) !== false) {
            $response = $faq['answer'];
            break 2;
        }
    }
}

// Contextual checks if no direct FAQ matched
if ($response === '') {
    if (strpos($lower, 'anime') !== false) {
        $response = "Looking for Anime? Check out our Anime section in Explore for top picks like 'Neon Blade Chronicles', character profiles like Aiko Renshaw, and upcoming releases!";
    } elseif (strpos($lower, 'game') !== false || strpos($lower, 'gaming') !== false) {
        $response = "Gamers unite! We feature raid tactics, gaming walkthroughs, and upcoming releases. Check out our Event Map for gaming conventions!";
    } elseif (strpos($lower, 'k-pop') !== false || strpos($lower, 'kpop') !== false) {
        $response = "K-Pop fans! Explore artist spotlights, upcoming comeback stages, and pre-order merchandise lightsticks in our Merch Showcase!";
    } elseif (strpos($lower, 'event') !== false || strpos($lower, 'con') !== false || strpos($lower, 'meet') !== false) {
        $response = "Visit our Event Map page to view upcoming fandom conventions, meetups, venues, day, date, and timing across Karachi, Lahore, and Islamabad!";
    } elseif (strpos($lower, 'merch') !== false || strpos($lower, 'buy') !== false || strpos($lower, 'shop') !== false) {
        $response = "Our Merchandise Showcase displays collectible items, limited edition drops, and pre-orders. Notice that items are for fandom discovery and showcase!";
    } elseif (strpos($lower, 'submit') !== false || strpos($lower, 'upload') !== false) {
        $response = "Registered users can submit posts, videos, or articles via 'Submit Post' in the navigation. Submissions are reviewed by admins before going live!";
    } elseif (strpos($lower, 'save') !== false || strpos($lower, 'bookmark') !== false) {
        $response = "You can save your favorite posts by clicking the 'Save' button when logged in. All saved items appear under 'Saved Content'!";
    } elseif (strpos($lower, 'hello') !== false || strpos($lower, 'hi') !== false || strpos($lower, 'hey') !== false) {
        $response = "Hello fandom enthusiast! 🌟 Welcome to Fan Hub Plus. How can I guide you today? You can ask about categories, submitting content, events, or saving posts.";
    } else {
        $response = "I'm here to help you navigate Fan Hub Plus! You can ask about our 8 fandom categories (Anime, Gaming, K-Pop, etc.), how to submit posts, find upcoming events, or contact support.";
    }
}

// Log conversation in database
$userId = current_user_id();
try {
    $logStmt = $pdo->prepare('INSERT INTO chatbot_queries (user_id, message, response) VALUES (?, ?, ?)');
    $logStmt->execute([$userId, $rawMessage, $response]);
} catch (Exception $e) {
    // Non-blocking log failure
}

echo json_encode([
    'status' => 'success',
    'response' => $response,
]);
