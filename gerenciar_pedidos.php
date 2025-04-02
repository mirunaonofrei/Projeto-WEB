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
    <div id="p" class="easyui-panel" title="Pedidos" style="width:100%;height:100%;padding:10px; background-color:rgb(210, 229, 255);" data-options="footer:'#ft'">
        <table id="dg" class="easyui-datagrid" style="height:500px; width: 100%;"
            data-options="
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
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="adicionar()">Adicionar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="remover()">Remover</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="editar()">Editar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="cancelar()">Cancelar</a>
    </div>
    <div id="ft" class="buttons">
        <button id="gerenciar_itens" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart'">Gerenciar Itens</button>
        <button id="gerenciar_clientes" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart'">Gerenciar Clientes</button>
    </div>

    <!-- 
    <div id="win" class="easyui-window" title="" style="width: 300px; height: 150px; padding: 10px;" data-options="modal:true,closed:true">
        <div style="text-align: center;">
        </div> 
    </div>
    -->



</body>

</html>


<script type="text/javascript">
    $(function() { // só executa depois que a pagina carregar
        $('#dg').datagrid({ // seleciona d tabela com id = "dg"
            detailFormatter: function(index, row) { // define o conteudo das linhas que expandem, passa como parametro o indez da linha e os dados dela
                return '<div class="ddv" style="padding:5px 0"></div>'; //
            },
            onExpandRow: function(index, row) { 
                var ddv = $(this).datagrid('getRowDetail', index).find('div.ddv'); // ddv armazena o elemento div class="ddv"
                ddv.panel({ // converte a div em um panel EasyUI e carrega os dadosdo pedido usando AJAX
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

    //var editIndex = undefined; ---- ver se da pra tirar

    function adicionar() {
        $.get('adicionar_pedido.php', function(num_pedido) {
        $('#numPedido').textbox('setValue', num_pedido);
        $('#dlgPedido').dialog('open');
    });
    }

    function remover() {
        var pedidoToDelete = null; // Variável para armazenar o pedido a ser excluído
        var row = $('#dg').datagrid('getSelected'); // Seleciona a linha do pedido
        if (row) {
            $.messager.confirm({
                title: 'Exclusão',
                msg: 'Tem certeza que deseja excluir esse pedido?',
                fn: function(r) {
                    if (r) {
                        pedidoToDelete = row; // Armazena o pedido selecionado
                        var num_pedido = pedidoToDelete.num_pedido;
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
                                        $('#dg').datagrid('reload');
                                    });
                                }

                                $.messager.alert('Erro no processamento', jsonResponse.msg, 'error', function(r) {
                                    $('#dg').datagrid('reload');
                                });
                            })
                    }
                }
            });
        } else {
            $.messager.alert('Atenção', 'Selecione um pedido para ser excluído!', 'info');
            $('#dg').datagrid('reload');

        }
    }

    function editar() {
        let num_pedido = $(this).data("num_pedido");
        if (num_pedido == undefined) {
            return
        }
        $('#win').window('refresh', 'controlar_pedido.php?num_pedido=' + num_pedido);
        $('#win').window('open');
    }

    function cancelar() {
        $('#dg').datagrid('cancelarChanges');
        editIndex = undefined;
    }



    $('#win').window({
        width: 600,
        height: 400,
        modal: true,
        closed: true // Garante que a janela inicie fechada
    });
</script>