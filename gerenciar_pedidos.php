<?php

$servername = "localhost";
$username = "root";
$password = "12simple36";
$dbname = "projeto_teste";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

$sql = "SELECT pedido.num_pedido, cliente.nom_cliente 
        FROM pedido 
        INNER JOIN cliente ON pedido.cod_cliente = cliente.cod_cliente
        ORDER BY pedido.num_pedido";

$stmt = $conn->prepare($sql);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

foreach ($pedidos as &$pedido) {
    $pedido['acoes'] = "<a href='controlar_pedido.php?num_pedido={$pedido['num_pedido']}' >[Modificar Pedido]</a> 
                        <a href='excluir_pedido.php?num_pedido={$pedido['num_pedido']}' onclick='return confirm(\"Tem certeza que deseja excluir este pedido?\")'>[Excluir Pedido]</a> 
                        <a href='controlar_item_pedido.php?num_pedido={$pedido['num_pedido']}'>[Incluir Item]</a>";
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Pedidos - Expand Row</title>
    <link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/default/easyui.css">
    <link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/icon.css">
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/jquery.min.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/datagrid-detailview.js"></script>
    <style>
        body {
            background-color: #f4f4f4;
        }

        .container {
            text-align: center;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .buttons {
            display: flex;
            justify-content: right;
            align-items: right;
            flex-direction: row;
            margin-bottom: 15px;
            margin-top: 15px;
        }

        .buttons a {
            margin: 5px;
        }
    </style>
</head>

<body>
    <div class="buttons">
        <a href="controlar_pedido.php" class="easyui-linkbutton" data-options="iconCls:'icon-add'">Pedido</a>
        <a href="gerenciar_item.php" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart'">Gerenciar Itens</a>
        <a href="gerenciar_cliente.php" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart'">Gerenciar Clientes</a>
    </div>
    <div class="container">
        <table id="dg" class="easyui-datagrid" style="width:800px;height:400px;"
            data-options="singleSelect:true, fitColumns:true, view:detailview">
            <thead>
                <tr>
                    <th data-options="field:'num_pedido', width:80">Pedido</th>
                    <th data-options="field:'nom_cliente', width:200">Cliente</th>
                    <th data-options="field:'acoes', width:250">Ações do Pedido</th>
                </tr>
            </thead>
        </table>
    </div>

    <script type="text/javascript">
        $(function() {
            $('#dg').datagrid({
                detailFormatter: function(index, row) {
                    return '<div class="ddv" style="padding:5px 0"></div>';
                },
                onExpandRow: function(index, row) {
                    var ddv = $(this).datagrid('getRowDetail', index).find('div.ddv');
                    ddv.panel({
                        border: false,
                        cache: false,
                        href: 'get_itens_pedido.php?num_pedido=' + row.num_pedido,
                        onLoad: function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }
                    });
                    $('#dg').datagrid('fixDetailRowHeight', index);
                }
            });

            // Carregar os pedidos via AJAX
            $('#dg').datagrid('loadData', <?= json_encode($pedidos) ?>);
        });
    </script>

</body>

</html>