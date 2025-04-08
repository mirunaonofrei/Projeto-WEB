<?php
require_once 'db.php';

$cod_item = isset($_GET["cod_item"]) ? intval($_GET["cod_item"]) : 0;
$den_item = "";

// Se estiver editando um item, preenche os dados dele
if ($cod_item > 0) {
    $stmt = $conn->prepare("SELECT * FROM item WHERE cod_item = :cod_item");
    $stmt->bindParam(':cod_item', $cod_item, PDO::PARAM_INT);
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $den_item = $item['den_item'];
    }
} else {
    // Se for um novo item, gera um novo código
    $stmt = $conn->prepare("SELECT COALESCE(MAX(cod_item), 0) + 1 AS novo_cod_item FROM item");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cod_item = $result['novo_cod_item']; 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cod_item = intval($_POST["cod_item"]);
    $den_item = trim($_POST["den_item"]);

    try {
        $stmt = $conn->prepare("SELECT * FROM item WHERE cod_item = :cod_item");
        $stmt->bindParam(':cod_item', $cod_item, PDO::PARAM_INT);
        $stmt->execute();
        $item = $stmt->fetch();

        if (!$item) {
            // Inserir novo item
            $stmt = $conn->prepare("INSERT INTO item (cod_item, den_item) VALUES (:cod_item, :den_item)");
            $_SESSION['mensagem'] = "item '$den_item' inserido com sucesso!";
        } else {
            // Atualizar item existente
            $stmt = $conn->prepare("UPDATE item SET den_item = :den_item WHERE cod_item = :cod_item");
            $_SESSION['mensagem'] = "item '$den_item' atualizado com sucesso!";
        }
        
        $stmt->bindParam(':cod_item', $cod_item, PDO::PARAM_INT);
        $stmt->bindParam(':den_item', $den_item);
        $stmt->execute();

        header("Location: controlar_item.php?cod_item=$cod_item");
        exit();
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro ao inserir ou atualizar o item: " . $e->getMessage();
        header("Location: controlar_item.php?cod_item=$cod_item");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controlar Item</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/easyui@1.10.3/themes/default/easyui.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/easyui@1.10.3/themes/icon.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery-easyui@1.10.3/jquery.easyui.min.js"></script>
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
</head>
<body>

<h2>Controlar Item</h2>

<!-- Exibir mensagem de sucesso ou erro -->
<?php if (isset($_SESSION['mensagem'])): ?>
    <p class="sucesso"><?php echo $_SESSION['mensagem']; ?></p>
    <?php unset($_SESSION['mensagem']); ?>
<?php endif; ?>

<form action="controlar_item.php" method="POST" id="form_controlar_item">
    <div style="margin-bottom:10px">
        <label for="cod_item">Código do item:</label>
        <input class="easyui-textbox" type="text" id="cod_item" name="cod_item" value="<?php echo htmlspecialchars($cod_item); ?>" readonly style="width:100%;" />
    </div>

    <div style="margin-bottom:10px">
        <label for="den_item">Nome do item:</label>
        <input class="easyui-textbox" type="text" id="den_item" name="den_item" value="<?php echo htmlspecialchars($den_item); ?>" required style="width:100%;" />
    </div>

    <div style="text-align:center; padding:10px;">
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="$('#form_controlar_item').submit()">Salvar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-cancel'" onclick="window.location.href='gerenciar_item.php'">Cancelar</a>
    </div>
</form>

</body>
</html>
