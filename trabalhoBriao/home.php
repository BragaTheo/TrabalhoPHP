<?php

require_once 'conexao.php';
$con = getConexao();
// conecta ao banco
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'd.nome';
$ordem = isset($_GET['ordem']) && strtoupper($_GET['ordem']) === 'DESC' ? 'DESC' : 'ASC';
// deixa buscar por nome, modelo e ordenar a tabela
$colunas_validas = ['d.nome','a.modelo','a.preco','a.ano'];
// evita sql injection
if (!in_array($ordenar, $colunas_validas)) {
    $ordenar = 'd.nome';
}

$sql = "SELECT a.id AS auto_id, a.modelo, a.ano, a.preco, a.quantidade, d.id AS dono_id, d.nome, d.telefone, d.email
        FROM automoveis a
        JOIN donos d ON a.dono_id = d.id";
// junta carro com dono
$params = [];
if ($busca !== '') {
    $sql .= " WHERE d.nome LIKE ? OR a.modelo LIKE ?";
    $like = '%' . $busca . '%';
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY $ordenar $ordem";
// ordena
$stmt = mysqli_prepare($con, $sql);
if ($stmt === false) {
    die("Erro na preparação da consulta: " . mysqli_error($con));
}
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Listagem - Donos e Automóveis</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <div class="container">
    <h2>Listagem de Automóveis (com Dono)</h2>

    <form method="get" class="form-inline">
      <input type="text" name="busca" placeholder="Buscar por dono ou modelo" value="<?php echo htmlspecialchars($busca); ?>">
      <select name="ordenar">
        <option value="d.nome" <?php if($ordenar=='d.nome') echo 'selected'; ?>>Dono</option>
        <option value="a.modelo" <?php if($ordenar=='a.modelo') echo 'selected'; ?>>Modelo</option>
        <option value="a.preco" <?php if($ordenar=='a.preco') echo 'selected'; ?>>Preço</option>
        <option value="a.ano" <?php if($ordenar=='a.ano') echo 'selected'; ?>>Ano</option>
      </select>
      <select name="ordem">
        <option value="ASC" <?php if($ordem=='ASC') echo 'selected'; ?>>Crescente</option>
        <option value="DESC" <?php if($ordem=='DESC') echo 'selected'; ?>>Decrescente</option>
      </select>
      <button type="submit">Pesquisar / Ordenar</button>
      <a class="btn-link" href="index.php">Voltar</a>
    </form>

    <table>
      <thead>
        <tr>
          <th>Dono</th>
          <th>Modelo</th>
          <th>Ano</th>
          <th>Preço</th>
          <th>Quantidade</th>
          <th>Total (qtd × preço)</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
          <?php while($row = mysqli_fetch_assoc($result)): ?>
    <!-- tabela html que mostra dono, modelo, ano, preço, etc -->
            <tr>
              <td><?php echo htmlspecialchars($row['nome']); ?></td>
              <td><?php echo htmlspecialchars($row['modelo']); ?></td>
              <td><?php echo htmlspecialchars($row['ano']); ?></td>
              <td>R$ <?php echo number_format($row['preco'], 2, ',', '.'); ?></td> 
              <td><?php echo (int)$row['quantidade']; ?></td>
              <td>R$ <?php echo number_format($row['preco'] * $row['quantidade'], 2, ',', '.'); ?></td>
              <td> 
                <a href="processamento.php?action=edit_auto&id=<?php echo $row['auto_id']; ?>">Editar</a> |
                <a href="processamento.php?action=delete_auto&id=<?php echo $row['auto_id']; ?>" onclick="return confirm('Excluir automóvel?');">Excluir</a> |
                <a href="processamento.php?action=edit_dono&id=<?php echo $row['dono_id']; ?>">Editar Dono</a>
                  <!-- chama processamento.php pra executar essas acoes -->
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7">Nenhum registro encontrado.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <h3>Resumo por Dono</h3>
    <?php
    $sqlResumo = "SELECT d.id, d.nome, SUM(a.preco * a.quantidade) AS total_valor
                  FROM donos d
                  LEFT JOIN automoveis a ON a.dono_id = d.id
                  GROUP BY d.id, d.nome";
// quanto cada dono tem em carros
    $resResumo = mysqli_query($con, $sqlResumo);
    ?>
    <table>
      <thead>
        <tr><th>Dono</th><th>Total (R$)</th></tr>
      </thead>
      <tbody>
        <?php if ($resResumo && mysqli_num_rows($resResumo) > 0): ?>
          <?php while($r = mysqli_fetch_assoc($resResumo)): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['nome']); ?></td>
              <td>R$ <?php echo number_format($r['total_valor'] ?? 0, 2, ',', '.'); ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="2">Nenhum dono cadastrado.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

  </div>
</body>
</html>
<?php
mysqli_stmt_close($stmt);
mysqli_close($con);
?>
