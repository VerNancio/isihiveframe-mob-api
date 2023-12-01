<?php

require_once '../../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\KEY;


function decodeJWT ($jwt) {
    

    $key = "596293a37dd27d83c19a7a888b61f06c3f17a6c3eb4b18c99c6a936f9d7271f1";  // chave secreta

    try {

        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        
        return $decoded;
    } 
    catch (\Exception $e) {

        echo "Erro ao decodificar o token: " . $e->getMessage();

        return false;
    }
}


function isTokenExpired($token) {

    $key = "596293a37dd27d83c19a7a888b61f06c3f17a6c3eb4b18c99c6a936f9d7271f1"; // Sua chave secreta

    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));

        return false;
    } 

    // Se cair nesse catch significa que está expirado
    catch (\Firebase\JWT\ExpiredException $e) {

        return true;
    } 
    
    // Erro ao decodificar token
    catch (\Exception $e) {

        return true;
    }
}

