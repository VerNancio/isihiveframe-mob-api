<?php
// Específica qual URL pode acessar
header('Access-Control-Allow-Origin: *');

// Especifica qual método http é aceito
header('Access-Control-Allow-Methods: GET');

// Cabeçalhos que podem ser recebidos
header('Access-Control-Allow-Headers: Content-Type');

// Tipo de conteúdo que é aceito no back-end
header("Content-Type: application/json");

// Requerindo o arquivo que faz a conexão com o banco de dados
require_once '../../../database/conn.php';

// Decoder de JWT
require_once '../../../token/decodeJWT.php';



// Função para retornar informações do produto
function getWhenTokenExp($token, $conn) {


    // Se o jwt é inválido...
    if (decodeJWT($token) === false) {

        $response = [
            'status' => 'error',
            'mensagem' => 'Token de autorização inválido'
        ];

        echo json_encode($response);

        exit; 
    }


    // JWT descodificado
    $decodedJWT = decodeJWT($token);


    // Resposta com timestamp
    $response = [
        'status' => 'success',
        'mensagem' => 'Timestamp de expiração do token retornado',
        'tokenExpTimestamp' => $decodedJWT->exp
    ];
    
    echo json_encode($response);

}


// Verificando a requisição
if ($_SERVER['REQUEST_METHOD'] === 'GET') {


    // Se token não enviado...
    if (!isset($_GET['token'])) {

        $resposta = [
            'status' => 'error',
            'Mensagem' => 'Token não enviado na requisição'
        ];

        echo json_encode($resposta);

        exit;
    }


    $token = $_GET['token'];


    getWhenTokenExp($token, $conn);

} else {

    $resposta = [
        'status' => 'error',
        'Mensagem' => 'método de requisição não aceito'
    ];

    echo json_encode($resposta);
}
?>