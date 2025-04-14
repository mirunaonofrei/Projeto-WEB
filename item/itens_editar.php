<?php

require_once '../db.php';

$cod_item = isset($_POST['cod_item']) ? $_POST['cod_item'] : (isset($_GET['cod_item']) ? $_GET['cod_item'] : null);

if ($cod_item) {
    // Obtém os dados do item
    $stmt = $conn->prepare("SELECT * FROM item WHERE cod_item = :cod_item");
    $stmt->bindParam(':cod_item', $cod_item);
    $stmt->execute();
    $item = $stmt->fetch();
    if (!$item) {
        echo json_encode(["status" => false, "message" => "item não encontrado"]);
        exit();
    }

    $den_item = $item ? $item['den_item'] : "";

    // Se for requisição GET, retorna os dados
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // Carregar os item para o formulário
        $stmtitem = $conn->prepare("SELECT den_item FROM item");
        $stmtitem->execute();

        header('Content-Type: application/json');
        echo json_encode([
            "cod_item" => $cod_item,
            "den_item" => $den_item
        ]);
        exit();
    }

    // Processar requisição POST para editar o item
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            if (!isset($_POST["den_item"]) || !isset($_POST["cod_item"])) {
                echo json_encode(["status" => false, "message" => "Dados inválidos"]);
                exit();
            }
            $den_item = $_POST["den_item"];
            $cod_item = $_POST["cod_item"];

            $stmt = $conn->prepare("UPDATE item SET den_item = :den_item WHERE cod_item = :cod_item");
            $stmt->bindParam(':cod_item', $cod_item);
            $stmt->bindParam(':den_item', $den_item);
            $stmt->execute();

            header('Content-Type: application/json');
            echo json_encode(["status" => true]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(["status" => false, "message" => $e->getMessage()]);
        }
        exit();
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(["status" => false, "message" => "Número do item não fornecido"]);
    exit();
}
?>