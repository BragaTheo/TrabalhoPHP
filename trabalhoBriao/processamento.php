<?php

require_once 'conexao.php';
$con = getConexao();
$action = isset($_GET['action']) ? $_GET['action'] : '';

function redirect($url) {
    header("Location: $url");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'form_dono') {
        ?>
        <!doctype html>
        <html lang="pt-BR">
        <head><meta charset="utf-8"><title>Cadastrar Dono</title><link rel="stylesheet" href="estilo.css"></head>
        <body>
          <div class="container">
            <h2>Cadastrar Dono</h2>
            <form method="post" action="processamento.php?action=save_dono">
              <label>Nome</label>
              <input type="text" name="nome" required>
              <label>Telefone</label>
              <input type="text" name="telefone">
              <label>Email</label>
              <input type="email" name="email">
              <button type="submit">Salvar</button>
              <a class="btn-link" href="index.php">Cancelar</a>
            </form>
          </div>
        </body>
        </html>
        <?php
        exit;
    }

    if ($action === 'form_auto') {
        $donos = mysqli_query($con, "SELECT id, nome FROM donos ORDER BY nome");
        ?>
        <!doctype html>
        <html lang="pt-BR">
        <head><meta charset="utf-8"><title>Cadastrar Automóvel</title><link rel="stylesheet" href="estilo.css"></head>
        <body>
          <div class="container">
            <h2>Cadastrar Automóvel</h2>
            <form method="post" action="processamento.php?action=save_auto">
              <label>Dono</label>
              <select name="dono_id" required>
                <option value="">-- Escolha um dono --</option>
                <?php while($d = mysqli_fetch_assoc($donos)): ?>
                  <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['nome']); ?></option>
                <?php endwhile; ?>
              </select>
              <label>Modelo</label>
              <input type="text" name="modelo" required>
              <label>Ano</label>
              <input type="number" name="ano" min="1900" max="<?php echo date('Y'); ?>">
              <label>Preço</label>
              <input type="number" step="0.01" name="preco" value="0.00">
              <label>Quantidade</label>
              <input type="number" name="quantidade" value="1" min="1">
              <button type="submit">Salvar</button>
              <a class="btn-link" href="index.php">Cancelar</a>
            </form>
          </div>
        </body>
        </html>
        <?php
        exit;
    }

    if ($action === 'edit_dono' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $res = mysqli_query($con, "SELECT * FROM donos WHERE id = $id");
        $dono = mysqli_fetch_assoc($res);
        if (!$dono) redirect('home.php');
        ?>
        <!doctype html>
        <html lang="pt-BR">
        <head><meta charset="utf-8"><title>Editar Dono</title><link rel="stylesheet" href="estilo.css"></head>
        <body>
          <div class="container">
            <h2>Editar Dono</h2>
            <form method="post" action="processamento.php?action=update_dono">
              <input type="hidden" name="id" value="<?php echo $dono['id']; ?>">
              <label>Nome</label>
              <input type="text" name="nome" value="<?php echo htmlspecialchars($dono['nome']); ?>" required>
              <label>Telefone</label>
              <input type="text" name="telefone" value="<?php echo htmlspecialchars($dono['telefone']); ?>">
              <label>Email</label>
              <input type="email" name="email" value="<?php echo htmlspecialchars($dono['email']); ?>">
              <button type="submit">Atualizar</button>
              <a class="btn-link" href="home.php">Cancelar</a>
            </form>
          </div>
        </body>
        </html>
        <?php
        exit;
    }

    if ($action === 'edit_auto' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $res = mysqli_query($con, "SELECT * FROM automoveis WHERE id = $id");
        $auto = mysqli_fetch_assoc($res);
        if (!$auto) redirect('home.php');
        $donos = mysqli_query($con, "SELECT id, nome FROM donos ORDER BY nome");
        ?>
        <!doctype html>
        <html lang="pt-BR">
        <head><meta charset="utf-8"><title>Editar Automóvel</title><link rel="stylesheet" href="estilo.css"></head>
        <body>
          <div class="container">
            <h2>Editar Automóvel</h2>
            <form method="post" action="processamento.php?action=update_auto">
              <input type="hidden" name="id" value="<?php echo $auto['id']; ?>">
              <label>Dono</label>
              <select name="dono_id" required>
                <?php while($d = mysqli_fetch_assoc($donos)): ?>
                  <option value="<?php echo $d['id']; ?>" <?php if($d['id']==$auto['dono_id']) echo 'selected'; ?>><?php echo htmlspecialchars($d['nome']); ?></option>
                <?php endwhile; ?>
              </select>
              <label>Modelo</label>
              <input type="text" name="modelo" value="<?php echo htmlspecialchars($auto['modelo']); ?>" required>
              <label>Ano</label>
              <input type="number" name="ano" value="<?php echo htmlspecialchars($auto['ano']); ?>">
              <label>Preço</label>
              <input type="number" step="0.01" name="preco" value="<?php echo htmlspecialchars($auto['preco']); ?>">
              <label>Quantidade</label>
              <input type="number" name="quantidade" value="<?php echo htmlspecialchars($auto['quantidade']); ?>">
              <button type="submit">Atualizar</button>
              <a class="btn-link" href="home.php">Cancelar</a>
            </form>
          </div>
        </body>
        </html>
        <?php
        exit;
    }

    if ($action === 'delete_auto' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = mysqli_prepare($con, "DELETE FROM automoveis WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        redirect('home.php');
    }

    if ($action === 'delete_dono' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = mysqli_prepare($con, "DELETE FROM donos WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        redirect('home.php');
    }

    redirect('home.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'save_dono') {
        $nome = trim($_POST['nome']);
        $telefone = trim($_POST['telefone']);
        $email = trim($_POST['email']);

        $stmt = mysqli_prepare($con, "INSERT INTO donos (nome, telefone, email) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $nome, $telefone, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        redirect('home.php');
    }

    if ($action === 'update_dono') {
        $id = (int)$_POST['id'];
        $nome = trim($_POST['nome']);
        $telefone = trim($_POST['telefone']);
        $email = trim($_POST['email']);

        $stmt = mysqli_prepare($con, "UPDATE donos SET nome = ?, telefone = ?, email = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'sssi', $nome, $telefone, $email, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        redirect('home.php');
    }

    if ($action === 'save_auto') {
        $dono_id = (int)$_POST['dono_id'];
        $modelo = trim($_POST['modelo']);
        $ano = !empty($_POST['ano']) ? (int)$_POST['ano'] : null;
        $preco = isset($_POST['preco']) ? (float)$_POST['preco'] : 0.00;
        $quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 1;

        $stmt = mysqli_prepare($con, "INSERT INTO automoveis (dono_id, modelo, ano, preco, quantidade) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isidi', $dono_id, $modelo, $ano, $preco, $quantidade);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        redirect('home.php');
    }

    if ($action === 'update_auto') {
        $id = (int)$_POST['id'];
        $dono_id = (int)$_POST['dono_id'];
        $modelo = trim($_POST['modelo']);
        $ano = !empty($_POST['ano']) ? (int)$_POST['ano'] : null;
        $preco = isset($_POST['preco']) ? (float)$_POST['preco'] : 0.00;
        $quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 1;

        $stmt = mysqli_prepare($con, "UPDATE automoveis SET dono_id = ?, modelo = ?, ano = ?, preco = ?, quantidade = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'isidii', $dono_id, $modelo, $ano, $preco, $quantidade, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        redirect('home.php');
    }
}

mysqli_close($con);
?>
