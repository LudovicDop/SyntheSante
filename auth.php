<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "ldoppler";
$password = "jesaispas123";
$dbname = "SyntheSante";

session_start();  // Démarrer la session

// Créer une connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Vérifie l'action (login ou register)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'register') {
        if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
            $login = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Vérifier si l'email existe déjà
            $stmt = $conn->prepare("SELECT email FROM client WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // Si l'email existe déjà, empêcher la création d'un nouveau compte
                echo "Cet email est déjà utilisé. Veuillez en essayer un autre.";
            } else {
                // Inscrire le nouvel utilisateur
                $stmt->close();

                $stmt = $conn->prepare("INSERT INTO client (login, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $login, $email, $password);

                if ($stmt->execute()) {
                    echo "Inscription réussie";
                } else {
                    echo "Erreur lors de l'inscription : " . $stmt->error;
                }
            }
            $stmt->close();
        } else {
            echo "Tous les champs doivent être remplis pour s'inscrire.";
        }
    }

    if ($action === 'login') {
        if (isset($_POST['email']) && isset($_POST['password'])) {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $stmt = $conn->prepare("SELECT login, password FROM client WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($login, $hashedPassword);
            $stmt->fetch();
            $stmt->close();

            if ($login && password_verify($password, $hashedPassword)) {
                // Stocker les informations dans la session
                $_SESSION['login'] = $login;
                $_SESSION['email'] = $email;

                // 🚨 Rediriger directement vers home.php après connexion réussie
                header("Location: home.php");
                exit();
            } else {
                echo "Échec de la connexion. Identifiants incorrects.";
            }
        } else {
            echo "Tous les champs doivent être remplis pour se connecter.";
        }
    }
}

$conn->close();
?>
