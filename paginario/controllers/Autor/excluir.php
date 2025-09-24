<?php
require 'paginario-biblioteca/paginario/controllers/db/conexao.php';

$id_autor = $_GET['id_autor'];

$sql = "DELETE FROM Autor WHERE id_autor = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_autor]);

header('Location: ler.php');
exit();
?>
