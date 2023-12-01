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
require_once '../../database/conn.php';

// Decoder de JWT
require_once '../../token/decodeJWT.php';



// Função para retornar 8 produtos, com ordem de prioridade dos produtos com prazo menor e/ou ultrapassados
function getReminderProducts($nif, $token, $conn) {


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




    // Preparando a query
    $stmt = $conn->prepare("SELECT idProduto, DataFinal, DataInicial, NomeProduto FROM vw_produtos
                            WHERE NIF = ? && Situacao != 'Concluido'
                            ORDER BY DATE(DataFinal) - CURDATE()
                            LIMIT 8");


    $stmt->bind_param("i", $nif); 

    $stmt->execute();
    $result = $stmt->get_result();


    // Array para pegar todos os produtos retornados
    $produtos = array();

    

    //

    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {
            // Processar os resultados

            // Tornar as primeiras letras das strings maiúsculas
            $row['NomeProduto'] = ucfirst($row['NomeProduto']);

            $produtos[] = $row;

        }

        // Enviando a resposta do servidor
        $resposta = [
            'status' => 'success',
            'mensagem' => 'Produtos retornados com sucesso',
            'products' => $produtos,
        ];

    } else {

        $resposta = [
            'status' => 'success',
            'mensagem' => 'Nenhum produto encontrado'
        ];
    }


    echo json_encode($resposta);

}


// Verificando a requisição
if ($_SERVER['REQUEST_METHOD'] === 'GET') {


    // Se token não enviado...
    if (!isset($_GET['token'])) {

        $resposta = [
            'status' => 'error',
            'Mensagem' => 'erro: ' . 'token não enviado na requisição'
        ];

        echo json_encode($resposta);

        exit;
    }


    // Se NIF não enviado...
    if (!isset($_GET['nif'])) {
        
        $resposta = [
            'status' => 'error',
            'Mensagem' => 'erro: ' . 'nif não enviado na requisição'
        ];

        echo json_encode($resposta);

        exit;
    }

    $nif = $_GET['nif'];
    $token = $_GET['token'];


    getReminderProducts($nif, $token, $conn);

} else {

    $resposta = [
        'status' => 'error',
        'Mensagem' => 'método de requisição não aceito'
    ];

    echo json_encode($resposta);
}
?>