-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: biblioteca_paginario
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `administrador`
--
drop database biblioteca_paginario;
create database biblioteca_paginario;
use biblioteca_paginario;

DROP TABLE IF EXISTS `administrador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `administrador` (
  `cpf_administrador` char(11) NOT NULL,
  `nome_completo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(11) DEFAULT NULL,
  `login` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  PRIMARY KEY (`cpf_administrador`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

-- Inserir dados de administradores
-- Senha: 123 (hash gerado com password_hash())
INSERT INTO administrador (cpf_administrador, nome_completo, email, telefone, login, senha) 
VALUES ('11111111111', 'Geovana Alves', 'geovanaalves@gmail.com', '11999999999', 'geovana', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Administrador adicional - Login: admin, Senha: admin123
INSERT INTO administrador (cpf_administrador, nome_completo, email, telefone, login, senha) 
VALUES ('98765432100', 'Administrador Sistema', 'admin@paginario.com', '11888888888', 'admin', '$2y$10$e0MYzXyjpJS7Pd0i9ubqOOTkn/8b5KN8C4F9GkWE.q8LcleAUlqiu');

--
-- Table structure for table `autor`
--

select * from usuarios;

DROP TABLE IF EXISTS `autor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `autor` (
  `id_autor` int NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(100) NOT NULL,
  `nacionalidade` varchar(100) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `biografia` text,
  PRIMARY KEY (`id_autor`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `editora`
--

DROP TABLE IF EXISTS `editora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `editora` (
  `id_editora` int NOT NULL AUTO_INCREMENT,
  `nome_editora` varchar(100) NOT NULL,
  `cnpj` varchar(14) NOT NULL,
  `telefone` varchar(11) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`id_editora`),
  UNIQUE KEY `cnpj` (`cnpj`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `endereco_editora`
--

DROP TABLE IF EXISTS `endereco_editora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `endereco_editora` (
  `id_endereco` int NOT NULL AUTO_INCREMENT,
  `id_editora` int DEFAULT NULL,
  `rua` varchar(100) NOT NULL,
  `numero` int NOT NULL,
  `bairro` varchar(100) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  PRIMARY KEY (`id_endereco`),
  KEY `id_editora` (`id_editora`),
  CONSTRAINT `endereco_editora_ibfk_1` FOREIGN KEY (`id_editora`) REFERENCES `editora` (`id_editora`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `endereco_usuario`
--

DROP TABLE IF EXISTS `endereco_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `endereco_usuario` (
  `cpf_usuario` char(11) NOT NULL,
  `rua` varchar(100) NOT NULL,
  `numero` int NOT NULL,
  `bairro` varchar(100) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  PRIMARY KEY (`cpf_usuario`),
  CONSTRAINT `endereco_usuario_ibfk_1` FOREIGN KEY (`cpf_usuario`) REFERENCES `usuario` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `envio_livro`
--

DROP TABLE IF EXISTS `envio_livro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `envio_livro` (
  `id_envio` int NOT NULL AUTO_INCREMENT,
  `data_envio` date DEFAULT NULL,
  `estado` varchar(100) DEFAULT NULL,
  `livro_id` int DEFAULT NULL,
  `autor_id` int DEFAULT NULL,
  PRIMARY KEY (`id_envio`),
  KEY `livro_id` (`livro_id`),
  KEY `autor_id` (`autor_id`),
  CONSTRAINT `envio_livro_ibfk_1` FOREIGN KEY (`livro_id`) REFERENCES `livro` (`id_livro`),
  CONSTRAINT `envio_livro_ibfk_2` FOREIGN KEY (`autor_id`) REFERENCES `autor` (`id_autor`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genero`
--

DROP TABLE IF EXISTS `genero`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `genero` (
  `id_genero` int NOT NULL AUTO_INCREMENT,
  `nome_genero` varchar(100) NOT NULL,
  PRIMARY KEY (`id_genero`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `le`
--

DROP TABLE IF EXISTS `le`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `le` (
  `id_leitura` int NOT NULL AUTO_INCREMENT,
  `cpf_usuario` char(11) NOT NULL,
  `id_livro` int NOT NULL,
  `data_leitura` date DEFAULT NULL,
  PRIMARY KEY (`id_leitura`),
  KEY `cpf_usuario` (`cpf_usuario`),
  KEY `id_livro` (`id_livro`),
  CONSTRAINT `le_ibfk_1` FOREIGN KEY (`cpf_usuario`) REFERENCES `usuario` (`cpf`),
  CONSTRAINT `le_ibfk_2` FOREIGN KEY (`id_livro`) REFERENCES `livro` (`id_livro`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `livro`
--

DROP TABLE IF EXISTS `livro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `livro` (
  `id_livro` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `autor` varchar(100) NOT NULL,
  `ano_publicacao` int DEFAULT NULL,
  `editor` varchar(100) DEFAULT NULL,
  `genero` varchar(100) NOT NULL,
  `formato` varchar(100) DEFAULT NULL,
  `link_arquivo` varchar(100) DEFAULT NULL,
  `sinopse` varchar(100) NOT NULL,
  `classificacao_indicativa` int NOT NULL,
  `genero_id` int DEFAULT NULL,
  `cpf_administrador` char(11) DEFAULT NULL,
  `capa` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_livro`),
  UNIQUE KEY `link_arquivo` (`link_arquivo`),
  KEY `genero_id` (`genero_id`),
  KEY `cpf_administrador` (`cpf_administrador`),
  CONSTRAINT `livro_ibfk_1` FOREIGN KEY (`genero_id`) REFERENCES `genero` (`id_genero`),
  CONSTRAINT `livro_ibfk_2` FOREIGN KEY (`cpf_administrador`) REFERENCES `administrador` (`cpf_administrador`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

select * from livro;

--
-- Table structure for table `publica`
--

DROP TABLE IF EXISTS `publica`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `publica` (
  `id_publicacao` int NOT NULL AUTO_INCREMENT,
  `id_editora` int DEFAULT NULL,
  `id_livro` int DEFAULT NULL,
  `data_publicacao` date DEFAULT NULL,
  PRIMARY KEY (`id_publicacao`),
  KEY `id_editora` (`id_editora`),
  KEY `id_livro` (`id_livro`),
  CONSTRAINT `publica_ibfk_1` FOREIGN KEY (`id_editora`) REFERENCES `editora` (`id_editora`),
  CONSTRAINT `publica_ibfk_2` FOREIGN KEY (`id_livro`) REFERENCES `livro` (`id_livro`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `solicitacao`
--

DROP TABLE IF EXISTS `solicitacao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitacao` (
  `id_solicitacao` int NOT NULL AUTO_INCREMENT,
  `nome_livro` varchar(255) NOT NULL,
  `nome_autor` varchar(255) NOT NULL,
  `sinopse` text NOT NULL,
  `indicativo_etario` varchar(50) NOT NULL,
  `cpf` char(11) NOT NULL,
  PRIMARY KEY (`id_solicitacao`),
  KEY `fk_solicitacao_usuario` (`cpf`),
  CONSTRAINT `fk_solicitacao_usuario` FOREIGN KEY (`cpf`) REFERENCES `usuario` (`cpf`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `cpf` char(11) NOT NULL,
  `nome_completo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(11) DEFAULT NULL,
  `login` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `id_acesso` int DEFAULT NULL,
  PRIMARY KEY (`cpf`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'biblioteca_paginario'
--

--
-- Dumping routines for database 'biblioteca_paginario'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-15 19:07:46
