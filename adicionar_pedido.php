<?php /////////////////////////////      USAR WINDOW.PROMPT PARA ISSO

require_once 'db.php';

$queryRecuperaClientes = "SELECT cod_cliente, nom_cliente FROM cliente"; // Recupera todos os clientes
$stmt = $conn->prepare($queryRecuperaClientes);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$num_pedido = isset($_GET["num_pedido"]) ? $_GET["num_pedido"] : "";

$queryProxPedido = "SELECT (COALESCE(MAX(num_pedido), 0) + 1) AS num_pedido FROM pedido";
$exeProxPedido = $conn->prepare($queryProxPedido);
$exeProxPedido->execute();
$rowProxPedido = $exeProxPedido->fetch(PDO::FETCH_ASSOC);
$num_pedido =  $rowProxPedido['num_pedido'];
$cod_cliente = "";


// Processa o envio do formulário via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cod_cliente = $_POST["cod_cliente"];
    $num_pedido = $_POST["num_pedido"];

    $queryInserePedido = "INSERT INTO pedido (num_pedido, cod_cliente) VALUES (:num_pedido, :cod_cliente)";
    $stmt = $conn->prepare($queryInserePedido);
    $stmt->bindParam(':num_pedido', $num_pedido);
    $stmt->bindParam(':cod_cliente', $cod_cliente);
    $stmt->execute();

    echo "<div class='mensagem sucesso'>Pedido inserido com sucesso!</div>";
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
            margin-top: 10px;
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

    <div class="easyui-panel" title="Adicionar Pedido" style="width:400px;padding:20px; align-items:center;">

        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="mensagem <?php echo strpos($_SESSION['mensagem'], 'Erro') !== false ? 'erro' : 'sucesso'; ?>">
                <?php echo $_SESSION['mensagem']; ?>
            </div>
            <?php unset($_SESSION['mensagem']); ?>
        <?php endif; ?>
        <form id="ff">
            <div style="margin-bottom:10px">
                <input class="easyui-textbox" label="Número do Pedido:" name="num_pedido" value="<?php echo htmlspecialchars($num_pedido); ?>" readonly style="width:100%;">
            </div>

            <div style="margin-bottom:10px">

            </div>

            <div style="text-align:center">
                <!-- <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-back'">Voltar</a>
                <a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" style="width:80px" onclick="salvarForm()">Salvar</a> -->

                <!-- <input type="submit" class="easyui-linkbutton" data-options="iconCls:'icon-save'" value="Salvar"> -->
            </div>
        </form>

    </div>
    <!-- Caixa de Diálogo de Confirmação 
    <div id="confirmationDialog" class="easyui-window" title="Confirmação" data-options="iconCls:'icon-ok',closed:true,modal:true" style="width:300px;height:150px;padding:10px;">
        <div id="confirmationMessage" style="text-align:center; font-size:16px;"></div>
        <div style="text-align:center; margin-top:20px;">
            <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-ok'">OK</a>
        </div>
    </div> 
    -->


    <script type="text/javascript">
        $(function() {
            $.messager.alert('Inserindo Pedido', 'Pedido inserido/atualizado com sucesso!', 'info', function(r) {
                    //         $('#dg').datagrid('reload');
                      });
        });
        
        function salvarForm() {

            var formData = $(this).serialize();

            $.ajax({
                url: 'salvar_pedido.php', // Envia os dados para controlar_pedido.php
                type: 'POST',
                data: formData,
                success: function(response) {
                    console.log(response)



                    // if (response.includes('sucesso')) {
                    //     // Exibe a caixa de diálogo de confirmação
                    //     $.messager.alert('Processamento Executado', 'Pedido inserido/atualizado com sucesso!', 'info', function(r) {
                    //         $('#dg').datagrid('reload');
                    //     });
                    //     //$('#confirmationMessage').text('Pedido inserido/atualizado com sucesso!');
                    //     //$('#confirmationDialog').window('open'); // Abre a caixa de diálogo

                    //     // Se o pedido foi inserido ou atualizado com sucesso, recarrega a tabela
                    //     $('#dg').datagrid('reload'); // Atualiza a tabela de pedidos
                    // } else {
                    //     // Exibe a mensagem de erro na própria janela
                    //     $.messager.alert('Erro no processamento', 'Erro ao incluir pedido!', 'error', function(r) {
                    //         $('#dg').datagrid('reload');
                    //     });
                    //     //$('#win').html(response);
                    // }
                }
            });
        }
    </script>

</body>

</html>