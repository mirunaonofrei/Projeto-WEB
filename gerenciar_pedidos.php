<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>PEDIDOS</title>
    <link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/default/easyui.css">
    <link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/icon.css">
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/jquery.min.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/datagrid-detailview.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/src/jquery.window.js"></script>
    <style>
        body {
            background-color: rgb(224, 237, 255);
        }

        .buttons {
            display: flex;
            flex-direction: row;
            justify-content: flex-end;
            background-color: rgb(155, 198, 255);
        }

        .datagrid-header,
        .datagrid-htable {
            background-color: rgb(155, 198, 255) !important;
        }
    </style>
</head>

<body>
    <div id="ft" class="buttons">
        <button id="gerenciar_itens" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart'">Gerenciar Itens</button>
        <button id="gerenciar_clientes" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart'">Gerenciar Clientes</button>
    </div>
    <div id="p" class="easyui-panel" title="Pedidos" style="width:100%;height:100%;padding:10px; background-color:rgb(210, 229, 255);" data-options="footer:'#ft'">
        <table id="dg" class="easyui-datagrid" style="height:500px; width: 100%;"
            data-options="
                        iconCls: 'icon-edit',
                        singleSelect:true,
                        fit:true, 
                        fitColumns:true, 
                        view:detailview,
                        footer:'#ft_dg',
                        url: 'buscar_dados_datagrid.php'">
            <thead>
                <tr>
                    <th data-options="field:'num_pedido', width:'30%'">Pedido</th>
                    <th data-options="field:'nom_cliente', width:'70%'">Cliente</th>
                </tr>
            </thead>
        </table>


    </div>

    <div id="ft_dg" style="height:auto; background-color:rgb(155, 198, 255);">
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="append()">Adicionar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeit()">Remover</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="edit()">Editar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="reject()">Cancelar</a>
    </div>


    <div id="win" class="easyui-window" title="Confirmar Exclusão" style="width: 300px; height: 150px; padding: 10px;" data-options="modal:true,closed:true">
        <p>Tem certeza que deseja excluir este pedido?</p>
        <div style="text-align: center;">
            <a href="javascript:void(0)" class="easyui-linkbutton" onclick="confirmDelete()">Sim</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" onclick="closeWin()">Não</a>
        </div>
    </div>

</body>

</html>


<script type="text/javascript">
    $(function() {
        $('#dg').datagrid({
            detailFormatter: function(index, row) {
                return '<div class="ddv" style="padding:5px 0"></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('div.ddv');
                ddv.panel({
                    href: 'get_itens_pedido.php?num_pedido=' + row.num_pedido,
                    border: false,
                    cache: false,
                    onLoad: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    }
                });
                setTimeout(function() {
                    $('#dg').datagrid('fixDetailRowHeight', index);
                }, 0);
            }
        });
        $('#dg').datagrid('loadData');
    });

    var editIndex = undefined;

    function append() {
        $('#win').window('refresh', 'controlar_pedido.php');
        $('#win').window('open');
    }
    var pedidoToDelete = null; // Variável para armazenar o pedido a ser excluído

    function removeit() {
        var row = $('#dg').datagrid('getSelected'); // Seleciona a linha do pedido
        if (row) {
            pedidoToDelete = row; // Armazena o pedido selecionado
            $('#win').window('open'); // Abre a janela de confirmação
        } else {
            alert("Por favor, selecione um pedido para excluir.");
        }
    }

    function confirmDelete() {

        if (pedidoToDelete) {
            var num_pedido = pedidoToDelete.num_pedido; // Obtém o número do pedido

            $.ajax({
                    url: 'excluir_pedido.php',
                    type: 'GET',
                    data: {
                        num_pedido: num_pedido
                    }
                })
                .done(function(response) { // Quando a requisição for bem-sucedida
                    let jsonResponse = JSON.parse(response);

                    if (jsonResponse.status) {
                        return $.messager.alert('Processamento Executado', jsonResponse.msg, 'info', function(r) {
                            $('#win').window('close');
                            $('#dg').datagrid('reload');
                        });
                    }

                    $.messager.alert('Erro no processamento', jsonResponse.msg, 'error', function(r) {
                        $('#win').window('close');
                        $('#dg').datagrid('reload');
                    });
                })
        }
    }


    function edit() {
        let num_pedido = $(this).data("num_pedido");
        if (num_pedido == undefined) {
            return
        }
        $('#win').window('refresh', 'controlar_pedido.php?num_pedido=' + num_pedido);
        $('#win').window('open');
    }

    function reject() {
        $('#dg').datagrid('rejectChanges');
        editIndex = undefined;
    }



    $('#win').window({
        width: 600,
        height: 400,
        modal: true,
        closed: true // Garante que a janela inicie fechada
    });
</script>