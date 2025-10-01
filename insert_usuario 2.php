<?php
require_once 'conexao 2.php';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
    
    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos obrigatórios!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Por favor, insira um email válido!";
    } else {
        try {
            $senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO tb_usuario (nome, email, senha, telefone)
                    VALUES (:nome, :email, :senha, :telefone)";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha', $senha_criptografada);
            $stmt->bindParam(':telefone', $telefone);
            
            if ($stmt->execute()) {
                $sucesso = "Usuário cadastrado com sucesso!";
                
                $nome = $email = $senha = $telefone = '';
            }
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $erro = "Este email já está cadastrado!";
            } else {
                $erro = "Erro ao cadastrar usuário: " . $e->getMessage();
            }
        }
    }
}
?>
 
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuários</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 500px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;
        }
        .btn {
            background-color: #007bff; color: white; padding: 10px 20px;
            border: none; border-radius: 4px; cursor: pointer;
        }
        .btn:hover { background-color: #0056b3; }
        .erro { color: red; margin-bottom: 15px; }
        .sucesso { color: green; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cadastro de Usuários</h1>
        
        <?php if (isset($erro)): ?>
            <div class="erro"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <?php if (isset($sucesso)): ?>
            <div class="sucesso"><?php echo $sucesso; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="nome">Nome *</label>
                <input type="text" id="nome" name="nome" value="<?php echo isset($nome) ? $nome : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="senha">Senha *</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" value="<?php echo isset($telefone) ? $telefone : ''; ?>">
            </div>
            
            <button type="submit" class="btn">Cadastrar Usuário</button>
        </form>
        
        <hr>
        <h2>Usuários Cadastrados</h2>
        <?php
        try {
            $sql_lista = "SELECT id, nome, email, telefone, data_cadastro FROM tb_usuario ORDER BY data_cadastro DESC";
            $stmt_lista = $pdo->query($sql_lista);
            
            if ($stmt_lista->rowCount() > 0) {
                echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
                echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Telefone</th><th>Data Cadastro</th></tr>";
                
                while ($row = $stmt_lista->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['telefone']) . "</td>";
                    echo "<td>" . $row['data_cadastro'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Nenhum usuário cadastrado ainda.</p>";
            }
            
        } catch (PDOException $e) {
            echo "Erro ao listar usuários: " . $e->getMessage();
        }
        ?>
    </div>
</body>
</html>