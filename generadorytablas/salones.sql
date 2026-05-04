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

-- Volcando estructura para tabla escuela.salones
CREATE TABLE IF NOT EXISTS `salones` (
  `id_salones` int NOT NULL AUTO_INCREMENT,
  `piso` tinyint NOT NULL,
  `numero` tinyint NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `capacidad` int NOT NULL,
  `corriente` varchar(50) NOT NULL,
  `televisor` varchar(50) NOT NULL,
  `pizarron` varchar(50) NOT NULL,
  `ubicacion` varchar(50) NOT NULL,
  PRIMARY KEY (`id_salones`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb3;

-- Volcando datos para la tabla escuela.salones: ~42 rows (aproximadamente)
REPLACE INTO `salones` (`id_salones`, `piso`, `numero`, `tipo`, `capacidad`, `corriente`, `televisor`, `pizarron`, `ubicacion`) VALUES
	(1, 0, 1, 'AULA', 34, 'NO', 'NO', 'SI', 'PB'),
	(2, 0, 2, 'AULA', 34, 'NO', 'NO', 'SI', 'PB'),
	(3, 0, 3, 'AULA', 34, 'NO', 'NO', 'SI', 'PB'),
	(4, 0, 4, 'AULA', 34, 'NO', 'NO', 'SI', 'PB'),
	(5, 0, 5, 'AULA TALLER ELECTRICIDAD', 15, 'SI', 'SI', 'SI', 'PB'),
	(6, 0, 6, 'AULA TALLER MECANICA  A', 28, 'SI', 'NO', 'PIZARRA', 'PB'),
	(7, 0, 7, 'AULA', 44, 'NO', 'NO', 'SI', 'PB'),
	(8, 0, 8, 'AULA', 38, 'NO', 'NO', 'SI', 'PB'),
	(9, 0, 9, 'AULA TALLER CARPINT. A', 16, 'SI', 'NO', 'SI', 'PB'),
	(10, 0, 10, 'AULA', 38, 'SI', 'NO', 'SI', 'PB'),
	(11, 0, 11, 'AULA TALLER INSTALAC. A', 40, 'SI', 'NO', 'SI', 'PB'),
	(12, 0, 12, 'AULA', 30, 'SI', 'NO', 'SI', 'PB'),
	(13, 0, 13, 'AULA', 30, 'SI', 'NO', 'SI', 'PB'),
	(14, 0, 14, 'AULA TALLER (POZO)', 30, 'SI', 'NO', 'SI', 'PB'),
	(15, 1, 15, 'AULA TALLER (MORGUE)', 15, 'SI', 'NO', 'SI', 'PB'),
	(17, 1, 17, 'AULA TALLER (CINEMA)', 0, '', '', '', ''),
	(18, 1, 18, 'AULA TALLER (TURISMO)', 20, 'SI', 'SI', 'SI', 'PA'),
	(19, 1, 19, 'AULA TALLER (SALVADO)', 20, 'SI', 'SI', 'PIZARRA', 'PA'),
	(20, 1, 20, 'AULA (APLICACIONES)', 17, 'SI', 'NO', 'PIZARRA', 'PA'),
	(21, 1, 21, 'AULA', 37, 'SI', 'NO', 'SI', 'PA'),
	(22, 1, 22, 'AULA', 40, 'NO', 'NO', 'SI', 'PA'),
	(23, 1, 23, 'AULA', 34, 'NO', 'NO', 'SI', 'PA'),
	(24, 1, 24, 'AULA', 30, 'NO', 'NO', 'SI', 'PA'),
	(25, 1, 25, 'AULA TALLER DIBUJO A', 16, 'SI', 'NO', 'SI', 'PA'),
	(26, 1, 26, 'AULA TALLER', 16, 'SI', 'NO', 'SI', 'PA'),
	(27, 1, 27, 'AULA', 30, 'SI', 'NO', 'PIZARRA', 'PA'),
	(28, 1, 28, 'AULA', 30, 'NO', 'NO', 'SI', 'PA'),
	(29, 1, 29, 'AULA', 30, 'SI', 'NO', 'SI', 'PA'),
	(30, 1, 30, 'AULA', 30, 'SI', 'NO', 'SI', 'PA'),
	(31, 1, 31, 'AULA TALLER S.O.', 14, 'SI', 'NO', 'PIZARRA', 'PA'),
	(32, 1, 32, 'AULA TALLER HARDWARE', 16, 'SI', 'NO', 'PIZARRA', 'PA'),
	(33, 1, 33, 'AULA TALLER', 24, 'SI', 'NO', 'SI', 'PA'),
	(34, 1, 34, 'AULA TALLER', 28, 'SI', 'NO', 'PIZARRA', 'PA'),
	(37, 0, 37, 'GIMNASIO A', 0, '', '', '', ''),
	(39, 0, 9, 'AULA TALLER CARPINT. B', 16, 'SI', 'NO', 'PIZARRA', 'PB'),
	(40, 0, 11, 'AULA TALLER INSTALAC. B', 20, 'SI', 'NO', 'SI', 'PB'),
	(41, 1, 35, 'AULA TALLER ', 20, 'SI', 'NO', 'PIZARRA', ''),
	(43, 1, 25, 'AULA TALLER DIBUJO B', 16, 'SI', 'NO', 'SI', 'PA'),
	(46, 1, 30, 'AULA TALLER BIBLIO', 20, 'NO', 'SI', 'SI', 'PA'),
	(51, 0, 37, 'GIMNASIO B', 0, '', '', '', ''),
	(52, 0, 6, 'AULA Â TALLER MECANICA B', 15, 'SI', 'NO', 'NO', 'PB'),
	(58, 0, 0, 'AULA', 0, '', '', '', '');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
