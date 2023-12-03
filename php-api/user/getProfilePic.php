<?php
// Específica qual URL pode acessar
header('Access-Control-Allow-Origin: *');

// Especifica qual método http é aceito
header('Access-Control-Allow-Methods: PUT');

// Cabeçalhos que podem ser recebidos
header('Access-Control-Allow-Headers: Content-Type');

// Tipo de conteúdo que é aceito no back-end
header("Content-Type: application/json");

// Buscando o arquivo do banco:
require_once '../../../database/conn.php';

// Decoder de JWT
require_once '../../../token/decodeJWT.php';



if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    
    // Caso não tenha sido enviado o token...
    if (!isset($_GET['token'])) {

        $response = [
            'status' => 'error',
            'mensagem' => 'Token não enviado na requisição'
        ];

        echo json_encode($response);

        exit; 
    } 


    // Caso esteja setado, é definido o valor da variavel token
    $token = $_GET['token'];


    // Se o token está expirado 
    if (isTokenExpired($token) === true) {

        $response = [
            'status' => 'error',
            'mensagem' => 'Token de autorização expirado'
        ];

        echo json_encode($response);

        exit; 
    }


    // Se o jwt é inválido...
    if (decodeJWT($token) === false) {

        $response = [
            'status' => 'error',
            'mensagem' => 'Token de autorização inválido'
        ];

        echo json_encode($response);

        exit; 
    }

    // 


    // Verifica se o id do produto não foi enviado na requisição...
    if (!isset($_GET['nif'])) {

        $response = [
            'status' => 'error',
            'mensagem' => 'Nif do técnico não enviado na requisição'
        ];
        
        echo json_encode($response);

        exit;
    } 

    $nif = $_GET['nif'];
    

    // Consulta SQL para obter a imagem (substitua 'sua_tabela' e 'seu_id' pelos valores apropriados)
    $stmt = $conn->prepare("SELECT FotoDePerfil FROM Usuarios WHERE NIF = ?");
    $stmt->bind_param('s', $nif);

    $stmt->execute();


    //

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();

        $response = [
            'status' => 'error',
            'mensagem' => 'Nif do técnico não enviado na requisição',
            'blobFoto' => $row['FotoDePerfil']
        ];

    }
} 


?>