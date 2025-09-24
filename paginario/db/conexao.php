<?php 
$dsn = "mysql:host=localhost;dbname=biblioteca_paginario";
$conexao = null;

try{
    $conexao = new PDO($dsn, "root", "1234");
    $conexao -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $erro){
    print "Erro de conexão: " . $erro -> getMessage();
    die();
}

?>