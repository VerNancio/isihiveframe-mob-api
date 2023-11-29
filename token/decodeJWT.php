<?php

require_once '../../../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\KEY;


function decodeJWT ($jwt) {

    $key = "secretpassword";  // chave secreta

    try {

        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        // print_r($decoded);
    } 
    catch (\Exception $e) {

        echo "Erro ao decodificar o token: " . $e->getMessage();

        return false;
    }
}


function isTokenExpired($token) {
    $key = 'secretpassword'; // Sua chave secreta

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