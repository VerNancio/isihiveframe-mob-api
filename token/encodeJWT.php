<?php

require_once '../../../vendor/autoload.php';

use \Firebase\JWT\JWT;


function encodeJWT ($data) {


    // Chave secreta para assinatura (considere armazenar em variáveis de ambiente)
    $key = "secretpassword";

    $payload = array(
        "iss" => 'http://192.168.1.8:80',
        "aud" => 'http:/192.168.1.8:8081',
        "iat" => time(),
        "exp" => time() + 172800, // Duas semanas
        "data" => $data
    );

    try {

        // Gera o token JWT
        $jwt = JWT::encode($payload, $key, 'HS256');
        // echo $jwt;

        return $jwt;
    } 
    catch (Exception $e) {
        // Lidar com a exceção (por exemplo, log, retornar uma resposta de erro, etc.)
        echo 'Erro ao gerar o token: ' . $e->getMessage();
        return null;
    }
}

