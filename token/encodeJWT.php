<?php

require_once '../../../vendor/autoload.php';


use \Firebase\JWT\JWT;


function encodeJWT ($data) {


    // Chave secreta para assinatura 
    $key = "596293a37dd27d83c19a7a888b61f06c3f17a6c3eb4b18c99c6a936f9d7271f1";


    $currentTimestamp = time();

    // Criar um objeto DateTime com o timestamp atual
    $currentTime = new DateTime();
    $currentTime->setTimestamp($currentTimestamp);

    // Adicionar 3 semanas ao tempo atual
    $currentTime->add(new DateInterval('P3W'));

    // Obter o novo timestamp após adicionar 3 semanas
    $expTimestamp = $currentTime->getTimestamp();

    $payload = array(
        "iss" => 'http://192.168.1.8:80',
        "aud" => 'http:/192.168.1.8:8081',
        "iat" => time(),
        "exp" => $expTimestamp, // Três semanas
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

