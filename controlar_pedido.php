<?php

require_once 'db.php';

//session_start();


// Recupera todos os clientes
$stmt = $conn->prepare("SELECT cod_cliente, nom_cliente FROM cliente");
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$num_pedido = isset($_GET["num_pedido"]) ? $_GET["num_pedido"] : "";

// Se existir um num_pedido na URL, carrega os dados do pedido
if ($num_pedido != "") {
    $stmt = $conn->prepare("SELECT * FROM pedido WHERE num_pedido = :num_pedido");
    $stmt->bindParam(':num_pedido', $num_pedido);
    $stmt->execute();
    $pedido = $stmt->fetch();
    $cod_cliente = $pedido ? $pedido['cod_cliente'] : "";
} else {
    // Se não existir num_pedido, gera o próximo número de pedido
    $stmt = $conn->prepare("SELECT (COALESCE(MAX(num_pedido), 0) + 1) AS num_pedido FROM pedido");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $num_pedido = $result['num_pedido'];
    $cod_cliente = "";
}

// Processa o envio do formulário via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cod_cliente = $_POST["cod_cliente"];
    $num_pedido = $_POST["num_pedido"];

    try {
        // Verifica se o pedido já existe
        $stmt = $conn->prepare("SELECT * FROM pedido WHERE num_pedido = :num_pedido");
        $stmt->bindParam(':num_pedido', $num_pedido);
        $stmt->execute();
        $pedido = $stmt->fetch();

        if (!$pedido) {
            // Se o pedido não existir, insere um novo
            $stmt = $conn->prepare("INSERT INTO pedido (num_pedido, cod_cliente) VALUES (:num_pedido, :cod_cliente)");
            $stmt->bindParam(':num_pedido', $num_pedido);
            $stmt->bindParam(':cod_cliente', $cod_cliente);
            $stmt->execute();

            echo "<div class='mensagem sucesso'>Pedido inserido com sucesso!</div>";
        } else {
            // Se o pedido já existir, atualiza o cliente
            $stmt = $conn->prepare("UPDATE pedido SET cod_cliente = :cod_cliente WHERE num_pedido = :num_pedido");
            $stmt->bindParam(':num_pedido', $num_pedido);
            $stmt->bindParam(':cod_cliente', $cod_cliente);
            $stmt->execute();

            echo "<div class='mensagem sucesso'>Pedido atualizado com sucesso!</div>";
        }
    } catch (PDOException $e) {
        // Exibe mensagem de erro
        echo "<div class='mensagem erro'>Erro ao inserir ou atualizar o pedido: " . $e->getMessage() . "</div>";
    }
    exit(); // Evita que o código abaixo seja executado após o envio via AJAX
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controlar Pedido</title>
    <link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/default/easyui.css">
    <link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/icon.css">
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/jquery.min.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/jquery.easyui.min.js"></script>

    <style>
        .mensagem {
            font-size: 16px;
            padding: 5px;
            text-align: center;
            margin-bottom: 10px;
        }

        .sucesso {
            color: green;
        }

        .erro {
            color: red;
        }
    </style>
</head>

<body>

    <div class="easyui-panel" title="Controlar Pedido" style="width:400px;padding:20px;">

        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="mensagem <?php echo strpos($_SESSION['mensagem'], 'Erro') !== false ? 'erro' : 'sucesso'; ?>">
                <?php echo $_SESSION['mensagem']; ?>
            </div>
            <?php unset($_SESSION['mensagem']); ?>
        <?php endif; ?>

        <form action="controlar_pedido.php" method="POST">
            <div style="margin-bottom:10px">
                <input class="easyui-textbox" label="Número do Pedido:" name="num_pedido" value="<?php echo htmlspecialchars($num_pedido); ?>" readonly style="width:100%;">
            </div>

            <div style="margin-bottom:10px">
                <select class="easyui-combobox" label="Cliente:" name="cod_cliente" required style="width:100%;">
                    <option value="">Selecione um cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?php echo $cliente['cod_cliente']; ?>" <?php echo ($cliente['cod_cliente'] == $cod_cliente) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cliente['nom_cliente']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="text-align:center">
                <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-back'">Voltar</a>
                <input type="submit" class="easyui-linkbutton" data-options="iconCls:'icon-save'" value="Salvar">
            </div>
        </form>

    </div>
    <!-- Caixa de Diálogo de Confirmação -->
    <div id="confirmationDialog" class="easyui-window" title="Confirmação" data-options="iconCls:'icon-ok',closed:true,modal:true" style="width:300px;height:150px;padding:10px;">
        <div id="confirmationMessage" style="text-align:center; font-size:16px;"></div>
        <div style="text-align:center; margin-top:20px;">
            <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-ok'">OK</a>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            $("form").submit(function(e) {
                e.preventDefault(); // Impede o envio tradicional do formulário

                var formData = $(this).serialize(); // Coleta os dados do formulário

                $.ajax({
                    url: 'controlar_pedido.php', // Envia os dados para controlar_pedido.php
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        // Exibe a resposta de sucesso ou erro
                        if (response.includes('sucesso')) {
                            // Exibe a caixa de diálogo de confirmação
                            $('#confirmationMessage').text('Pedido inserido/atualizado com sucesso!');
                            $('#confirmationDialog').window('open'); // Abre a caixa de diálogo

                            // Se o pedido foi inserido ou atualizado com sucesso, recarrega a tabela
                            $('#dg').datagrid('reload'); // Atualiza a tabela de pedidos
                        } else {
                            // Exibe a mensagem de erro na própria janela
                            $('#win').html(response);
                        }
                    }
                });
            });
        });
    </script>

</body>

</html>