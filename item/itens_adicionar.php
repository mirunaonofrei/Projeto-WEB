<?php

require_once '../db.php';


// Consulta para obter o próximo código disponível para o item
$queryProxItem = "SELECT (COALESCE(MAX(cod_item), 0) + 1) AS cod_item FROM item";
$exeProxItem = $conn->prepare($queryProxItem);
$exeProxItem->execute();
$rowProxItem = $exeProxItem->fetch(PDO::FETCH_ASSOC);
$cod_item = $rowProxItem['cod_item'];

// Se for requisição GET, retorna o próximo código (cod_item) apenas
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    header('Content-Type: application/json');
    echo json_encode([
        "cod_item" => $cod_item  // Retorna apenas o próximo código disponível
    ]);
    exit();
}


// Se for requisição POST, insere um novo Item com cod_item e den_item
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cod_item = $_POST["cod_item"];
    $den_item = $_POST["den_item"];
    try {
        $queryVerificaDuplicado = "SELECT COUNT(*) FROM item WHERE den_item = :den_item";
        $stmt = $conn->prepare($queryVerificaDuplicado);
        $stmt->bindParam(':den_item', $den_item);
        $stmt->execute();
        $existe = $stmt->fetchColumn();

        if ($existe > 0) {

            echo json_encode([
                'status' => false,
                'msg' => "Já existe um item com esse nome!"
            ]);
        } else {

            // Inicia a inserção no banco de dados
            $queryInsereItem = "INSERT INTO item (cod_item, den_item) VALUES (:cod_item, :den_item)";
            $stmt = $conn->prepare($queryInsereItem);
            $stmt->bindParam(':cod_item', $cod_item);
            $stmt->bindParam(':den_item', $den_item);
            $stmt->execute();
            header('Content-Type: application/json');
            echo json_encode([
                'status' => true,
                'msg' => "Item incluido com sucesso!"
            ]);
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(["status" => false . $e->getMessage()]);
    }
    exit();
}
