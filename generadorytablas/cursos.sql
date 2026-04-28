-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para escuela
CREATE DATABASE IF NOT EXISTS `escuela` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `escuela`;

-- Volcando estructura para tabla escuela.cursos
CREATE TABLE IF NOT EXISTS `cursos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `division` varchar(1) NOT NULL,
  `ano` tinyint NOT NULL,
  `turno` varchar(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb3;

-- Volcando datos para la tabla escuela.cursos: ~44 rows (aproximadamente)
REPLACE INTO `cursos` (`id`, `division`, `ano`, `turno`) VALUES
	(1, 'A', 1, 'M'),
	(2, 'B', 1, 'M'),
	(3, 'C', 1, 'T'),
	(4, 'D', 1, 'M'),
	(5, 'E', 1, 'M'),
	(6, 'F', 1, 'T'),
	(7, 'A', 2, 'M'),
	(8, 'B', 2, 'M'),
	(9, 'C', 2, 'T'),
	(10, 'D', 2, 'M'),
	(11, 'E', 2, 'T'),
	(12, 'F', 2, 'M'),
	(13, 'A', 3, 'M'),
	(14, 'B', 3, 'M'),
	(15, 'C', 3, 'T'),
	(16, 'E', 3, 'M'),
	(17, 'A', 4, 'T'),
	(18, 'B', 4, 'M'),
	(19, 'C', 4, 'M'),
	(20, 'E', 4, 'M'),
	(21, 'G', 4, 'M'),
	(22, 'A', 5, 'V'),
	(23, 'B', 5, 'T'),
	(24, 'C', 5, 'V'),
	(25, 'E', 5, 'V'),
	(26, 'H', 5, 'T'),
	(27, 'A', 6, 'V'),
	(28, 'B', 6, 'T'),
	(29, 'C', 6, 'V'),
	(30, 'E', 6, 'V'),
	(33, 'H', 6, 'V'),
	(34, 'B', 7, 'V'),
	(36, 'H', 7, 'V'),
	(37, 'A', 7, 'V'),
	(38, 'G', 2, 'M'),
	(39, 'E', 7, 'V'),
	(40, 'G', 1, 'M'),
	(41, 'D', 3, 'M'),
	(42, 'D', 4, 'T'),
	(43, 'C', 7, 'V'),
	(44, 'F', 3, 'T'),
	(45, 'G', 3, 'T'),
	(46, 'F', 7, 'T'),
	(47, 'P', 1, '');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
