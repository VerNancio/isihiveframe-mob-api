<?php
// Específica qual URL pode acessar
header('Access-Control-Allow-Origin: *');

// Especifica qual método http é aceito
header('Access-Control-Allow-Methods: POST');

// Cabeçalhos que podem ser recebidos
header('Access-Control-Allow-Headers: Content-Type');

// Tipo de conteúdo que é aceito no back-end
header("Content-Type: application/json");

// Buscando o arquivo do banco:
require_once '../../../database/conn.php';

// Decoder de JWT
require_once '../../../token/decodeJWT.php';



// Insere no banco de dados caso tudo ok as horas trabalhadas no produto
function postWorkedHours($productId, $nif, $hoursPerson, $horusMach, $token, $conn) {


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



    // Query para verificar se o produto já está concluido ou não
    $stmt = $conn -> prepare ("SELECT idProduto FROM produtos 
                               WHERE idProduto = ? && Situacao != 'Concluido'");

    $stmt -> bind_param('i', $productId);

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {

        $response = [
            'status' => 'error',
            'mensagem' => 'Produto já concluído'
        ];
    
        echo json_encode($response);

        exit;
    }

    //




    // Query para ver se o máximo de horas-pessoa diárias foram trabalhadas ou não
    $stmtPersonHours = $conn -> prepare ("SELECT SUM(HorasPessoa) AS HorasPessoaDia FROM CargaHoraria WHERE fk_nifTecnico = ? AND Datas = CURDATE()");
    $stmtPersonHours -> bind_param('s', $nif);

    $stmtPersonHours->execute();

    $resultPerson = $stmtPersonHours->get_result();


    // Query para ver se o máximo de horas-pessoa diárias foram trabalhadas ou não
    $stmtMachHours = $conn -> prepare ("SELECT SUM(HorasMaquina) AS HorasMaquinaDia FROM CargaHoraria WHERE fk_idProduto = ? AND Datas = CURDATE()");
    $stmtMachHours -> bind_param('i', $productId);

    $stmtMachHours->execute();

    $resultMach = $stmtMachHours->get_result();


    //

    if (!$resultPerson || !$resultMach) {


        $response = [
            'status' => 'error',
            'mensagem' => 'Erro ao lançar horas'
        ];

        echo json_encode($response);

        exit;
        
    }


    $personHoursDayPosted = $resultPerson->fetch_assoc();
    $machHoursDayPosted = $resultMach->fetch_assoc();

    $invalideHoursPerson = $personHoursDayPosted['HorasPessoaDia'] > 10 && $hoursPerson > 0;
    $invalideHoursMach = $machHoursDayPosted['HorasMaquinaDia'] > 23 && $horusMach > 0;

    //

    if ($invalideHoursPerson || $invalideHoursMach) {

        if ($invalideHoursPerson && $invalideHoursMach) $message = 'Limite de horas-máquina e horas-pessoa diário atingidos';
        elseif ($invalideHoursPerson) $message = 'Limite de horas diárias do técnico atingido';
        elseif ($invalideHoursMach) $message = 'Limite de horas-máquina diária atingido';

        $response = [
            'status' => 'success',
            'mensagem' => $message
        ];
    
        echo json_encode($response);

        die();

    }

    //
    
 

    // Query para inserir as horas trabalhadas na tabela CargaHoraria
    $stmt = $conn -> prepare ("INSERT INTO CargaHoraria 
                               (fk_idProduto, fk_nifTecnico, horasPessoa, HorasMaquina, Datas)
                               VALUES (?, ?, ?, ?, CURDATE())");

    $stmt -> bind_param('isii', $productId, $nif, $hoursPerson, $horusMach);

    if ($stmt -> execute()) {

        $response = [
            'status' => 'success',
            'mensagem' => 'Horas lançadas com sucesso'
        ];
    
        echo json_encode($response);
    }
    else {

        $response = [
            'status' => 'error',
            'mensagem' => 'Erro ao lançar horas'
        ];
    
        echo json_encode($response);
    }

};


//Função para verificar o metodo de requisição vindo do JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jsonData = file_get_contents("php://input");
    
    $data = json_decode($jsonData, true);


    // ID do produto trabalhado
    $token = $data['token'];

    // ID do produto trabalhado
    $productId = $data['productId'];

    // nif do técnico
    $nif = $data['NIF'];

    // Horas trabalhadas pela pessoa
    $hoursPerson = $data['hoursPerson'];

    // Horas trabalhadas com máquina
    $horusMach = $data['hoursMach'];
    
    postWorkedHours($productId, $nif, $hoursPerson, $horusMach, $token, $conn);

} 
else {
    $response = [
        'status' => 'error',
        'mensagem' => 'método HTTP inválido'
    ];

    echo json_encode($response);
}

?>