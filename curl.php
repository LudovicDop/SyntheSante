<?php
if (function_exists('curl_version')) {
    echo "cURL est installé sur votre serveur.";
} else {
    echo "cURL n'est pas installé. Veuillez l'activer.";
}
