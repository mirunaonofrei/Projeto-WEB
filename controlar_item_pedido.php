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

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controlar Item</title>
    <style>
        .erro {
            color: red;
            font-size: 14px;
        }

        .sucesso {
            color: green;
            font-size: 16px;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Atualiza descrição do item ao selecionar código
            document.getElementById("cod_item").addEventListener("change", function() {
                var selectedItem = this.options[this.selectedIndex];
                document.getElementById("den_item").value = selectedItem.getAttribute("data-descricao") || "";
            });

            // Valida o campo de preço
            document.getElementById("pre_unitario").addEventListener("input", function() {
                var valor = this.value;
                if (!/^\d*([,.]\d{0,6})?$/.test(valor)) {
                    this.setCustomValidity("Digite apenas números, ',' ou '.' para os centavos.");
                } else {
                    this.setCustomValidity("");
                }
            });

            // Substitui ',' por '.' no campo de preço antes do envio do formulário
            document.getElementById("form_item").addEventListener("submit", function(event) {
                var preco = document.getElementById("pre_unitario").value;
                if (!/^\d*([,.]\d{0,6})?$/.test(preco)) {
                    event.preventDefault();
                    alert("Formato inválido! Use apenas números e ',' ou '.' para os centavos.");
                    return false;
                }
                document.getElementById("pre_unitario").value = preco.replace(",", ".");
            });
        });
    </script>
</head>

<body>

    <h2>Controlar Item</h2>

    <?php if (isset($_SESSION['mensagem'])): ?>
        <p class="sucesso"><?= $_SESSION['mensagem'] ?></p>
        <?php unset($_SESSION['mensagem']); ?>
    <?php endif; ?>


    <form id="form_item" action="controlar_item_pedido.php" method="POST">

        <label for="num_pedido">Número do Pedido:</label>
        <input type="text" id="num_pedido" name="num_pedido" value="<?= htmlspecialchars($num_pedido) ?>" readonly><br><br>

        <label for="num_seq_item">Número de Sequência de Item:</label>
        <input type="text" id="num_seq_item" name="num_seq_item" value="<?= htmlspecialchars($num_seq_item) ?>" readonly><br><br>

        <label for="cod_item">Código do Item:</label>
        <select id="cod_item" name="cod_item" required>
            <option value="">Selecione um item</option>
            <?php foreach ($itens as $item): ?>
                <option value="<?= $item['cod_item'] ?>" data-descricao="<?= htmlspecialchars($item['den_item']) ?>"
                    <?= (isset($_POST['cod_item']) && $_POST['cod_item'] == $item['cod_item']) || ($item['cod_item'] == $cod_item) ? 'selected' : '' ?>>
                    <?= $item['cod_item'] ?> - <?= htmlspecialchars($item['den_item']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="qtd_solicitada">Quantidade Solicitada:</label>
        <input type="number" id="qtd_solicitada" name="qtd_solicitada" value="<?= isset($_POST['qtd_solicitada']) ? htmlspecialchars($_POST['qtd_solicitada']) : htmlspecialchars($qtd_solicitada) ?>" required><br><br>

        <label for="pre_unitario">Preço Unitário:</label>
        <input type="text" id="pre_unitario" name="pre_unitario" value="<?= isset($_POST['pre_unitario']) ? htmlspecialchars($_POST['pre_unitario']) : htmlspecialchars($pre_unitario) ?>" required><br><br>

        <input type="submit" value="Salvar">
    </form>
    <a href="gerenciar_pedidos.php"><button>Voltar</button></a>
</body>

</html>