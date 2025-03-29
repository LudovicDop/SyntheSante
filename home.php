<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['login']) || !isset($_SESSION['email'])) {
    header('Location: index.html');  // Rediriger vers la page de connexion si non connecté
    exit;
}

$login = htmlspecialchars($_SESSION['login']);
$email = htmlspecialchars($_SESSION['email']);
$keywords = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['description'])) {
    $description = htmlspecialchars($_POST['description']);
    
    // Remplace par ta clé API valide obtenue sur OpenAI
    $apiKey = '';  // Exemple : sk-XXXXXXX
    $url = 'https://api.openai.com/v1/chat/completions';

    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'user', 'content' => "Extrais les mots-clés principaux de ce texte : " . $description]
        ],
        'temperature' => 0.3
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);

    if ($result === false) {
        echo 'Erreur cURL : ' . curl_error($ch);  // Affiche les erreurs cURL s'il y en a
    }

    curl_close($ch);

    $response = json_decode($result, true);

    if (!$response || isset($response['error'])) {
        echo 'Erreur API : ' . ($response['error']['message'] ?? 'Réponse invalide de');
    } else if (isset($response['choices'][0]['message']['content'])) {
        $keywordsText = trim($response['choices'][0]['message']['content']);
        $keywords = preg_split('/,|\n|\r/', $keywordsText);  // Diviser par virgule ou retour à la ligne
        $keywords = array_filter(array_map('trim', $keywords));  // Supprimer les espaces inutiles
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Accueil - Synthé Santé</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background-color: #1e88e5;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            position: relative;
        }
        .user-menu {
            position: relative;
            display: inline-block;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }
        .user-menu:hover .dropdown-content {
            display: block;
        }
        .content {
            padding: 20px;
            max-width: 800px;
            margin: auto;
            background: white;
            border-radius: 8px;
        }
        h1 {
            color: #1a237e;
        }
        .keyword {
            background-color: #e3f2fd;
            padding: 5px 10px;
            border-radius: 5px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="user-menu">
            <span><?php echo $login; ?> ▼</span>
            <div class="dropdown-content">
                <form action="logout.php" method="POST">
                    <button type="submit">Déconnexion</button>
                </form>
            </div>
        </div>
    </div>

    <div class="content">
        <h1>Bienvenue, <?php echo $login; ?> !</h1>
        <p>Votre adresse email est : <?php echo $email; ?></p>

        <h2>Extracteur de mots-clés</h2>
        <form method="POST">
            <textarea name="description" rows="5" style="width: 100%;"></textarea><br><br>
            <button type="submit">Extraire les mots-clés</button>
        </form>

        <?php if (!empty($keywords)): ?>
            <h3>Mots-clés extraits :</h3>
            <?php foreach ($keywords as $keyword): ?>
                <div class="keyword"><?php echo htmlspecialchars($keyword); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
