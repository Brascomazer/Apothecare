<?php
// Schakel foutmeldingen in voor debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Basisinstellingen
session_start();
require_once '../includes/chat_config.php';

header('Content-Type: application/json');

// Log functie voor debugging
function debug_log($message) {
    error_log("[APOTHECARE CHAT DEBUG] " . $message);
}

// Functie om prompt leaking uit antwoorden te filteren
function clean_bot_response($response) {
    // Verwijder instructie-tekst
    $response = preg_replace('/Je bent ApothBot.*?(?:\.|\n|$)/', '', $response);
    
    // Verwijder instructie-fragmenten over max 2-3 zinnen
    $response = preg_replace('/(?:geef|gebruik) (?:korte|alleen).*?(?:\.|\n|$)/i', '', $response);
    
    // Verwijder verwijzingen naar instructies
    $response = preg_replace('/Als de gebruiker vraagt naar.*?(?:\.|\n|$)/i', '', $response);
    
    // Verwijder komma's aan het begin van de zin en extra spaties
    $response = preg_replace('/^\s*,\s*/', '', $response);
    $response = preg_replace('/\s{2,}/', ' ', $response);
    
    // Verwijder eventuele vragen die het model zelf stelt
    $response = preg_replace('/\b(Vraag|Heb je|Wilt u|Heeft u|Kan ik|Wat|Hoe).*\?$/m', '', $response);
    
    // Trim spaties en leestekens aan begin en eind
    $response = trim($response, " \t\n\r\0\x0B.,;:");
    
    return $response;
}

try {
    // Ontvang gebruikersbericht
    $rawInput = file_get_contents('php://input');
    debug_log("Raw input: " . $rawInput);
    
    $data = json_decode($rawInput, true);
    if (!$data || !isset($data['message'])) {
        throw new Exception("Geen geldige invoer ontvangen");
    }
    
    $message = trim($data['message']);
    
    if (empty($message)) {
        throw new Exception("Geen geldige vraag ontvangen");
    }
    
    debug_log("Verwerking bericht: " . $message);
    
    // Controleer API key
    if (!defined('OPENROUTER_API_KEY') || empty(OPENROUTER_API_KEY)) {
        throw new Exception("API sleutel is niet geconfigureerd of leeg");
    }
    
    // OpenRouter request voorbereiden met verbeterde prompt
    $payload = [
        'model' => 'openai/gpt-3.5-turbo',  // Switch naar een beter model dat werkt met system prompts
        'messages' => [
            [
                'role' => 'system',
                'content' => 'Je bent een behulpzame apotheekassistent. Geef korte, duidelijke en medisch correcte antwoorden in het Nederlands. Beperk je tot advies over vrij verkrijgbare medicijnen zoals paracetamol of ibuprofen. Verwijs bij twijfel naar een arts.'
            ],
            [
                'role' => 'user',
                'content' => $message
            ]
        ],
        'max_tokens' => 150,
        'temperature' => 0.3,
        'stop' => ["Vraag:", "Question:", "Gebruiker:", "User:"]
    ];
    
    debug_log("Request payload: " . json_encode($payload));
    
    // OpenRouter API aanroepen
    $ch = curl_init("https://openrouter.ai/api/v1/chat/completions");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENROUTER_API_KEY,
            'HTTP-Referer: http://localhost',
            'X-Title: Apothecare Chatbot'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30
    ]);
    
    debug_log("API verzoek naar OpenRouter verstuurd");
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $curl_error = curl_error($ch);
    $http_code = $info['http_code'];
    curl_close($ch);
    
    debug_log("CURL info: " . json_encode($info));
    
    if ($curl_error) {
        debug_log("CURL error: " . $curl_error);
        throw new Exception("API connectie probleem: " . $curl_error);
    }
    
    debug_log("API Response raw: " . $response);
    
    if ($http_code !== 200) {
        debug_log("HTTP error: " . $http_code . " - " . $response);
        throw new Exception("API fout " . $http_code . ": " . $response);
    }
    
    // Response parsen
    $result = json_decode($response, true);
    if ($result === null) {
        debug_log("JSON decode error: " . json_last_error_msg());
        throw new Exception("Kan API response niet verwerken: " . json_last_error_msg());
    }
    
    debug_log("API Response parsed: " . print_r($result, true));
    
    // Antwoord extraheren en opschonen
    if (isset($result['choices'][0]['message']['content'])) {
        $bot_reply = $result['choices'][0]['message']['content'];
        
        // Clean de response met onze nieuwe functie
        $bot_reply = clean_bot_response($bot_reply);
        
        // Zorg dat er altijd een antwoord is
        if (empty($bot_reply)) {
            $bot_reply = "Voor hoofdpijn en koorts kunt u paracetamol of ibuprofen gebruiken. Zorg voor voldoende rust en drink veel water.";
        }
        
        // Afkorten indien nodig
        if (strlen($bot_reply) > 250) {
            $shortened = substr($bot_reply, 0, 250);
            $last_period = strrpos($shortened, '.');
            
            if ($last_period !== false) {
                $bot_reply = substr($shortened, 0, $last_period + 1);
            } else {
                $bot_reply = $shortened . "...";
            }
        }
        
        debug_log("Gefilterd antwoord: " . $bot_reply);
        echo json_encode(['success' => true, 'message' => $bot_reply]);
    } else {
        throw new Exception("Onverwacht API-antwoordformaat: " . json_encode($result));
    }

} catch (Exception $e) {
    debug_log("ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => "Sorry, ik kan momenteel je vraag niet beantwoorden. Probeer het later opnieuw."
    ]);
}
?>