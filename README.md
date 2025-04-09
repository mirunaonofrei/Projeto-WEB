# 📦 Sistema de Pedidos Web

Sistema web simples para gerenciamento de pedidos, clientes e itens, com interface interativa utilizando [EasyUI jQuery](https://www.jeasyui.com/).

## 🖥️ Visão Geral

Este sistema permite cadastrar, editar, listar e excluir pedidos de clientes, com visualização detalhada dos itens de cada pedido. 
A interface é construída com componentes visuais responsivos da EasyUI, facilitando a interação do usuário.

### Funcionalidades:

- 📋 Gerenciamento de pedidos
- 👤 Gerenciamento de clientes
- 📝 Gerenciamento de itens
- 🧾 Gerenciamento de itens de pedido

## 🚀 Tecnologias Utilizadas

- **Frontend**:
  - HTML5 + CSS3
  - [EasyUI jQuery](https://www.jeasyui.com/)
  - JavaScript + jQuery

- **Backend**:
  - PHP
  - MySQL 
  - Ajax para requisições assíncronas

 ## 📁 Estrutura de Arquivos

```bash

├── buscar_dados_datagrid.php                # Retorna Pedidos e seus respectivos Clientes
├── buscar_itens.php                         # Retorna Itens (código e descrição do item)
├── db.php                                   # Faz a conexão com o banco de dados
├── gerenciar_pedidos.php                    # **Arquivo principal**
├── index.php                                # Indicador do arquivo principal
├── item_adicionar.php                       # Adicionar Item no Pedido
├── item_editar.php                          # Editar Item no Pedido
├── item_excluir.php                         # Exclui Item no Pedido
├── item_pedido.php                          # Tabela de itens de cada Pedido
├── itens_gerenciar.php                      # Gerencia Itens (código e descrição)
├── itens_remover.php                        # Remove Itens (código e descrição)
├── pedido_adicionar.php                     # Adiciona Pedidos
├── pedido_editar.php                        # Edita Pedidos
├── pedido_excluir.php                       # Exclui Pedidos
```

## 📷 Exemplo da Tela Principal
![image](https://github.com/user-attachments/assets/ea949f6b-8925-42eb-8ef0-52cc52307ff7)

## ⚙️ Como Rodar Localmente
1. Clone este repositório:
```bash
git clone https://github.com/mirunaonofrei/Projeto-WEB.git
cd Projeto-WEB
```
2. Configure seu ambiente PHP + MySQL (XAMPP, WAMP, etc)
3. Crie o banco de dados e importe os arquivos .sql (se houver).
4. Configure os dados de conexão com o banco (no arquivo db.php).
5. Acesse via navegador: 
```bash
http://localhost/Projeto-WEB/gerenciar_pedidos.php
```

## 📄 Licença
Este projeto é livre para uso acadêmico. Caso utilize, cite este repositório.

Desenvolvido com ❤️ por Miruna
 
