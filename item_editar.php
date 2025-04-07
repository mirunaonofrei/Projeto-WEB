<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $num_pedido = $_POST["num_pedido"] ?? null;
    $num_seq_item = $_POST["num_seq_item"] ?? null;
    $cod_item = $_POST["cod_item"] ?? null;
    $qtd_solicitada = $_POST["qtd_solicitada"] ?? null;
    $pre_unitario = isset($_POST["pre_unitario"]) ? str_replace(',', '.', $_POST["pre_unitario"]) : null;

    if (!$num_pedido || !$num_seq_item || !$cod_item || !$qtd_solicitada || !$pre_unitario) {
        echo json_encode(["status" => false, "msg" => "Dados incompletos para edição."]);
        exit();
    }

    try {
        $stmt = $conn->prepare("UPDATE item_pedido 
                                SET qtd_solicitada = :qtd_solicitada, pre_unitario = :pre_unitario, cod_item = :cod_item
                                WHERE num_pedido = :num_pedido AND num_seq_item = :num_seq_item");

        $stmt->bindParam(':num_pedido', $num_pedido, PDO::PARAM_INT);
        $stmt->bindParam(':num_seq_item', $num_seq_item, PDO::PARAM_INT);
        $stmt->bindParam(':cod_item', $cod_item, PDO::PARAM_INT);
        $stmt->bindParam(':qtd_solicitada', $qtd_solicitada, PDO::PARAM_INT);
        $stmt->bindParam(':pre_unitario', $pre_unitario);

        $stmt->execute();

        echo json_encode(["status" => true, "msg" => "Item atualizado com sucesso."]);
    } catch (PDOException $e) {
        echo json_encode(["status" => false, "msg" => "Erro ao atualizar item: " . $e->getMessage()]);
    }

    exit();
}
