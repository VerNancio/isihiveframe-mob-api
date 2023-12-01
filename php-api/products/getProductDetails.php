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



// Função para retornar informações do produto
function getProductDetails($productId, $token, $conn) {


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



    //

    // Preparando query
    $stmt = $conn->prepare("SELECT * FROM vw_produtos
                            WHERE idProduto = ?");

    $stmt->bind_param("i", $productId); 

    $stmt->execute();
    $result = $stmt->get_result();
    

    if ($result->num_rows > 0) {
        
        // Loop para poder fazer a alteração e capitalizar as primeiras letras de string
        while($row = $result->fetch_assoc()) {

            
            // Tornar as primeiras letras das strings maiúsculas
            $row['NomeProduto'] = ucfirst($row['NomeProduto']);
            $row['Area'] = ucfirst($row['Area']);
            $row['ServicoCategoria'] = ucfirst($row['ServicoCategoria']);
            $row['TituloProposta'] = ucfirst($row['TituloProposta']);

            $row['Status'] = ucfirst($row['Status']);
            $row['Situacao'] = ucfirst($row['Situacao']);

            $row['Nome'] = ucfirst($row['Nome']);
            $row['TipoUser'] = ucfirst($row['TipoUser']);
            $row['Maquina'] = ucfirst($row['Maquina']);


            //

            $details = $row;
        }


        // Preparando query para retornar o número total de horas do produto
        $stmt = $conn->prepare("SELECT SUM(HorasPessoa) AS HorasPessoa, SUM(HorasMaquina) AS HorasMaquina FROM CargaHoraria
                            WHERE fk_idProduto = ?");

        $stmt->bind_param("i", $productId); 

        $stmt->execute();
        $result = $stmt->get_result();

        $resposta = [
            'status' => 'success',
            'mensagem' => 'Produtos retornados com sucesso',
            'productDetails' => $details,
            'workedHours' => $result->fetch_assoc()
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
            'Mensagem' => 'Token não enviado na requisição'
        ];

        echo json_encode($resposta);

        exit;
    }


    if (!isset($_GET['productId'])) {

        $resposta = [
            'status' => 'error',
            'Mensagem' => 'Id de produto não enviado na requisição'
        ];

        echo json_encode($resposta);

        exit;

    }

    $productId =  $_GET['productId'];
    $token = $_GET['token'];


    getProductDetails($productId, $token, $conn);

} else {

    $resposta = [
        'status' => 'error',
        'Mensagem' => 'método de requisição não aceito'
    ];

    echo json_encode($resposta);
}
?>