<?php
require_once '../db.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $num_pedido = $_POST["num_pedido"] ?? null;
    $num_seq_item = $_POST["num_seq_item"] ?? null;
    $den_item = $_POST["den_item"] ?? null;
    $qtd_solicitada = $_POST["qtd_solicitada"] ?? null;
    $pre_unitario = $_POST["pre_unitario"] ?? null;
    $pre_unitario = is_string($pre_unitario) ? str_replace(',', '.', $pre_unitario) : floatval($pre_unitario);

    if (!$num_pedido || !$num_seq_item || !$den_item || !$qtd_solicitada || !$pre_unitario) {
        echo json_encode(["status" => false, "msg" => "Dados incompletos para edição."]);
        exit();
    }

    try {
        // Busca o cod_item correspondente ao den_item
        $stmtItem = $conn->prepare("SELECT cod_item FROM item WHERE den_item = :den_item LIMIT 1");
        $stmtItem->bindParam(':den_item', $den_item);
        $stmtItem->execute();
        $item = $stmtItem->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            echo json_encode(["status" => false, "msg" => "Item não encontrado."]);
            exit();
        }

        $cod_item = $item['cod_item'];

        // Agora faz o UPDATE usando o cod_item encontrado
        $stmt = $conn->prepare("UPDATE item_pedido 
                                SET cod_item = :cod_item, qtd_solicitada = :qtd_solicitada, pre_unitario = :pre_unitario
                                WHERE num_pedido = :num_pedido AND num_seq_item = :num_seq_item");

        $stmt->bindParam(':cod_item', $cod_item, PDO::PARAM_INT);
        $stmt->bindParam(':qtd_solicitada', $qtd_solicitada, PDO::PARAM_INT);
        $stmt->bindParam(':pre_unitario', $pre_unitario);
        $stmt->bindParam(':num_pedido', $num_pedido, PDO::PARAM_INT);
        $stmt->bindParam(':num_seq_item', $num_seq_item, PDO::PARAM_INT);

        $stmt->execute();

        echo json_encode(["status" => true, "msg" => "Item atualizado com sucesso."]);
    } catch (PDOException $e) {
        echo json_encode(["status" => false, "msg" => "Erro ao atualizar item: " . $e->getMessage()]);
    }

    exit();
}
