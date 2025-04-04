<?php

require_once 'db.php';

$stmt = $conn->prepare("SELECT cod_item, den_item FROM item");
$stmt->execute();
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ies_new = false;
// Caso o formulário tenha sido enviado, processar
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $num_pedido = $_POST["num_pedido"];
    $num_seq_item = $_POST["num_seq_item"];
    $num_seq_item_get = isset($_GET["num_seq_item"]) ? $_GET["num_seq_item"] : "";
    $cod_item = $_POST["cod_item"];
    $qtd_solicitada = $_POST["qtd_solicitada"];
    $pre_unitario = str_replace(',', '.', $_POST["pre_unitario"]); // Corrige formato de preço

    // Verificar se o item já existe no pedido com o mesmo `num_seq_item`
    $stmt = $conn->prepare("SELECT COUNT(*) FROM item_pedido WHERE num_pedido = :num_pedido AND num_seq_item = :num_seq_item");
    $stmt->bindParam(':num_pedido', $num_pedido);
    $stmt->bindParam(':num_seq_item', $num_seq_item);
    $stmt->execute();
    $exists = $stmt->fetchColumn();

    if ($exists == 0) {
        // Inserir novo item
        $url_num_seq_prod = '';
        $stmt = $conn->prepare("INSERT INTO item_pedido (num_pedido, num_seq_item, cod_item, qtd_solicitada, pre_unitario) 
                                VALUES (:num_pedido, :num_seq_item, :cod_item, :qtd_solicitada, :pre_unitario)");
        $_SESSION['mensagem'] = "Item inserido com sucesso!";
    } else {
        // Atualizar item existente
        $url_num_seq_prod = "&num_seq_item=$num_seq_item";
        $stmt = $conn->prepare("UPDATE item_pedido SET qtd_solicitada = :qtd_solicitada, pre_unitario = :pre_unitario, cod_item = :cod_item
                                WHERE num_pedido = :num_pedido AND num_seq_item = :num_seq_item");
        $_SESSION['mensagem'] = "Item atualizado com sucesso!";
    }

    // Vincula os parâmetros antes de executar a query
    $stmt->bindParam(':num_pedido', $num_pedido);
    $stmt->bindParam(':num_seq_item', $num_seq_item);
    $stmt->bindParam(':cod_item', $cod_item);
    $stmt->bindParam(':qtd_solicitada', $qtd_solicitada);
    $stmt->bindParam(':pre_unitario', $pre_unitario);
    $stmt->execute();

    // Não limpa o formulário
    // Redireciona de volta para a página de controle de pedido
    header("Location: controlar_item_pedido.php?num_pedido=$num_pedido" . $url_num_seq_prod);
    exit();

} else {
    // Variáveis iniciais
    $num_pedido = isset($_GET["num_pedido"]) ? $_GET["num_pedido"] : "";
    $num_seq_item = isset($_GET["num_seq_item"]) ? $_GET["num_seq_item"] : "";
    $cod_item = "";
    $den_item = "";
    $qtd_solicitada = "";
    $pre_unitario = "";
    $erro_item = "";
}
if ($ies_new) $num_seq_item = '';
if ($num_pedido && $num_seq_item) {
    // Carregar os dados do item se estivermos editando um item
    $stmt = $conn->prepare("SELECT * FROM item_pedido WHERE num_pedido = :num_pedido AND num_seq_item = :num_seq_item");
    $stmt->bindParam(':num_pedido', $num_pedido);
    $stmt->bindParam(':num_seq_item', $num_seq_item);
    $stmt->execute();
    $item_pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item_pedido) {
        // Preencher os campos do formulário com os dados do item
        $cod_item = $item_pedido['cod_item'];
        $qtd_solicitada = $item_pedido['qtd_solicitada'];
        $pre_unitario = $item_pedido['pre_unitario'];

        // Buscar a descrição do item
        $stmt = $conn->prepare("SELECT den_item FROM item WHERE cod_item = :cod_item");
        $stmt->bindParam(':cod_item', $cod_item);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        $den_item = $item['den_item'];
    }
} else {
    // Gerar o próximo num_seq_item para um novo item
    if ($num_pedido) {
        $stmt = $conn->prepare("SELECT COALESCE(MAX(num_seq_item), 0) + 1 AS num_seq_item FROM item_pedido WHERE num_pedido = :num_pedido");
        $stmt->bindParam(':num_pedido', $num_pedido);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $num_seq_item = $result['num_seq_item'];
    }
}

$conn = null;
?>
