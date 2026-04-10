<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aulaprog');

function getConexao() {
    // funcao que conecta no mysql e cria um banco se nao existir, e cria tabelas se nao existirem
    $con = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
    if (!$con) {
        die("Falha na conexão ao servidor MySQL: " . mysqli_connect_error());
    }

   
    $sqlCreateDB = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if (!mysqli_query($con, $sqlCreateDB)) {
        die("Erro ao criar banco de dados: " . mysqli_error($con));
    }

   
    if (!mysqli_select_db($con, DB_NAME)) {
        die("Erro ao selecionar banco: " . mysqli_error($con));
    }

   
    mysqli_set_charset($con, 'utf8mb4');

   
    $sqlDonos = "CREATE TABLE IF NOT EXISTS donos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        telefone VARCHAR(30),
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $sqlAutomoveis = "CREATE TABLE IF NOT EXISTS automoveis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        dono_id INT NOT NULL,
        modelo VARCHAR(100) NOT NULL,
        ano YEAR,
        preco DECIMAL(10,2) DEFAULT 0.00,
        quantidade INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (dono_id) REFERENCES donos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!mysqli_query($con, $sqlDonos)) {
        die("Erro ao criar tabela 'donos': " . mysqli_error($con));
    }
    if (!mysqli_query($con, $sqlAutomoveis)) {
        die("Erro ao criar tabela 'automoveis': " . mysqli_error($con));
    }

    return $con;
}
?>
