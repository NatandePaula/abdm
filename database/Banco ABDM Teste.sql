-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: abdm
-- ------------------------------------------------------
-- Server version	5.5.5-10.11.16-MariaDB

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
-- Table structure for table `acolhido`
--

DROP TABLE IF EXISTS `acolhido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acolhido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `cpf` varchar(255) NOT NULL,
  `dataNascimento` date DEFAULT NULL,
  `sexo` varchar(20) DEFAULT NULL,
  `tipoBeneficio` varchar(45) DEFAULT NULL,
  `historico` mediumtext DEFAULT NULL,
  `motivoInstitucionalizacao` text DEFAULT NULL,
  `dataAcolhimento` date DEFAULT NULL,
  `origemAcolhimento` varchar(45) DEFAULT NULL,
  `matricula` varchar(12) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'ativo',
  `dataDesligamento` date DEFAULT NULL,
  `motivoDesligamento` text DEFAULT NULL,
  `curador_id` int(11) DEFAULT NULL,
  `nome_mae` varchar(255) DEFAULT NULL,
  `nome_pai` varchar(255) DEFAULT NULL,
  `tipo_documento` varchar(50) DEFAULT NULL,
  `documento_identificacao` varchar(1024) DEFAULT NULL,
  `cartao_municipal_saude` varchar(1024) DEFAULT NULL,
  `cartao_sus` varchar(1024) DEFAULT NULL,
  `plano_assistencial_funerario` text DEFAULT NULL,
  `info_saude` text DEFAULT NULL,
  `info_nutri` text DEFAULT NULL,
  `endereco_referencia` varchar(255) DEFAULT NULL,
  `endereco_observacao` text DEFAULT NULL,
  `dados_cadastrais_json` longtext DEFAULT NULL,
  `dados_complementares_json` longtext DEFAULT NULL,
  `Endereco_idEndereco` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_acolhido_curador` (`curador_id`),
  KEY `fk_acolhido_Endereco1` (`Endereco_idEndereco`),
  CONSTRAINT `fk_acolhido_Endereco1` FOREIGN KEY (`Endereco_idEndereco`) REFERENCES `endereco` (`idEndereco`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_acolhido_curador` FOREIGN KEY (`curador_id`) REFERENCES `curador` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acolhido`
--

LOCK TABLES `acolhido` WRITE;
/*!40000 ALTER TABLE `acolhido` DISABLE KEYS */;
INSERT INTO `acolhido` VALUES (1,'teste acolhido1','Z1dNbDdycWY','1999-01-01',NULL,'BPC','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer blandit lobortis nisl, elementum rhoncus lectus bibendum a. Vivamus urna ipsum, tincidunt ut consequat id, sodales a massa. Morbi a consequat dui. Etiam sit amet pharetra nisl. Duis convallis ut arcu ultrices maximus. Nullam consequatLorem ipsum dolor sit amet,Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer blandit lobortis nisl, elementum rhoncus lectus bibendum a. Vivamus urna ipsum, tincidunt ut consequat id, sodales a massa. Morbi a consequat dui. Etiam sit amet pharetra nisl. Duis convallis ut arcu ultrices maximus. Nullam consequatLorem ipsum dolor sit amet,\r\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Integer blandit lobortis nisl, elementum rhoncus lectus bibendum a. Vivamus urna ipsum, tincidunt ut consequat id, sodales a massa. Morbi a consequat dui. Etiam sit amet pharetra nisl. Duis convallis ut arcu ultrices maximus. Nullam consequatLorem ipsum dolor sit amet,','creasLorem ipsum dolor sit amet, consectetur adipiscing elit. Integer blandit lobortis nisl, elementum rhoncus lectus bibendum a. Vivamus urna ipsum, tincidunt ut consequat id, sodales a massa. Morbi a consequat dui. Etiam sit amet pharetra nisl. Duis convallis ut arcu ultrices maximus. Nullam consequatLorem ipsum dolor sit amet, consectetur adipiscing elit. Integer blandit lobortis nisl, elementum rhoncus lectus bibendum a. Vivamus urna ipsum, tincidunt ut consequat id, sodales a massa. Morbi a consequat dui. Etiam sit amet pharetra nisl. Duis convallis ut arcu ultrices maximus. Nullam consequat','2025-03-25','CREAS','2026032517','desligado','2026-10-14','hghagdhagdh',NULL,NULL,NULL,'RG','ck4ydHFOZGxRVUFtZTE2cG5Vb0liZz09OjoP6NpcQEMn+pv4PxH8o1/4',NULL,NULL,NULL,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer blandit lobortis nisl, elementum rhoncus lectus bibendum a. Vivamus urna ipsum, tincidunt ut consequat id, sodales a massa. Morbi a consequat dui. Etiam sit amet pharetra nisl. Duis convallis ut arcu ultrices maximus. Nullam consequat','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer blandit lobortis nisl, elementum rhoncus lectus bibendum a. Vivamus urna ipsum, tincidunt ut consequat id, sodales a massa. Morbi a consequat dui. Etiam sit amet pharetra nisl. Duis convallis ut arcu ultrices maximus. Nullam consequatLorem ipsum dolor sit amet, consectetur adipiscing elit. Integer blandit lobortis nisl, elementum',NULL,NULL,NULL,NULL,1),(2,'João Silva','Y3BIclR4OW85Tm4yNzVIaGpCdktHQT09Ojozu9OiiHQVarn96ufbSBFA','1985-07-12','masculino',NULL,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer blandit lobortis nisl, elementum rhoncus lectus bibendum a. Vivamus urna ipsum, tincidunt ut consequat id, sodales a massa. Morbi a consequat dui. Etiam sit amet pharetra nisl. Duis convallis ut arcu ultrices maximus. Nullam consequat','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer blandit lobortis nisl, elementum rhoncus lectus bibendum a. Vivamus urna ipsum, tincidunt ut consequat id, sodales a massa. Morbi a consequat dui. Etiam sit amet pharetra nisl. Duis convallis ut arcu ultrices maximus. Nullam consequat','2026-06-11',NULL,'2026061162','desligado','2026-10-14','hghtg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'{\"documentos\":{\"sus\":{\"situacao\":\"\",\"numero\":\"\"},\"certidao\":{\"situacao\":\"\",\"numero\":\"\"},\"rg\":{\"situacao\":\"\",\"numero\":\"\"},\"cpf\":{\"situacao\":\"possui\",\"numero\":\"\"},\"titulo_eleitoral\":{\"situacao\":\"\",\"numero\":\"\"},\"ctps\":{\"situacao\":\"\",\"numero\":\"\"},\"cartao_municipal\":{\"situacao\":\"nao_possui\",\"numero\":\"\"},\"outros\":{\"situacao\":\"\",\"numero\":\"\"}},\"beneficios\":[],\"orientacao_sexual\":\"\",\"orientacao_sexual_outros\":\"\",\"atividade_remunerada\":\"\",\"beneficio_atividade\":\"\",\"beneficio_outro\":\"\",\"outras_fontes_renda\":\"\",\"administra_recursos\":\"\",\"responsavel_recursos\":\"\",\"laudo_medico\":\"\",\"dst\":\"\",\"orteses_proteses\":\"\",\"cuidados_personalizados\":\"\",\"qual_dst\":\"\",\"especialistas_tratamentos\":\"\",\"especialistas_necessarios\":\"\",\"qual_orteses_proteses\":\"\",\"qual_cuidados_personalizados\":\"\",\"envolvimento_outros\":\"\",\"envolvimento_substancias\":[]}',2),(3,'Acolhido teste 2','ZDNDUmdrblhMeHA0OVc3cXdkS0s5dz09OjqNN9L8LNTYmQNL/H2sQgCI','1995-01-01','masculino',NULL,'metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.','metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, hendrerit sit amet dui a, interdum faucibus metus. \r\n\r\nFusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. \r\n- Mauris \r\n- sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, \r\n\r\nhendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.metus, hendrerit sit amet dui a, interdum faucibus metus. Fusce in leo felis. Suspendisse vel ipsum tincidunt, accumsan mi nec, pellentesque erat. Mauris sit amet sem pulvinar, rutrum tellus blandit, euismod nisl.','2025-08-06','CREAS','2026061178','ativo',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'{\"data_nascimento\": \"1995-01-01\", \"cor\": \"\", \"orientacao_sexual\": \"\", \"orientacao_sexual_outros\": \"\", \"estado_civil\": \"\", \"escolaridade\": \"\", \"nacionalidade\": \"\", \"naturalidade\": \"\", \"origem_encaminhamento\": [], \"motivo_acolhimento\": \"\", \"numero_processo\": \"\", \"impedimento_visitacao\": \"\", \"quem_impedido\": \"\", \"beneficios\": [], \"beneficio_atividade\": \"\", \"beneficio_outro\": \"\", \"administra_recursos\": \"\", \"responsavel_recursos\": \"\", \"composicao_familiar\": [], \"documentos\": {\"certidao\": {\"situacao\": \"\", \"numero\": \"\"}, \"rg\": {\"situacao\": \"\", \"numero\": \"\"}, \"cpf\": {\"situacao\": \"\", \"numero\": \"\"}, \"titulo_eleitoral\": {\"situacao\": \"\", \"numero\": \"\"}, \"ctps\": {\"situacao\": \"\", \"numero\": \"\"}, \"sus\": {\"situacao\": \"\", \"numero\": \"\"}, \"outros\": {\"situacao\": \"\", \"numero\": \"\"}}, \"plano_atividades\": {\"convivencia\": \"\", \"desenvolvimento\": \"\", \"documentacao\": \"\", \"esporte_cultura\": \"\", \"geracao_renda\": \"\", \"mobilizacao_rede\": \"\", \"transferencia_renda\": \"\", \"vinculos\": \"\", \"saude\": \"\", \"servicos\": \"\"}, \"ficha_evolucao\": [], \"procedimentos_familia\": \"\", \"procedimentos_acolhido\": \"\", \"rede_servicos\": [], \"observacao_rede\": \"\", \"laudo_medico\": \"\", \"cid10\": \"\", \"dst\": \"\", \"qual_dst\": \"\", \"restricao_alimentar\": \"\", \"qual_restricao\": \"\", \"doencas_cronicas\": \"\", \"especialistas_tratamentos\": \"\", \"especialistas_necessarios\": \"\", \"orteses_proteses\": \"\", \"cuidados_personalizados\": \"\", \"envolvimento_substancias\": [], \"envolvimento_outros\": \"\", \"documentos_familia\": \"\", \"renda_familiar\": \"\", \"institucionalizacao_familiar\": \"\", \"local_institucionalizacao\": \"\", \"encaminhamento_rede\": \"\", \"relacao_familia_acolhido\": \"\", \"familiares_visita\": \"\", \"visita_em_casa\": \"\", \"dificuldades_visita_casa\": \"\", \"frequencia_visitas\": \"\", \"dificuldades_visitas_instituicao\": \"\", \"afinidade\": \"\", \"afinidade_nome\": \"\", \"afinidade_endereco\": \"\", \"risco_convivio\": \"\"}','{\"data_nascimento\":\"1995-01-01\",\"cor\":\"\",\"orientacao_sexual\":\"\",\"orientacao_sexual_outros\":\"\",\"estado_civil\":\"\",\"escolaridade\":\"\",\"nacionalidade\":\"\",\"naturalidade\":\"\",\"origem_encaminhamento\":[],\"motivo_acolhimento\":\"\",\"numero_processo\":\"\",\"impedimento_visitacao\":\"\",\"quem_impedido\":\"\",\"beneficios\":[],\"beneficio_atividade\":\"\",\"beneficio_outro\":\"\",\"administra_recursos\":\"\",\"responsavel_recursos\":\"\",\"composicao_familiar\":[],\"documentos\":{\"sus\":{\"situacao\":\"\",\"numero\":\"\"},\"certidao\":{\"situacao\":\"\",\"numero\":\"\"},\"rg\":{\"situacao\":\"\",\"numero\":\"\"},\"cpf\":{\"situacao\":\"possui\",\"numero\":\"\"},\"titulo_eleitoral\":{\"situacao\":\"\",\"numero\":\"\"},\"ctps\":{\"situacao\":\"\",\"numero\":\"\"},\"cartao_municipal\":{\"situacao\":\"nao_possui\",\"numero\":\"\"},\"outros\":{\"situacao\":\"\",\"numero\":\"\"}},\"plano_atividades\":{\"convivencia\":\"\",\"desenvolvimento\":\"\",\"documentacao\":\"\",\"esporte_cultura\":\"\",\"geracao_renda\":\"\",\"mobilizacao_rede\":\"\",\"transferencia_renda\":\"\",\"vinculos\":\"\",\"saude\":\"\",\"servicos\":\"\"},\"ficha_evolucao\":[],\"procedimentos_familia\":\"\",\"procedimentos_acolhido\":\"\",\"rede_servicos\":[],\"observacao_rede\":\"\",\"laudo_medico\":\"\",\"cid10\":\"\",\"dst\":\"\",\"qual_dst\":\"\",\"restricao_alimentar\":\"\",\"qual_restricao\":\"\",\"doencas_cronicas\":\"\",\"especialistas_tratamentos\":\"\",\"especialistas_necessarios\":\"\",\"orteses_proteses\":\"\",\"cuidados_personalizados\":\"\",\"envolvimento_substancias\":[],\"envolvimento_outros\":\"\",\"documentos_familia\":\"\",\"renda_familiar\":\"\",\"institucionalizacao_familiar\":\"\",\"local_institucionalizacao\":\"\",\"encaminhamento_rede\":\"\",\"relacao_familia_acolhido\":\"\",\"familiares_visita\":\"\",\"visita_em_casa\":\"\",\"dificuldades_visita_casa\":\"\",\"frequencia_visitas\":\"\",\"dificuldades_visitas_instituicao\":\"\",\"afinidade\":\"\",\"afinidade_nome\":\"\",\"afinidade_endereco\":\"\",\"risco_convivio\":\"\",\"atividade_remunerada\":\"\",\"outras_fontes_renda\":\"\",\"qual_orteses_proteses\":\"\",\"qual_cuidados_personalizados\":\"\"}',3),(4,'João da Silva','111.111.111','1980-05-15','masculino','Aposentadoria por idade','Hipertenso e diab??tico tipo 2.','Abandono familiar, sem condi????es de autocuidado.','2022-01-10','Hospital Municipal','2026061273','ativo',NULL,NULL,2,'Joana da Silva','Jos?? da Silva','RG','TmVJcWpZcDBIMXBsRWFNSjh6REJVQT09OjphbghyOg6JrLVNW/e8769s',NULL,'Q0o4aDNlYlcrVjJyR2RqUmVBVkhnQT09OjpokyRNTyJvsXSsb5OkQ9+l',NULL,'Alergia a penicilina.',NULL,NULL,NULL,NULL,NULL,9),(5,'Ana Pereira','333.333.333','1945-11-22','feminino','BPC/LOAS','Apresenta lapsos de mem??ria e desorienta????o espacial.','Vulnerabilidade social e quadro demencial inicial.','2021-03-20','CREAS','2026061231','ativo',NULL,NULL,NULL,'Luzia Pereira','N??o informado','RG','WGIvbWdIM1FlbU1RdnJUZlNFQUlNUT09OjqTo+HI0yQpEMAfUunUzRlQ',NULL,'SkdkWGFiZVJBN3VhWE9MWUpxTkZnUT09Ojra0VdHicqXYV0OapVubJFb',NULL,NULL,'Dieta branda, dificuldade de mastiga????o.',NULL,NULL,NULL,NULL,12),(6,'Carlos Oliveira','MVN4c3oyOVF','1982-02-01','masculino','Aux??lio-doen??a','Hemiplegia ?? direita, necessita de aux??lio para todas as atividades de vida di??ria.','Sequelas de AVC, necessita de cuidados integrais.','2023-06-05','Fam??lia','2026061217','ativo',NULL,NULL,3,'Marta Oliveira','Ant??nio Oliveira','CT','Z2YxRmkzeVowK2Njd0JRdVJaS21rQT09OjpZgZzzbjNXEhffOSc7vyVo',NULL,'bDZjQUZsLzZITXU5YVpMYisrMXpEdz09Ojq8q5AHSiiS+8P9U/tyhDDf',NULL,'Uso cont??nuo de anticoagulante.',NULL,NULL,NULL,NULL,NULL,13),(7,'Josefa Santos','666.666.666','1978-09-30',NULL,NULL,'Hist??rico de etilismo cr??nico.','Situa????o de rua, sem v??nculos familiares.','2020-11-15','Rua','2026061206','ativo',NULL,NULL,NULL,'N??o informado','N??o informado','OUTRO','U1Vqdlp0Q0duc1ljMDRSQkNJaXljUT09OjqV6NixYn6AdqSWNnS3PMUg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,15),(8,'Benedito Costa','777.777.777','1975-07-12','masculino','Aposentadoria por invalidez','Amputa????o transtibial esquerda devido a complica????es de diabetes.','Amputa????o de membro inferior, sem suporte familiar para reabilita????o.','2023-02-28','Hospital','2026061235','ativo',NULL,NULL,NULL,'Tereza Costa','Manoel Costa','RG','OGQyQnBkbnpObkhVbVRCR1U3NlViZz09Ojro8mI6MxtuNOnpWQGzHRpa',NULL,'NmFtUG9iWjhiMXU4R2VUN2V4QUlHdz09Ojrmji8lCKtoqa6tG02rYRde',NULL,'Faz uso de pr??tese na perna esquerda.',NULL,NULL,NULL,NULL,NULL,16),(9,'Davi Benjamin Caleb Sales','NFQ1dS92K0xtQWN1dHJQWmFVdEpEUT09Ojqq9sc65XW1eMnJMMXuoRom','1990-05-15','masculino','BPC/LOAS','orem ipsum tincidunt justo etiam ornare orci nisi nibh tortor ut at, vestibulum mi litora ultrices dictum habitant aptent donec ultricies fames consequat fames, tincidunt aliquet lacinia in eget aenean lacus consectetur posuere praesent. etiam ipsum mi leo metus odio sit hac posuere, etiam ac nulla conubia tortor accumsan imperdiet, class potenti est commodo habitasse venenatis ut. tellus laoreet per curabitur nulla faucibus nisl nullam nostra vulputate elit in ipsum pharetra, commodo cubilia justo eu potenti tempus vitae felis aenean enim nam tempus. ultricies mi aptent malesuada urna tempor eget justo fames, hendrerit augue sit tincidunt quisque semper turpis nisl convallis, semper quam libero curae convallis purus etiam. \r\n\r\n	Faucibus vivamus fringilla iaculis luctus vitae donec viverra netus dui, suspendisse tortor ipsum tellus porttitor commodo morbi ante velit curabitur, euismod quisque dictum etiam curabitur morbi eget velit. suspendisse primis elementum aenean magna tristique torquent lorem orci suscipit netus, diam sapien mollis placerat elit ut scelerisque dui velit primis, tellus aliquet viverra id ut fames ligula mattis cras. et vel nostra proin nostra vel elit imperdiet iaculis, quisque curae suspendisse convallis consequat lobortis aptent ullamcorper, libero etiam urna torquent mattis phasellus elit. quisque laoreet ad sem magna torquent sed cras netus nulla, aliquam felis bibendum aenean porttitor habitasse arcu malesuada, quis etiam class libero at nam malesuada orci.','orem ipsum tincidunt justo etiam ornare orci nisi nibh tortor ut at, vestibulum mi litora ultrices dictum habitant aptent donec ultricies fames consequat fames, tincidunt aliquet lacinia in eget aenean lacus consectetur posuere praesent. etiam ipsum mi leo metus odio sit hac posuere, etiam ac nulla conubia tortor accumsan imperdiet, class potenti est commodo habitasse venenatis ut. tellus laoreet per curabitur nulla faucibus nisl nullam nostra vulputate elit in ipsum pharetra, commodo cubilia justo eu potenti tempus vitae felis aenean enim nam tempus. ultricies mi aptent malesuada urna tempor eget justo fames, hendrerit augue sit tincidunt quisque semper turpis nisl convallis, semper quam libero curae convallis purus etiam.','2025-02-10','CREAS','2026061212','ativo',NULL,NULL,7,'Simone Rosângela','Francisco Geraldo Sales','RG','cEQyMU9VWmJ3bzB3LzUzclF1Umt4Zz09OjpV0TJS7btvjQw/PoND+PS6',NULL,NULL,'Fornecido pela Instiuição durante o acolhimento','Sem dados clinicos','Sem dados nutricionais',NULL,NULL,'{\"cor\": \"parda\", \"orientacao_sexual\": \"heterossexual\", \"orientacao_sexual_outros\": \"\", \"estado_civil\": \"Nao informado - ficha ficticia de demonstracao.\", \"escolaridade\": \"Nao informado - ficha ficticia de demonstracao.\", \"nacionalidade\": \"Nao informado - ficha ficticia de demonstracao.\", \"naturalidade\": \"Nao informado - ficha ficticia de demonstracao.\", \"documentos\": {\"certidao\": {\"situacao\": \"nao_sabe_informar\", \"numero\": \"\"}, \"rg\": {\"situacao\": \"nao_sabe_informar\", \"numero\": \"\"}, \"cpf\": {\"situacao\": \"nao_sabe_informar\", \"numero\": \"\"}, \"titulo_eleitoral\": {\"situacao\": \"nao_sabe_informar\", \"numero\": \"\"}, \"ctps\": {\"situacao\": \"nao_sabe_informar\", \"numero\": \"\"}, \"sus\": {\"situacao\": \"nao_sabe_informar\", \"numero\": \"\"}, \"outros\": {\"situacao\": \"nao_sabe_informar\", \"numero\": \"\"}}, \"beneficio_atividade\": \"Nao informado - ficha ficticia de demonstracao.\", \"beneficio_outro\": \"Nao informado - ficha ficticia de demonstracao.\", \"administra_recursos\": \"n??o\", \"responsavel_recursos\": \"Nao informado - ficha ficticia de demonstracao.\", \"laudo_medico\": \"n??o\", \"dst\": \"n??o\", \"qual_dst\": \"\", \"restricao_alimentar\": \"n??o\", \"qual_restricao\": \"\", \"doencas_cronicas\": \"Sem dados clinicos - registro ficticio.\", \"especialistas_tratamentos\": \"Nao informado - ficha ficticia de demonstracao.\", \"especialistas_necessarios\": \"Nao informado - ficha ficticia de demonstracao.\", \"orteses_proteses\": \"Nao informado - ficha ficticia de demonstracao.\", \"cuidados_personalizados\": \"Nao informado - ficha ficticia de demonstracao.\", \"envolvimento_substancias\": [], \"envolvimento_outros\": \"\", \"documentos_familia\": \"n??o\", \"renda_familiar\": \"Nao informada - registro ficticio.\", \"institucionalizacao_familiar\": \"n??o\", \"local_institucionalizacao\": \"\", \"encaminhamento_rede\": \"Nao informado - ficha ficticia de demonstracao.\", \"relacao_familia_acolhido\": \"Nao informado - ficha ficticia de demonstracao.\", \"familiares_visita\": \"Nao informado - demonstracao.\", \"visita_em_casa\": \"n??o\", \"dificuldades_visita_casa\": \"Nao informado - ficha ficticia de demonstracao.\", \"frequencia_visitas\": \"Nao informada - demonstracao.\", \"dificuldades_visitas_instituicao\": \"Nao informado - ficha ficticia de demonstracao.\", \"afinidade\": \"n??o\", \"afinidade_nome\": \"\", \"afinidade_endereco\": \"\", \"risco_convivio\": \"Nao informado - ficha ficticia de demonstracao.\", \"origem_encaminhamento\": [], \"numero_processo\": \"Nao informado - ficha ficticia de demonstracao.\", \"impedimento_visitacao\": \"n??o\", \"quem_impedido\": \"\", \"rede_servicos\": [], \"observacao_rede\": \"Nao informado - ficha ficticia de demonstracao.\"}','{\"cor\":\"parda\",\"orientacao_sexual\":\"heterossexual\",\"orientacao_sexual_outros\":\"\",\"estado_civil\":\"Nao informado - ficha ficticia de demonstracao.\",\"escolaridade\":\"Nao informado - ficha ficticia de demonstracao.\",\"nacionalidade\":\"Nao informado - ficha ficticia de demonstracao.\",\"naturalidade\":\"Nao informado - ficha ficticia de demonstracao.\",\"documentos\":{\"sus\":{\"situacao\":\"nao_disponibilizado\",\"numero\":\"\"},\"certidao\":{\"situacao\":\"possui\",\"numero\":\"\"},\"rg\":{\"situacao\":\"possui\",\"numero\":\"aCtXcDcrSW1vMFFIUENSUHRjS2ZqUT09OjpW\\/L\\/pmBAE+LBIcZiNlFgS\"},\"cpf\":{\"situacao\":\"possui\",\"numero\":\"\"},\"titulo_eleitoral\":{\"situacao\":\"possui\",\"numero\":\"\"},\"ctps\":{\"situacao\":\"nao_sabe_informar\",\"numero\":\"\"},\"cartao_municipal\":{\"situacao\":\"nao_sabe_informar\",\"numero\":\"\"},\"outros\":{\"situacao\":\"\",\"numero\":\"\"}},\"beneficio_atividade\":\"\",\"beneficio_outro\":\"\",\"administra_recursos\":\"outra_pessoa\",\"responsavel_recursos\":\"Curador\",\"laudo_medico\":\"sim\",\"dst\":\"nao\",\"qual_dst\":\"\",\"restricao_alimentar\":\"n??o\",\"qual_restricao\":\"\",\"doencas_cronicas\":\"Sem dados clinicos - registro ficticio.\",\"especialistas_tratamentos\":\"\",\"especialistas_necessarios\":\"\",\"orteses_proteses\":\"nao\",\"cuidados_personalizados\":\"nao\",\"envolvimento_substancias\":[\"tabaco\"],\"envolvimento_outros\":\"\",\"documentos_familia\":\"n??o\",\"renda_familiar\":\"Nao informada - registro ficticio.\",\"institucionalizacao_familiar\":\"n??o\",\"local_institucionalizacao\":\"\",\"encaminhamento_rede\":\"Nao informado - ficha ficticia de demonstracao.\",\"relacao_familia_acolhido\":\"Nao informado - ficha ficticia de demonstracao.\",\"familiares_visita\":\"Nao informado - demonstracao.\",\"visita_em_casa\":\"n??o\",\"dificuldades_visita_casa\":\"Nao informado - ficha ficticia de demonstracao.\",\"frequencia_visitas\":\"Nao informada - demonstracao.\",\"dificuldades_visitas_instituicao\":\"Nao informado - ficha ficticia de demonstracao.\",\"afinidade\":\"n??o\",\"afinidade_nome\":\"\",\"afinidade_endereco\":\"\",\"risco_convivio\":\"Nao informado - ficha ficticia de demonstracao.\",\"origem_encaminhamento\":[],\"numero_processo\":\"Nao informado - ficha ficticia de demonstracao.\",\"impedimento_visitacao\":\"n??o\",\"quem_impedido\":\"\",\"rede_servicos\":[],\"observacao_rede\":\"Nao informado - ficha ficticia de demonstracao.\",\"beneficios\":[\"bpc\"],\"atividade_remunerada\":\"nao\",\"outras_fontes_renda\":\"\",\"qual_orteses_proteses\":\"\",\"qual_cuidados_personalizados\":\"\"}',25),(10,'André Gabriel Ryan Galvão','QUZzVm9OMWtaTEd2bXk1QkVUa2RqZz09OjrM9oewK+LoFSONJQEwc0Qt','1989-04-05','masculino','BPC/LOAS','orem ipsum dolor sit amet, consectetur adipiscing elit. Etiam volutpat, erat quis mattis mattis, lacus elit dictum felis, non suscipit dolor dui nec diam. Phasellus bibendum gravida lacinia. Sed quis facilisis velit, quis commodo velit. Vivamus posuere id odio in porttitor. In eu felis ac sem blandit mollis sed at risus. orem ipsum dolor sit amet, consectetur adipiscing elit. Etiam volutpat, erat quis mattis mattis, lacus elit dictum felis, non suscipit dolor dui nec diam. Phasellus bibendum gravida lacinia. Sed quis facilisis velit, quis commodo velit. Vivamus posuere id odio in porttitor. In eu felis ac sem blandit mollis sed at risus. orem ipsum dolor sit amet, consectetur adipiscing elit. Etiam volutpat, erat quis mattis mattis, lacus elit dictum felis, non suscipit dolor dui nec diam. Phasellus bibendum gravida lacinia. Sed quis facilisis velit, quis commodo velit. Vivamus posuere id odio in porttitor. In eu felis ac sem blandit mollis sed at risus. orem ipsum dolor sit amet, consectetur adipiscing elit. Etiam volutpat, erat quis mattis mattis, lacus elit dictum felis, non suscipit dolor dui nec diam. Phasellus bibendum gravida lacinia. Sed quis facilisis velit, quis commodo velit. Vivamus posuere id odio in porttitor. In eu felis ac sem blandit mollis sed at risus. orem ipsum dolor sit amet, consectetur adipiscing elit. Etiam volutpat, erat quis mattis mattis, lacus elit dictum felis, non suscipit dolor dui nec diam. Phasellus bibendum gravida lacinia. Sed quis facilisis velit, quis commodo velit. Vivamus posuere id odio in porttitor. In eu felis ac sem blandit mollis sed at risus.','orem ipsum dolor sit amet, consectetur adipiscing elit. Etiam volutpat, erat quis mattis mattis, lacus elit dictum felis, non suscipit dolor dui nec diam. Phasellus bibendum gravida lacinia. Sed quis facilisis velit, quis commodo velit. Vivamus posuere id odio in porttitor. In eu felis ac sem blandit mollis sed at risus.','2026-10-14','CREAS','2026101424','ativo',NULL,NULL,NULL,'Valentina Esther','Fernando Yago Galv??o',NULL,NULL,NULL,NULL,'Plano Zelo - Fornecido Pela Institui????o durante o acolhimento','orem ipsum dolor sit amet, consectetur adipiscing elit. Etiam volutpat, erat quis mattis mattis, lacus elit dictum felis, non suscipit dolor dui nec diam. Phasellus bibendum gravida lacinia. Sed quis facilisis velit, quis commodo velit. Vivamus posuere id odio in porttitor. In eu felis ac sem blandit mollis sed at risus.','orem ipsum dolor sit amet, consectetur adipiscing elit. Etiam volutpat, erat quis mattis mattis, lacus elit dictum felis, non suscipit dolor dui nec diam. Phasellus bibendum gravida lacinia. Sed quis facilisis velit, quis commodo velit. Vivamus posuere id odio in porttitor. In eu felis ac sem blandit mollis sed at risus. orem ipsum dolor sit amet, consectetur adipiscing elit. Etiam volutpat, erat quis mattis mattis, lacus elit dictum felis, non suscipit dolor dui nec diam. Phasellus bibendum gravida lacinia. Sed quis facilisis velit, quis commodo velit. Vivamus posuere id odio in porttitor. In eu felis ac sem blandit mollis sed at risus.','Casa',NULL,NULL,'{\"documentos\":{\"sus\":{\"situacao\":\"nao_disponibilizado\",\"numero\":\"\"},\"certidao\":{\"situacao\":\"possui\",\"numero\":\"\"},\"rg\":{\"situacao\":\"possui\",\"numero\":\"\"},\"cpf\":{\"situacao\":\"possui\",\"numero\":\"\"},\"titulo_eleitoral\":{\"situacao\":\"nao_sabe_informar\",\"numero\":\"\"},\"ctps\":{\"situacao\":\"nao_disponibilizado\",\"numero\":\"\"},\"cartao_municipal\":{\"situacao\":\"nao_possui\",\"numero\":\"\"},\"outros\":{\"situacao\":\"\",\"numero\":\"\"}},\"orientacao_sexual\":\"heterossexual\",\"orientacao_sexual_outros\":\"\",\"beneficios\":[\"bpc\"],\"atividade_remunerada\":\"nao\",\"beneficio_atividade\":\"\",\"beneficio_outro\":\"\",\"outras_fontes_renda\":\"\",\"administra_recursos\":\"outra_pessoa\",\"responsavel_recursos\":\"Curadora\",\"laudo_medico\":\"sim\",\"dst\":\"nao\",\"orteses_proteses\":\"nao\",\"cuidados_personalizados\":\"nao\",\"qual_dst\":\"\",\"especialistas_tratamentos\":\"Psiquiatra e Neuro\",\"especialistas_necessarios\":\"Acompanhamento Psiquiatrico\",\"qual_orteses_proteses\":\"\",\"qual_cuidados_personalizados\":\"\",\"envolvimento_outros\":\"\",\"envolvimento_substancias\":[\"alcool\",\"tabaco\"]}',31);
/*!40000 ALTER TABLE `acolhido` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendario_configuracao`
--

DROP TABLE IF EXISTS `calendario_configuracao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendario_configuracao` (
  `id` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `url` text DEFAULT NULL,
  `atualizado_por_login` int(11) DEFAULT NULL,
  `atualizado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_calendario_config_login` (`atualizado_por_login`),
  CONSTRAINT `fk_calendario_config_login` FOREIGN KEY (`atualizado_por_login`) REFERENCES `login` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendario_configuracao`
--

LOCK TABLES `calendario_configuracao` WRITE;
/*!40000 ALTER TABLE `calendario_configuracao` DISABLE KEYS */;
INSERT INTO `calendario_configuracao` VALUES (1,'https://calendar.google.com/calendar/embed?height=1900&wkst=1&ctz=America%2FSao_Paulo&showPrint=0&hl=pt_BR&src=cmVzaWRlbmNpYWluY2x1c2l2YWFiZG1AZ21haWwuY29t&src=MDk2Yjc2Zjk1NTU0MTBkZjdiNmE1NGUwYzRmNzVjYWRlNWI2ZWI2ZDY5ZTRlOGNiOWMzNmRkZjhiMmNlZTJhNkBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&src=ODNjZTIxMWY3N2NmNTE5YTEyNWZiOTdjM2U0OWQ4NmEzMDAxNjk5ZjUwZTlmOWIzZmM2ODdlYWFhYzQ2ZWEwNUBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&src=Y2VjYzVmMmIzOGU5YmJjYzE3ODRjZmQzNWUwMDVmOGNiZWEwNDlmMDRiZTBhOTllNjJkMGMxYmQ4MjU0YzlmM0Bncm91cC5jYWxlbmRhci5nb29nbGUuY29t&src=NzZkNzIyN2JkMDc2YjE3NWI2NDRhNzNiOTkzNzViYjRjNDI4OGI1MTE2Y2ZjZDVlNzNjYzg4Y2YzNTBmMzE4YUBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&src=N2I1Njg5MTczMDE4MzFmZjNkN2Q4MGRhMzcyMzdkNmE2ZWI5MWU4MWExN2Y1NTc3ZmI2MGYyMTUzNTMyODViM0Bncm91cC5jYWxlbmRhci5nb29nbGUuY29t&src=ZTQ0ODRjMjBiYzZjMWZhNzhiNWFhYjNjMWQ1ZDM3YTgyMmUxYWI5M2VkNGI5ZmQ0YmMwN2NlYjg5M2NhMTY0YUBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&src=YWRkcmVzc2Jvb2sjY29udGFjdHNAZ3JvdXAudi5jYWxlbmRhci5nb29nbGUuY29t&src=cHQuYnJhemlsaWFuI2hvbGlkYXlAZ3JvdXAudi5jYWxlbmRhci5nb29nbGUuY29t&color=%23039BE5&color=%23616161&color=%237CB342&color=%23F4511E&color=%23AD1457&color=%238E24AA&color=%23795548&color=%2333B679&color=%230B8043',1,'2026-11-22 18:44:41');
/*!40000 ALTER TABLE `calendario_configuracao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cidacolhido`
--

DROP TABLE IF EXISTS `cidacolhido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cidacolhido` (
  `idCid` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(6) DEFAULT NULL,
  `descricao` varchar(45) DEFAULT NULL,
  `acolhido_id` int(11) NOT NULL,
  PRIMARY KEY (`idCid`),
  KEY `fk_CidAcolhido_acolhido1` (`acolhido_id`),
  CONSTRAINT `fk_CidAcolhido_acolhido1` FOREIGN KEY (`acolhido_id`) REFERENCES `acolhido` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cidacolhido`
--

LOCK TABLES `cidacolhido` WRITE;
/*!40000 ALTER TABLE `cidacolhido` DISABLE KEYS */;
INSERT INTO `cidacolhido` VALUES (44,'F21','Transtorno esquizotípico',9),(45,'F15','Transtornos mentais e comportamentais devidos',9),(50,'G40','Epilepsia',10),(51,'G10','Doença de Huntington',2),(52,'F20','Esquizofrenia',2),(56,'F21','Transtorno esquizotípico',3),(57,'G20','Doença de Parkinson',3),(58,'F10','Esquizofrenia',3);
/*!40000 ALTER TABLE `cidacolhido` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `curador`
--

DROP TABLE IF EXISTS `curador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `curador` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `cpf` varchar(255) DEFAULT NULL,
  `datanNascimento` date DEFAULT NULL,
  `grauParentesco` varchar(45) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `telefone_2` varchar(20) DEFAULT NULL,
  `idEndereco` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_curador_Endereco` (`idEndereco`),
  CONSTRAINT `fk_curador_Endereco` FOREIGN KEY (`idEndereco`) REFERENCES `endereco` (`idEndereco`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `curador`
--

LOCK TABLES `curador` WRITE;
/*!40000 ALTER TABLE `curador` DISABLE KEYS */;
INSERT INTO `curador` VALUES (7,'Simone Rosângela','T2t2YjdvVDgvam5mL25vQVpvcFBzZz09Ojp86I85qiIBSd0SH6/L+rKW','1980-04-05','Mãe','(19) 2562-6133','(19) 99852-2336',37);
/*!40000 ALTER TABLE `curador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dispensacoes`
--

DROP TABLE IF EXISTS `dispensacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dispensacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prescricao_id` int(11) DEFAULT NULL COMMENT 'Refer??ncia direta ?? prescri????o',
  `prescricao_horario_id` int(11) DEFAULT NULL COMMENT 'Nulo para prescri????es por intervalo',
  `acolhido_id` int(11) NOT NULL,
  `medicamento_id` int(11) NOT NULL,
  `quantidade_dispensada` decimal(10,2) NOT NULL,
  `horario_previsto` time DEFAULT NULL COMMENT 'Hor??rio em que a dispensa????o estava agendada',
  `data_dispensacao` date NOT NULL,
  `horario_dispensado` time NOT NULL,
  `usuario_responsavel` varchar(255) NOT NULL,
  `status` enum('dispensado','recusado','nao_administrado') NOT NULL DEFAULT 'dispensado',
  `observacao` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_disp_prescricao_data_horario` (`prescricao_id`,`data_dispensacao`,`horario_previsto`),
  KEY `fk_dispensacoes_horario` (`prescricao_horario_id`),
  KEY `fk_dispensacoes_acolhido` (`acolhido_id`),
  CONSTRAINT `fk_disp_prescricao` FOREIGN KEY (`prescricao_id`) REFERENCES `prescricoes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_dispensacoes_acolhido` FOREIGN KEY (`acolhido_id`) REFERENCES `acolhido` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dispensacoes_horario` FOREIGN KEY (`prescricao_horario_id`) REFERENCES `prescricao_horarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dispensacoes`
--

LOCK TABLES `dispensacoes` WRITE;
/*!40000 ALTER TABLE `dispensacoes` DISABLE KEYS */;
INSERT INTO `dispensacoes` VALUES (12,23,NULL,5,13,15.00,'00:00:00','2026-06-23','01:55:42','Sistema','dispensado',NULL),(13,22,61,5,18,1.00,'09:00:00','2026-09-07','02:26:20','Sistema','nao_administrado',NULL),(16,22,61,5,18,1.00,'09:00:00','2026-10-03','20:17:00','testeadmin','dispensado',NULL),(17,19,57,6,16,1.00,'11:00:00','2026-09-28','20:45:42','testeadmin','dispensado',NULL),(18,22,61,5,18,1.00,'09:00:00','2026-09-28','20:45:49','testeadmin','recusado',NULL),(22,19,57,6,16,1.00,'11:00:00','2026-10-07','23:14:13','testeadmin','dispensado',NULL),(23,22,61,5,18,1.00,'09:00:00','2026-10-07','23:14:25','natandepaula','recusado',NULL),(27,22,61,5,18,1.00,'09:00:00','2026-10-14','13:45:59','testeadmin','dispensado',NULL),(28,19,57,6,16,1.00,'11:00:00','2026-10-14','14:45:48','testeadmin','dispensado',NULL),(29,21,66,8,15,1.00,'14:00:00','2026-11-18','15:16:25','testeadmin','dispensado',NULL),(30,18,68,6,13,1.00,'08:00:00','2026-11-22','14:27:06','testeadmin','dispensado',NULL),(32,20,70,6,15,1.00,'09:00:00','2026-11-22','14:27:18','testeadmin','dispensado',NULL),(37,24,84,5,15,1.50,'09:00:00','2026-11-24','11:25:34','natan','dispensado',NULL),(38,22,61,5,18,1.00,'09:00:00','2026-11-24','15:33:48','natan','dispensado',NULL),(39,22,61,5,18,1.00,'09:00:00','2026-11-23','15:51:38','natan','dispensado',NULL),(40,24,84,5,15,1.50,'09:00:00','2026-11-23','15:51:38','natan','dispensado',NULL),(48,26,85,5,13,1.00,'20:00:00','2026-11-24','17:17:24','natan','dispensado',NULL),(49,26,85,5,13,1.00,'20:00:00','2026-11-16','17:17:43','natan','dispensado',NULL),(52,31,86,3,26,1.00,'20:00:00','2026-11-27','19:17:27','natan','dispensado',NULL),(53,22,61,5,18,1.00,'09:00:00','2026-11-27','19:17:44','natan','dispensado',NULL),(54,24,84,5,15,1.50,'09:00:00','2026-11-27','19:22:31','natan','nao_administrado',NULL),(55,26,85,5,13,1.00,'20:00:00','2026-11-27','19:23:40','natan','dispensado','teste'),(57,22,61,5,18,1.00,'09:00:00','2026-11-26','21:40:36','natan','dispensado','tomou depois de conversar bastante'),(58,18,68,6,13,1.00,'08:00:00','2026-11-26','21:40:44','natan','dispensado',NULL),(59,24,84,5,15,1.50,'09:00:00','2026-11-26','21:42:57','natan','recusado',NULL),(60,26,85,5,13,1.00,'20:00:00','2026-11-26','21:43:13','natan','recusado','não quis tomar'),(61,21,66,8,15,1.00,'14:00:00','2026-11-26','21:43:43','natan','nao_administrado','Foi suspensa');
/*!40000 ALTER TABLE `dispensacoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `endereco`
--

DROP TABLE IF EXISTS `endereco`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `endereco` (
  `idEndereco` int(11) NOT NULL AUTO_INCREMENT,
  `estado` varchar(45) NOT NULL,
  `cidade` varchar(45) NOT NULL,
  `rua` varchar(45) NOT NULL,
  `numero` int(11) DEFAULT NULL,
  `cep` varchar(8) NOT NULL,
  `referencia` varchar(150) DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  PRIMARY KEY (`idEndereco`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `endereco`
--

LOCK TABLES `endereco` WRITE;
/*!40000 ALTER TABLE `endereco` DISABLE KEYS */;
INSERT INTO `endereco` VALUES (1,'ES','Alegre','Alguma',12,'29510-00',NULL,NULL),(2,'ES','Alegre','rual tal',21212,'29510000',NULL,NULL),(3,'ES','Alegre','Rua tal',15,'29510000',NULL,NULL),(4,'RJ','Cantagalo','haghaghag',12,'28520-00',NULL,NULL),(5,'ES','Alegre','hagahgah',21212,'29520-00',NULL,NULL),(6,'ES','Alegre','hagahgah',21212,'29520-00',NULL,NULL),(7,'ES','Alegre','hagahgah',21212,'29520-00',NULL,NULL),(8,'ES','Alegre','hagahgah',21212,'29520-00',NULL,NULL),(9,'ES','Alegre','Rua das Flores',123,'29500-00',NULL,NULL),(10,'ES','Alegre','Av. Principal',456,'29500-00',NULL,NULL),(11,'ES','Vila Velha','Rua da Paz',789,'29100-01',NULL,NULL),(12,'ES','Jer??nimo Monteiro','Rua da Saudade',0,'29520-00',NULL,NULL),(13,'RJ','Rio de Janeiro','Rua dos Coqueiros',321,'22420-04',NULL,NULL),(14,'RJ','Rio de Janeiro','Rua da Lapa',100,'20231-03',NULL,NULL),(15,'ES','Vit??ria','N??o se aplica',0,'29000-00',NULL,NULL),(16,'ES','Vila Velha','Av. Beira Mar',987,'29101-12',NULL,NULL),(17,'ES','Vit??ria','Rua Sete de Setembro',45,'29010-00',NULL,NULL),(18,'RJ','Rio de Janeiro','Rua da Lapa',100,'20231-03',NULL,NULL),(19,'RJ','Rio de Janeiro','Rua da Lapa',100,'20231-03',NULL,NULL),(20,'RJ','Rio de Janeiro','Rua da Lapa',100,'20231-03',NULL,NULL),(21,'RJ','Rio de Janeiro','Rua da Lapa',100,'20231-03',NULL,NULL),(22,'ES','Alegre','Ant??nio Peres Rodrigues',34,'29510000',NULL,NULL),(23,'ES','Alegre','DJAHAJDHJ',11,'29510000',NULL,NULL),(24,'ES','Alegre','2121',21212,'29520000',NULL,NULL),(25,'SP','Sumaré','Rua das Crianças',56,'13173322','Endereco sem correspondencia real','REGISTRO FICTICIO PARA DEMONSTRACAO DO SISTEMA. SEM VALIDADE CLINICA OU ADMINISTRATIVA. NAO UTILIZAR COMO DADO REAL.'),(26,'ES','Municipio Ficticio','Rua de Demonstracao',0,'00000000','Endereco sem correspondencia real','REGISTRO FICTICIO PARA DEMONSTRACAO DO SISTEMA. SEM VALIDADE CLINICA OU ADMINISTRATIVA. NAO UTILIZAR COMO DADO REAL.'),(29,'ES','Alegre','Rua taltal',21212,'29520000',NULL,NULL),(31,'ES','Gua??u??','Antonio Martins',0,'29560000',NULL,NULL),(32,'ES','Alegre','Celina',NULL,'29510000',NULL,NULL),(33,'ES','Alegre','Rua tal',NULL,'29500000',NULL,NULL),(35,'ES','Municipio Ficticio','Rua de Demonstracao',NULL,'00000000',NULL,NULL),(36,'ES','Municipio Ficticio','Rua de Demonstracao',NULL,'00000000',NULL,NULL),(37,'SP','Sumaré','Rua das Crianças',56,'13173322',NULL,NULL),(38,'SP','Sumaré','Rua das Crianças',NULL,'13173322',NULL,NULL),(39,'ES','Alegre','Rua tal',NULL,'29500000',NULL,NULL),(40,'ES','Alegre','hghghgh',21212,'29520000',NULL,NULL),(41,'ES','Alegre','hghghgh',21212,'29520000',NULL,NULL),(42,'ES','Alegre','Rua tal',NULL,'29500000',NULL,NULL),(43,'ES','Alegre','hghghgh',21212,'29520000',NULL,NULL),(44,'ES','Alegre','hghghgh',21212,'29520000',NULL,NULL);
/*!40000 ALTER TABLE `endereco` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evolucao_acolhido`
--

DROP TABLE IF EXISTS `evolucao_acolhido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evolucao_acolhido` (
  `idEvolucao` int(11) NOT NULL AUTO_INCREMENT,
  `acolhido_id` int(11) NOT NULL,
  `autor_login_id` int(11) DEFAULT NULL,
  `usuario_idUsuario` int(11) DEFAULT NULL,
  `registrado_em` datetime NOT NULL,
  `registro` longtext NOT NULL,
  `profissional_nome` varchar(180) NOT NULL,
  `atuacao_profissional` varchar(120) DEFAULT NULL,
  `registro_profissional` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`idEvolucao`),
  KEY `idx_evolucao_acolhido_data` (`acolhido_id`,`registrado_em`),
  KEY `idx_evolucao_autor_data` (`autor_login_id`,`registrado_em`),
  KEY `fk_evolucao_usuario` (`usuario_idUsuario`),
  CONSTRAINT `fk_evolucao_acolhido` FOREIGN KEY (`acolhido_id`) REFERENCES `acolhido` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_evolucao_autor` FOREIGN KEY (`autor_login_id`) REFERENCES `login` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_evolucao_usuario` FOREIGN KEY (`usuario_idUsuario`) REFERENCES `usuario` (`idUsuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evolucao_acolhido`
--

LOCK TABLES `evolucao_acolhido` WRITE;
/*!40000 ALTER TABLE `evolucao_acolhido` DISABLE KEYS */;
INSERT INTO `evolucao_acolhido` VALUES (1,9,NULL,NULL,'2025-02-10 16:00:00','REGISTRO FICTICIO PARA DEMONSTRACAO DO SISTEMA. SEM VALIDADE CLINICA OU ADMINISTRATIVA. NAO UTILIZAR COMO DADO REAL. Evolucao de demonstracao; nao representa observacao clinica real.','REGISTRO FICTICIO - SEM PROFISSIONAL REAL',NULL,NULL),(4,5,5,3,'2026-11-14 09:36:00','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean facilisis, nunc at accumsan semper, sem magna gravida ligula, sit amet feugiat ex dolor et ipsum. Suspendisse varius tortor id enim cursus fermentum. Mauris vitae maximus enim. Curabitur iaculis leo eu lectus pretium, vitae dignissim ante auctor. In hendrerit risus at aliquam lacinia. Aliquam sit amet lobortis lacus. Aenean ullamcorper porta urna, sit amet pretium lectus tristique vel. Ut non rhoncus nisi. Fusce in elit a purus iaculis mollis. Sed tortor mi, feugiat at diam non, placerat facilisis ligula. Nulla consequat ante lectus, et euismod sem convallis congue. Sed a tempus nibh, id faucibus sapien. Suspendisse non suscipit ante. Aliquam eleifend mi ac orci maximus maximus.','Fisio Fábio','Fisioterapeuta','12121'),(5,5,5,3,'2026-11-02 10:21:00','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean facilisis, nunc at accumsan semper, sem magna gravida ligula, sit amet feugiat ex dolor et ipsum. Suspendisse varius tortor id enim cursus fermentum. Mauris vitae maximus enim. Curabitur iaculis leo eu lectus pretium, vitae dignissim ante auctor. In hendrerit risus at aliquam lacinia. Aliquam sit amet lobortis lacus. Aenean ullamcorper porta urna, sit amet pretium lectus tristique vel. Ut non rhoncus nisi. Fusce in elit a purus iaculis mollis. Sed tortor mi, feugiat at diam non, placerat facilisis ligula. Nulla consequat ante lectus, et euismod sem convallis congue. Sed a tempus nibh, id faucibus sapien. Suspendisse non suscipit ante. Aliquam eleifend mi ac orci maximus maximus.\r\n\r\nProin ligula diam, rhoncus sed nunc non, interdum laoreet justo. Integer diam eros, lacinia at mi nec, tempor congue lectus. Maecenas ligula mauris, suscipit nec lacus sed, malesuada egestas magna. Nullam sed justo ante. Ut pharetra pharetra turpis non tempus. In aliquam fringilla urna, sit amet sagittis felis feugiat vel. Phasellus convallis congue leo, id consectetur dolor convallis sit amet. Cras risus nulla, iaculis vitae finibus sit amet, sollicitudin nec urna. Nulla at cursus odio.','Fisio Fábio','Fisioterapeuta','12121'),(7,3,3,1,'2026-11-23 16:31:00','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eget lorem velit. Nam et auctor diam. Sed vel dolor condimentum, mattis risus et, dictum quam. Proin lectus mauris, mattis in nisl et, malesuada imperdiet mi. Sed rhoncus arcu enim, at dictum neque convallis a. Donec gravida sodales dui, sagittis venenatis dolor sagittis non. Morbi non pharetra diam. Cras in iaculis ante. Vestibulum gravida gravida egestas. Nunc sagittis purus augue, sed consequat sapien posuere at. Phasellus viverra quam ut viverra fringilla. Nam a enim non elit convallis iaculis.','Natan de Paula Batista','Coordenador',NULL),(8,5,3,1,'2026-11-23 19:13:00','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean facilisis, nunc at accumsan semper, sem magna gravida ligula, sit amet feugiat ex dolor et ipsum. Suspendisse varius tortor id enim cursus fermentum. Mauris vitae maximus enim. Curabitur iaculis leo eu lectus pretium, vitae dignissim ante auctor. In hendrerit risus at aliquam lacinia. Aliquam sit amet lobortis lacus. Aenean ullamcorper porta urna, sit amet pretium lectus tristique vel. Ut non rhoncus nisi. Fusce in elit a purus iaculis mollis.','Natan de Paula Batista','Coordenador',NULL);
/*!40000 ALTER TABLE `evolucao_acolhido` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `familiar`
--

DROP TABLE IF EXISTS `familiar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `familiar` (
  `idFamiliar` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) DEFAULT NULL,
  `dataNascimento` date DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `grauParentesco` varchar(45) DEFAULT NULL,
  `acolhido_id` int(11) NOT NULL,
  `idEndereco` int(11) DEFAULT NULL,
  PRIMARY KEY (`idFamiliar`),
  KEY `fk_Familiar_acolhido1` (`acolhido_id`),
  KEY `fk_familiar_Endereco` (`idEndereco`),
  CONSTRAINT `fk_Familiar_acolhido1` FOREIGN KEY (`acolhido_id`) REFERENCES `acolhido` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_familiar_Endereco` FOREIGN KEY (`idEndereco`) REFERENCES `endereco` (`idEndereco`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `familiar`
--

LOCK TABLES `familiar` WRITE;
/*!40000 ALTER TABLE `familiar` DISABLE KEYS */;
INSERT INTO `familiar` VALUES (5,'Pedro da Silva','1978-03-12','(28) 98888-','Filho',4,11),(7,'Antonio Costa','1952-12-01','(27) 95555-','Irm??o',8,17),(11,'Lucia Oliveira','1960-01-25','(21) 96666-5333','Irm??',6,21),(16,'Simone Rosângela','1980-04-05','(19) 2562-6133','Responsavel legal (ficticio)',9,38),(20,'Fernando Yago Galv??o','1981-04-01',NULL,'Pai',10,42),(22,'Joana hahaha','1980-01-01','999999999','Mae',3,44);
/*!40000 ALTER TABLE `familiar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horarios_nomeados`
--

DROP TABLE IF EXISTS `horarios_nomeados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horarios_nomeados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL COMMENT 'Ex: Caf?? da Manh??, Antes de dormir',
  `horario` time NOT NULL,
  `periodo_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hn_periodo` (`periodo_id`),
  CONSTRAINT `fk_hn_periodo` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horarios_nomeados`
--

LOCK TABLES `horarios_nomeados` WRITE;
/*!40000 ALTER TABLE `horarios_nomeados` DISABLE KEYS */;
INSERT INTO `horarios_nomeados` VALUES (1,'Antes do almoço','10:30:00',2),(2,'Manhã','08:00:00',1),(3,'Depois do almoço','11:00:00',2),(4,'noite','20:00:00',10);
/*!40000 ALTER TABLE `horarios_nomeados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `informacao_links`
--

DROP TABLE IF EXISTS `informacao_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `informacao_links` (
  `idInformacaoLink` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `url` varchar(2048) NOT NULL,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idInformacaoLink`),
  KEY `idx_informacao_links_ordem` (`ordem`,`idInformacaoLink`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `informacao_links`
--

LOCK TABLES `informacao_links` WRITE;
/*!40000 ALTER TABLE `informacao_links` DISABLE KEYS */;
INSERT INTO `informacao_links` VALUES (1,'Calendário de Férias','https://docs.google.com/spreadsheets/d/1og7qIslXNufsZR6gXt2r925yfMRydpZFiJB45G92CqY/edit',10,'2026-10-08 01:22:26','2026-11-18 01:10:12'),(3,'Curso - Cuidador de Idosos','https://www.faculdadeunimed.edu.br/cursos/curta-e-media-duracao/cuidadores-de-idosos/#:~:text=O%20curso%20gratuito%20Cuidadores%20de,especialista%20da%20%C3%A1rea%20de%20sa%C3%BAde',30,'2026-10-08 02:07:00','2026-11-09 22:06:40'),(4,'Cursos - Corpo de Bombeiros','https://ead.cb.es.gov.br/course/index.php?categoryid=84',40,'2026-10-08 02:08:03','2026-10-08 02:08:03');
/*!40000 ALTER TABLE `informacao_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login`
--

DROP TABLE IF EXISTS `login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login` (
  `idLogin` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(45) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `troca_senha_obrigatoria` tinyint(1) NOT NULL DEFAULT 0,
  `senha_temporaria_expira_em` datetime DEFAULT NULL,
  `senha_alterada_em` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`idLogin`),
  UNIQUE KEY `uq_login_login` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login`
--

LOCK TABLES `login` WRITE;
/*!40000 ALTER TABLE `login` DISABLE KEYS */;
INSERT INTO `login` VALUES (1,'testeadmin','$2y$10$gBHM.3cDMiJ6HcIjklZp7.FU8VRshRrgQe6DTn1fNs3jiJBysP1nK',1,1,0,NULL,'2026-11-17 23:49:20.516008'),(2,'testecuidador','$2y$10$ViaEyEQDUhQymlH8gaNNBuJO08pYXiR3F5.S/Bk831M0ZU6KsgQ7q',1,0,0,NULL,'2026-11-17 23:49:20.516008'),(3,'natan','$2y$10$1fOubs/H5pcgAZFga0EgU.fvQg9j6tpf133eF5nC2a.8sEnGMl1Ka',1,1,0,NULL,'2026-11-17 23:49:20.516008'),(4,'testedesabitado','$2y$10$oUXQG8zf.7ZYsQWpTP2og.LEI1TV8Lsh/ppqNNGhcL4kPSG8W5SIe',0,0,0,NULL,'2026-11-17 23:49:20.516008'),(5,'fisio','$2y$10$ymoNqX1YegUrA00PeHReFOtGSf.f3XjF1NysLgeINLXNzW8ARbuhy',1,0,0,NULL,'2026-11-17 23:49:20.516008');
/*!40000 ALTER TABLE `login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_funcionalidades`
--

DROP TABLE IF EXISTS `login_funcionalidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_funcionalidades` (
  `Login_idLogin` int(11) NOT NULL,
  `funcionalidade` varchar(64) NOT NULL,
  PRIMARY KEY (`Login_idLogin`,`funcionalidade`),
  KEY `idx_login_funcionalidades_funcionalidade` (`funcionalidade`),
  CONSTRAINT `fk_login_funcionalidades_login` FOREIGN KEY (`Login_idLogin`) REFERENCES `login` (`idLogin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_funcionalidades`
--

LOCK TABLES `login_funcionalidades` WRITE;
/*!40000 ALTER TABLE `login_funcionalidades` DISABLE KEYS */;
INSERT INTO `login_funcionalidades` VALUES (2,'calendario'),(2,'gerenciar_pia_campos'),(2,'informacoes'),(5,'consultar_minhas_evolucoes'),(5,'imprimir_minhas_evolucoes'),(5,'informacoes'),(5,'registrar_evolucao_acolhido');
/*!40000 ALTER TABLE `login_funcionalidades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medicamentos`
--

DROP TABLE IF EXISTS `medicamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medicamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_comercial` varchar(255) NOT NULL,
  `principio_ativo` varchar(255) NOT NULL,
  `apresentacao` varchar(255) DEFAULT NULL,
  `unidade_base` enum('comprimido','ml','gota','unidade','capsula') NOT NULL,
  `gotas_por_ml` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medicamentos`
--

LOCK TABLES `medicamentos` WRITE;
/*!40000 ALTER TABLE `medicamentos` DISABLE KEYS */;
INSERT INTO `medicamentos` VALUES (13,'DIPIRONA','DIPIRONA','500 MG/ML SOL INJ','ml',20),(15,'FENOBARBITAL','FENOBARBITAL','100 MG COM','comprimido',NULL),(16,'CLONAZEPAM','CLONAZEPAM','2 MG COM','comprimido',NULL),(17,'PARACETAMOL','PARACETAMOL','750 MG COM REV','comprimido',NULL),(18,'BROMAZEPAM','BROMAZEPAM','3 MG COM','comprimido',NULL),(20,'ANTARA','LEVETIRACETAM','250 MG COM REV','comprimido',NULL),(21,'CLORIDRATO DE CLORPROMAZINA','CLORIDRATO DE CLORPROMAZINA','25 MG COM REV','comprimido',NULL),(23,'COMPLEXO B','NICOTINAMIDA;NITRATO DE TIAMINA;RIBOFLAVINA;CLORIDRATO DE PIRIDOXINA;PANTOTENATO DE CALCIO','(5,45 + 2 + 2 + 20 + 3) MG COM REV','comprimido',NULL),(24,'NEULEPTIL','PERICIAZINA','10 MG COM REV','comprimido',NULL),(26,'LACRIFILM','CARMELOSE SÓDICA','5 MG/ML SOL OFT','ml',17),(27,'DIPIRONA  MONOIDRATADA','DIPIRONA','500 MG/ML SOL OR','ml',20);
/*!40000 ALTER TABLE `medicamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medicamentos_estoque`
--

DROP TABLE IF EXISTS `medicamentos_estoque`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medicamentos_estoque` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `medicamento_id` int(11) NOT NULL,
  `data_entrada` datetime NOT NULL DEFAULT current_timestamp(),
  `quantidade_entrada` decimal(10,2) NOT NULL COMMENT 'Quantidade na unidade de entrada (ex: 1 caixa, 1 frasco)',
  `unidade_entrada` varchar(50) NOT NULL COMMENT 'Ex: caixa, frasco, cartela',
  `fator_conversao` int(11) NOT NULL COMMENT 'Quantas unidades_base tem na unidade_entrada (ex: 30 comprimidos por caixa)',
  `quantidade_atual_base` decimal(14,2) NOT NULL COMMENT 'Quantidade atual na menor unidade (ex: 25 comprimidos)',
  PRIMARY KEY (`id`),
  KEY `medicamento_id` (`medicamento_id`),
  CONSTRAINT `medicamentos_estoque_ibfk_1` FOREIGN KEY (`medicamento_id`) REFERENCES `medicamentos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medicamentos_estoque`
--

LOCK TABLES `medicamentos_estoque` WRITE;
/*!40000 ALTER TABLE `medicamentos_estoque` DISABLE KEYS */;
INSERT INTO `medicamentos_estoque` VALUES (17,16,'2026-06-22 20:38:44',10.00,'',30,197.00),(18,13,'2026-06-22 20:45:44',2.00,'',15,0.00),(19,15,'2026-06-22 20:46:01',10.00,'',30,189.00),(20,18,'2026-09-07 21:32:41',3.00,'',30,24.00),(21,13,'2026-11-24 15:56:35',1.00,'FRASCO',20,18.85),(22,13,'2026-11-24 16:53:10',10.00,'ml',1,10.00),(23,13,'2026-11-24 16:55:14',2.00,'embalagem',30,60.00),(24,26,'2026-11-27 19:17:18',3.00,'embalagem',15,44.94);
/*!40000 ALTER TABLE `medicamentos_estoque` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medicamentos_movimentacoes`
--

DROP TABLE IF EXISTS `medicamentos_movimentacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medicamentos_movimentacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estoque_id` int(11) NOT NULL,
  `tipo` enum('entrada','dispensacao','perda_vencimento','doacao','ajuste') NOT NULL,
  `quantidade_movimentada` decimal(14,2) NOT NULL COMMENT 'Quantidade na unidade_base',
  `data_movimentacao` datetime NOT NULL DEFAULT current_timestamp(),
  `acolhido_id` int(11) DEFAULT NULL COMMENT 'Para dispensa????es',
  `usuario_responsavel` varchar(255) DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estoque_id` (`estoque_id`),
  KEY `acolhido_id` (`acolhido_id`),
  CONSTRAINT `medicamentos_movimentacoes_ibfk_1` FOREIGN KEY (`estoque_id`) REFERENCES `medicamentos_estoque` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medicamentos_movimentacoes_ibfk_2` FOREIGN KEY (`acolhido_id`) REFERENCES `acolhido` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medicamentos_movimentacoes`
--

LOCK TABLES `medicamentos_movimentacoes` WRITE;
/*!40000 ALTER TABLE `medicamentos_movimentacoes` DISABLE KEYS */;
INSERT INTO `medicamentos_movimentacoes` VALUES (44,17,'entrada',300.00,'2026-06-22 20:38:44',NULL,'Sistema',NULL),(45,17,'perda_vencimento',100.00,'2026-06-22 20:39:02',NULL,'Sistema',''),(46,18,'entrada',30.00,'2026-06-22 20:45:44',NULL,'Sistema',NULL),(47,19,'entrada',300.00,'2026-06-22 20:46:01',NULL,'Sistema',NULL),(48,19,'perda_vencimento',100.00,'2026-06-22 20:46:19',NULL,'Sistema',''),(49,18,'dispensacao',15.00,'2026-06-22 20:55:42',NULL,'Sistema','Dispensado via quadro de dispensa????o.'),(50,19,'dispensacao',1.00,'2026-09-07 21:26:32',NULL,'Sistema','Dispensado via quadro de dispensa????o.'),(51,20,'entrada',90.00,'2026-09-07 21:32:41',NULL,'Sistema',NULL),(52,20,'perda_vencimento',60.00,'2026-09-07 21:33:11',NULL,'Sistema',''),(53,18,'dispensacao',1.00,'2026-10-03 19:44:38',NULL,'Sistema','Dispensado via quadro de dispensa????o.'),(54,20,'dispensacao',1.00,'2026-10-03 20:17:00',NULL,'testeadmin','Dispensado via quadro de dispensa????o.'),(55,17,'dispensacao',1.00,'2026-10-03 20:45:42',NULL,'testeadmin','Dispensado via quadro de dispensacao.'),(56,19,'dispensacao',1.00,'2026-10-03 20:45:53',NULL,'testeadmin','Dispensado via quadro de dispensacao.'),(57,18,'dispensacao',1.00,'2026-10-07 21:23:31',NULL,'testeadmin','Dispensado via quadro de dispensacao.'),(58,19,'dispensacao',1.00,'2026-10-07 23:10:31',NULL,'natandepaula','Dispensado via quadro de dispensa????o.'),(59,17,'dispensacao',1.00,'2026-10-07 23:14:13',NULL,'testeadmin','Dispensado via quadro de dispensa????o.'),(60,19,'dispensacao',1.00,'2026-10-07 23:15:52',NULL,'natandepaula','Dispensado via quadro de dispensa????o.'),(61,19,'dispensacao',1.00,'2026-10-08 00:04:25',NULL,'natandepaula','Dispensado via quadro de dispensa????o.'),(62,19,'dispensacao',1.00,'2026-10-13 09:30:13',NULL,'natandepaula','Dispensado via quadro de dispensa????o.'),(63,20,'dispensacao',1.00,'2026-10-14 13:45:59',NULL,'testeadmin','Dispensado via quadro de dispensa????o.'),(64,17,'dispensacao',1.00,'2026-10-14 14:45:48',NULL,'testeadmin','Dispensado via quadro de dispensacao.'),(65,18,'perda_vencimento',12.00,'2026-10-14 14:46:49',NULL,'testeadmin',''),(66,19,'dispensacao',1.00,'2026-11-18 15:16:25',NULL,'testeadmin','Dispensado via quadro de dispensacao.'),(67,18,'dispensacao',1.00,'2026-11-22 14:27:06',NULL,'testeadmin','Dispensado via quadro de dispensacao.'),(68,19,'dispensacao',1.00,'2026-11-22 14:27:18',NULL,'testeadmin','Dispensado via quadro de dispensacao.'),(69,19,'dispensacao',1.50,'2026-11-24 11:25:34',NULL,'natan','Dispensado via quadro de dispensação.'),(70,20,'dispensacao',1.00,'2026-11-24 15:33:48',NULL,'natan','Dispensado via quadro de dispensação.'),(71,20,'dispensacao',1.00,'2026-11-24 15:51:38',NULL,'natan','Dispensado via quadro de dispensação.'),(72,19,'dispensacao',1.50,'2026-11-24 15:51:38',NULL,'natan','Dispensado via quadro de dispensação.'),(73,21,'entrada',20.00,'2026-11-24 15:56:35',NULL,'natan',NULL),(74,22,'entrada',10.00,'2026-11-24 16:53:10',NULL,'natan',NULL),(75,23,'entrada',60.00,'2026-11-24 16:55:14',NULL,'natan',NULL),(76,21,'dispensacao',0.05,'2026-11-24 17:17:24',NULL,'natan','Dispensado via quadro de dispensação. Baixa aproximada de 0.05 mL para 1 gotas (20 gotas/mL).'),(77,21,'dispensacao',0.05,'2026-11-24 17:17:43',NULL,'natan','Dispensado via quadro de dispensação. Baixa aproximada de 0.05 mL para 1 gotas (20 gotas/mL).'),(78,24,'entrada',45.00,'2026-11-27 19:17:18',NULL,'natan',NULL),(79,24,'dispensacao',0.06,'2026-11-27 19:17:27',NULL,'natan','Dispensado via quadro de dispensação. Baixa aproximada de 0.06 mL para 1 gotas (17 gotas/mL).'),(80,20,'dispensacao',1.00,'2026-11-27 19:17:44',NULL,'natan','Dispensado via quadro de dispensação.'),(82,20,'dispensacao',1.00,'2026-11-27 21:40:36',NULL,'natan','Dispensado via quadro de dispensação.'),(83,21,'dispensacao',1.00,'2026-11-27 21:40:44',NULL,'natan','Dispensado via quadro de dispensação.');
/*!40000 ALTER TABLE `medicamentos_movimentacoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periodos`
--

DROP TABLE IF EXISTS `periodos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `periodos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `ordem` int(11) NOT NULL DEFAULT 0 COMMENT 'Usado para ordenar a exibi????o (menor primeiro)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periodos`
--

LOCK TABLES `periodos` WRITE;
/*!40000 ALTER TABLE `periodos` DISABLE KEYS */;
INSERT INTO `periodos` VALUES (1,'Manhã',10),(2,'Almoço',20),(3,'Tarde',30),(4,'Jantar',40),(5,'Ceia',50),(6,'Madrugada',60),(10,'Noite',35);
/*!40000 ALTER TABLE `periodos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissao`
--

DROP TABLE IF EXISTS `permissao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissao` (
  `idPermisoes` int(11) NOT NULL AUTO_INCREMENT,
  `cod` varchar(5) NOT NULL,
  `nome` varchar(45) NOT NULL,
  `descricao` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`idPermisoes`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissao`
--

LOCK TABLES `permissao` WRITE;
/*!40000 ALTER TABLE `permissao` DISABLE KEYS */;
INSERT INTO `permissao` VALUES (1,'ABDM1','Conta ABDM','Acesso controlado pelas permiss?????es de funcionalidades da conta.');
/*!40000 ALTER TABLE `permissao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pia`
--

DROP TABLE IF EXISTS `pia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pia` (
  `idPIA` int(11) NOT NULL AUTO_INCREMENT,
  `acolhido_id` int(11) NOT NULL,
  `dataAtualizacao` date DEFAULT NULL,
  `dados_json` longtext DEFAULT NULL,
  `estrutura_json` longtext DEFAULT NULL,
  `criado_por_login` int(11) DEFAULT NULL,
  `atualizado_por_login` int(11) DEFAULT NULL,
  `criado_em` datetime DEFAULT NULL,
  `atualizado_em` datetime DEFAULT NULL,
  PRIMARY KEY (`idPIA`),
  KEY `fk_PIA_acolhido1` (`acolhido_id`),
  CONSTRAINT `fk_PIA_acolhido1` FOREIGN KEY (`acolhido_id`) REFERENCES `acolhido` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pia`
--

LOCK TABLES `pia` WRITE;
/*!40000 ALTER TABLE `pia` DISABLE KEYS */;
INSERT INTO `pia` VALUES (1,3,'2026-11-23','{\"data_nascimento\":\"1995-01-01\",\"cor\":\"\",\"orientacao_sexual\":\"\",\"orientacao_sexual_outros\":\"\",\"estado_civil\":\"\",\"escolaridade\":\"\",\"nacionalidade\":\"\",\"naturalidade\":\"\",\"origem_encaminhamento\":[],\"motivo_acolhimento\":\"\",\"numero_processo\":\"\",\"impedimento_visitacao\":\"\",\"quem_impedido\":\"\",\"beneficios\":[],\"beneficio_atividade\":\"\",\"beneficio_outro\":\"\",\"administra_recursos\":\"\",\"responsavel_recursos\":\"\",\"composicao_familiar\":[],\"documentos\":{\"certidao\":{\"situacao\":\"\",\"numero\":\"\"},\"rg\":{\"situacao\":\"\",\"numero\":\"\"},\"cpf\":{\"situacao\":\"\",\"numero\":\"\"},\"titulo_eleitoral\":{\"situacao\":\"\",\"numero\":\"\"},\"ctps\":{\"situacao\":\"\",\"numero\":\"\"},\"sus\":{\"situacao\":\"\",\"numero\":\"\"},\"outros\":{\"situacao\":\"\",\"numero\":\"\"}},\"plano_atividades\":{\"convivencia\":\"\",\"desenvolvimento\":\"\",\"documentacao\":\"\",\"esporte_cultura\":\"\",\"geracao_renda\":\"\",\"mobilizacao_rede\":\"\",\"transferencia_renda\":\"\",\"vinculos\":\"\",\"saude\":\"\",\"servicos\":\"\"},\"ficha_evolucao\":[{\"data\":\"\",\"texto\":\"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eget lorem velit. Nam et auctor diam. Sed vel dolor condimentum, mattis risus et, dictum quam. Proin lectus mauris, mattis in nisl et, malesuada imperdiet mi. Sed rhoncus arcu enim, at dictum neque convallis a. Donec gravida sodales dui, sagittis venenatis dolor sagittis non. Morbi non pharetra diam. Cras in iaculis ante. Vestibulum gravida gravida egestas. Nunc sagittis purus augue, sed consequat sapien posuere at. Phasellus viverra quam ut viverra fringilla. Nam a enim non elit convallis iaculis.\"}],\"procedimentos_familia\":\"\",\"procedimentos_acolhido\":\"\",\"rede_servicos\":[],\"observacao_rede\":\"\",\"laudo_medico\":\"\",\"cid10\":\"\",\"transtorno_mental\":\"\",\"qual_transtorno\":\"\",\"dst\":\"\",\"qual_dst\":\"\",\"restricao_alimentar\":\"\",\"qual_restricao\":\"\",\"doencas_cronicas\":\"\",\"especialistas_tratamentos\":\"\",\"especialistas_necessarios\":\"\",\"orteses_proteses\":\"\",\"cuidados_personalizados\":\"\",\"envolvimento_substancias\":[],\"envolvimento_outros\":\"\",\"documentos_familia\":\"\",\"renda_familiar\":\"\",\"institucionalizacao_familiar\":\"\",\"local_institucionalizacao\":\"\",\"encaminhamento_rede\":\"\",\"relacao_familia_acolhido\":\"\",\"familiares_visita\":\"\",\"visita_em_casa\":\"\",\"dificuldades_visita_casa\":\"\",\"frequencia_visitas\":\"\",\"dificuldades_visitas_instituicao\":\"\",\"afinidade\":\"\",\"afinidade_nome\":\"\",\"afinidade_endereco\":\"\",\"risco_convivio\":\"\"}','[{\"chave\":\"data_nascimento\",\"secao\":\"acolhido\",\"rotulo\":\"Data de nascimento\",\"tipo\":\"date\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"cor\",\"secao\":\"acolhido\",\"rotulo\":\"Cor\",\"tipo\":\"select\",\"opcoes\":\"branca|preta|parda|amarela\",\"ordem\":20},{\"chave\":\"orientacao_sexual\",\"secao\":\"acolhido\",\"rotulo\":\"Orientação sexual\",\"tipo\":\"select\",\"opcoes\":\"heterossexual|homossexual|outros\",\"ordem\":30},{\"chave\":\"orientacao_sexual_outros\",\"secao\":\"acolhido\",\"rotulo\":\"Se outros, especificar\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":40},{\"chave\":\"estado_civil\",\"secao\":\"acolhido\",\"rotulo\":\"Estado civil\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":50},{\"chave\":\"escolaridade\",\"secao\":\"acolhido\",\"rotulo\":\"Escolaridade\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":60},{\"chave\":\"nacionalidade\",\"secao\":\"acolhido\",\"rotulo\":\"Nacionalidade\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":70},{\"chave\":\"naturalidade\",\"secao\":\"acolhido\",\"rotulo\":\"Naturalidade\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":80},{\"chave\":\"origem_encaminhamento\",\"secao\":\"acolhimento\",\"origem\":\"planejamento\",\"rotulo\":\"Origem do encaminhamento\",\"tipo\":\"multi\",\"opcoes\":\"Ministério Público|Judiciário|CREAS\",\"ordem\":10},{\"chave\":\"motivo_acolhimento\",\"secao\":\"acolhimento\",\"origem\":\"planejamento\",\"rotulo\":\"Motivo do acolhimento\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"numero_processo\",\"secao\":\"acolhimento\",\"origem\":\"planejamento\",\"rotulo\":\"Número do processo\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":30},{\"chave\":\"impedimento_visitacao\",\"secao\":\"acolhimento\",\"origem\":\"planejamento\",\"rotulo\":\"Há impedimento ou proibição de visitação?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":40},{\"chave\":\"quem_impedido\",\"secao\":\"acolhimento\",\"origem\":\"planejamento\",\"rotulo\":\"Quem está impedido de visitar?\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":50},{\"chave\":\"beneficios\",\"secao\":\"beneficios\",\"origem\":\"planejamento\",\"rotulo\":\"Renda e benefícios\",\"tipo\":\"benefits\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"beneficio_atividade\",\"secao\":\"beneficios\",\"origem\":\"planejamento\",\"rotulo\":\"Atividade laborativa remunerada: qual?\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"beneficio_outro\",\"secao\":\"beneficios\",\"origem\":\"planejamento\",\"rotulo\":\"Outro benefício ou renda\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":30},{\"chave\":\"administra_recursos\",\"secao\":\"beneficios\",\"origem\":\"planejamento\",\"rotulo\":\"O acolhido administra seus recursos?\",\"tipo\":\"select\",\"opcoes\":\"sim, sozinho|sim, com auxílio|não\",\"ordem\":40},{\"chave\":\"responsavel_recursos\",\"secao\":\"beneficios\",\"origem\":\"planejamento\",\"rotulo\":\"Pessoa que administra ou auxilia na administração dos recursos\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":50},{\"chave\":\"composicao_familiar\",\"secao\":\"composicao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Composição familiar\",\"tipo\":\"family\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"documentos\",\"secao\":\"documentos\",\"origem\":\"planejamento\",\"rotulo\":\"Situação documental\",\"tipo\":\"documents\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"plano_atividades\",\"secao\":\"plano\",\"origem\":\"planejamento\",\"rotulo\":\"Plano de atividades\",\"tipo\":\"activities\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"ficha_evolucao\",\"secao\":\"plano\",\"origem\":\"planejamento\",\"rotulo\":\"Ficha de evolução\",\"tipo\":\"evolution\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"procedimentos_familia\",\"secao\":\"procedimentos\",\"origem\":\"planejamento\",\"rotulo\":\"Procedimentos referentes à família\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"procedimentos_acolhido\",\"secao\":\"procedimentos\",\"origem\":\"planejamento\",\"rotulo\":\"Procedimentos referentes ao acolhido\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"rede_servicos\",\"secao\":\"rede\",\"origem\":\"planejamento\",\"rotulo\":\"Instituições e serviços da rede socioassistencial\",\"tipo\":\"network\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"observacao_rede\",\"secao\":\"rede\",\"origem\":\"planejamento\",\"rotulo\":\"Observação e\\/ou outros serviços\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"laudo_medico\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Possui laudo médico?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":10},{\"chave\":\"cid10\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"CID 10\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"transtorno_mental\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Doença ou transtorno mental?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":30},{\"chave\":\"qual_transtorno\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Qual doença ou transtorno?\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":40},{\"chave\":\"dst\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Possui alguma IST?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":50},{\"chave\":\"qual_dst\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Qual IST?\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":60},{\"chave\":\"restricao_alimentar\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Tem alguma restrição alimentar?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":70},{\"chave\":\"qual_restricao\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Se sim, qual?\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":80},{\"chave\":\"doencas_cronicas\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Outras doenças crônicas\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":90},{\"chave\":\"especialistas_tratamentos\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Médicos especialistas com quem já se tratou\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":100},{\"chave\":\"especialistas_necessarios\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Médicos especialistas de que necessita atualmente\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":110},{\"chave\":\"orteses_proteses\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Necessita de órteses e próteses? Quais?\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":120},{\"chave\":\"cuidados_personalizados\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Necessita de cuidados personalizados? Quais?\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":130},{\"chave\":\"envolvimento_substancias\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Há indícios de envolvimento do acolhido com\",\"tipo\":\"multi\",\"opcoes\":\"álcool|tabaco|outras drogas|outros\",\"ordem\":140},{\"chave\":\"envolvimento_outros\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Outros envolvimentos: especificar\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":150},{\"chave\":\"documentos_familia\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"A família possui todos os documentos pessoais?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":10},{\"chave\":\"renda_familiar\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Renda familiar\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"institucionalizacao_familiar\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Há institucionalização de membro da família nuclear?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":30},{\"chave\":\"local_institucionalizacao\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Local da institucionalização\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":40},{\"chave\":\"encaminhamento_rede\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Encaminhamento da família para a rede intersetorial do município\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":50},{\"chave\":\"relacao_familia_acolhido\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Relação família\\/acolhido\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":60},{\"chave\":\"familiares_visita\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Quais familiares inicialmente visitarão o acolhido?\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":70},{\"chave\":\"visita_em_casa\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"A família pode receber visita do acolhido em casa?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":80},{\"chave\":\"dificuldades_visita_casa\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Dificuldades para receber visita em casa\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":90},{\"chave\":\"frequencia_visitas\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Frequência de visitas na instituição\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":100},{\"chave\":\"dificuldades_visitas_instituicao\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Dificuldades para realizar visitas na instituição\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":110},{\"chave\":\"afinidade\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Há familiar ou amigo com quem tem mais afinidade?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":120},{\"chave\":\"afinidade_nome\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Nome do familiar ou amigo\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":130},{\"chave\":\"afinidade_endereco\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Endereço do familiar ou amigo\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":140},{\"chave\":\"risco_convivio\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"A manutenção do convívio familiar oferece risco? Por quê?\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":150},{\"chave\":\"pia_servico_nome\",\"secao\":\"identificacao_servico\",\"origem\":\"planejamento\",\"rotulo\":\"Nome do serviço de acolhimento institucional\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"pia_servico_endereco\",\"secao\":\"identificacao_servico\",\"origem\":\"planejamento\",\"rotulo\":\"Endereço do serviço de acolhimento\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"pia_servico_telefone\",\"secao\":\"identificacao_servico\",\"origem\":\"planejamento\",\"rotulo\":\"Telefone do serviço\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":30}]',1,3,'2026-10-13 14:43:15','2026-11-23 16:32:56'),(2,9,'2026-11-17','{\"plano_atividades\":{\"convivencia\":\"PLANO FICTICIO PARA TESTE; NAO CONSTITUI ORIENTACAO DE CUIDADO.\",\"desenvolvimento\":\"PLANO FICTICIO PARA TESTE; NAO CONSTITUI ORIENTACAO DE CUIDADO.\",\"documentacao\":\"PLANO FICTICIO PARA TESTE; NAO CONSTITUI ORIENTACAO DE CUIDADO.\",\"esporte_cultura\":\"PLANO FICTICIO PARA TESTE; NAO CONSTITUI ORIENTACAO DE CUIDADO.\",\"geracao_renda\":\"PLANO FICTICIO PARA TESTE; NAO CONSTITUI ORIENTACAO DE CUIDADO.\",\"mobilizacao_rede\":\"PLANO FICTICIO PARA TESTE; NAO CONSTITUI ORIENTACAO DE CUIDADO.\",\"transferencia_renda\":\"PLANO FICTICIO PARA TESTE; NAO CONSTITUI ORIENTACAO DE CUIDADO.\",\"vinculos\":\"PLANO FICTICIO PARA TESTE; NAO CONSTITUI ORIENTACAO DE CUIDADO.\",\"saude\":\"PLANO FICTICIO PARA TESTE; NAO CONSTITUI ORIENTACAO DE CUIDADO.\",\"servicos\":\"PLANO FICTICIO PARA TESTE; NAO CONSTITUI ORIENTACAO DE CUIDADO.\"},\"procedimentos_familia\":\"PLANEJAMENTO FICTICIO PARA TESTE; NAO CONSTITUI ORIENTACAO DE CUIDADO.\",\"procedimentos_acolhido\":\"PLANEJAMENTO FICTICIO PARA TESTE; NAO CONSTITUI ORIENTACAO DE CUIDADO.\",\"origem_encaminhamento\":[],\"beneficios\":[],\"composicao_familiar\":[],\"rede_servicos\":[],\"envolvimento_substancias\":[],\"ficha_evolucao\":[],\"transtorno_mental\":\"\",\"qual_transtorno\":\"\"}','[{\"chave\":\"plano_atividades\",\"secao\":\"plano\",\"origem\":\"planejamento\",\"rotulo\":\"Plano de atividades\",\"tipo\":\"activities\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"procedimentos_familia\",\"secao\":\"procedimentos\",\"origem\":\"planejamento\",\"rotulo\":\"Procedimentos referentes à família\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"procedimentos_acolhido\",\"secao\":\"procedimentos\",\"origem\":\"planejamento\",\"rotulo\":\"Procedimentos referentes ao acolhido\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"origem_encaminhamento\",\"secao\":\"acolhimento\",\"origem\":\"planejamento\",\"rotulo\":\"Origem do encaminhamento\",\"tipo\":\"multi\",\"opcoes\":\"Ministério Público|Judiciário|CREAS\",\"ordem\":10},{\"chave\":\"motivo_acolhimento\",\"secao\":\"acolhimento\",\"origem\":\"planejamento\",\"rotulo\":\"Motivo do acolhimento\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"numero_processo\",\"secao\":\"acolhimento\",\"origem\":\"planejamento\",\"rotulo\":\"Número do processo\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":30},{\"chave\":\"impedimento_visitacao\",\"secao\":\"acolhimento\",\"origem\":\"planejamento\",\"rotulo\":\"Há impedimento ou proibição de visitação?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":40},{\"chave\":\"quem_impedido\",\"secao\":\"acolhimento\",\"origem\":\"planejamento\",\"rotulo\":\"Quem está impedido de visitar?\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":50},{\"chave\":\"beneficios\",\"secao\":\"beneficios\",\"origem\":\"planejamento\",\"rotulo\":\"Renda e benefícios\",\"tipo\":\"benefits\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"beneficio_atividade\",\"secao\":\"beneficios\",\"origem\":\"planejamento\",\"rotulo\":\"Atividade laborativa remunerada: qual?\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"beneficio_outro\",\"secao\":\"beneficios\",\"origem\":\"planejamento\",\"rotulo\":\"Outro benefício ou renda\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":30},{\"chave\":\"administra_recursos\",\"secao\":\"beneficios\",\"origem\":\"planejamento\",\"rotulo\":\"O acolhido administra seus recursos?\",\"tipo\":\"select\",\"opcoes\":\"sim, sozinho|sim, com auxílio|não\",\"ordem\":40},{\"chave\":\"responsavel_recursos\",\"secao\":\"beneficios\",\"origem\":\"planejamento\",\"rotulo\":\"Pessoa que administra ou auxilia na administração dos recursos\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":50},{\"chave\":\"composicao_familiar\",\"secao\":\"composicao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Composição familiar\",\"tipo\":\"family\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"documentos\",\"secao\":\"documentos\",\"origem\":\"planejamento\",\"rotulo\":\"Situação documental\",\"tipo\":\"documents\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"rede_servicos\",\"secao\":\"rede\",\"origem\":\"planejamento\",\"rotulo\":\"Instituições e serviços da rede socioassistencial\",\"tipo\":\"network\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"observacao_rede\",\"secao\":\"rede\",\"origem\":\"planejamento\",\"rotulo\":\"Observação e\\/ou outros serviços\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"laudo_medico\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Possui laudo médico?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":10},{\"chave\":\"cid10\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"CID 10\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"transtorno_mental\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Doença ou transtorno mental?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":30},{\"chave\":\"qual_transtorno\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Qual doença ou transtorno?\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":40},{\"chave\":\"dst\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Possui alguma IST?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":50},{\"chave\":\"qual_dst\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Qual IST?\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":60},{\"chave\":\"restricao_alimentar\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Tem alguma restrição alimentar?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":70},{\"chave\":\"qual_restricao\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Se sim, qual?\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":80},{\"chave\":\"doencas_cronicas\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Outras doenças crônicas\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":90},{\"chave\":\"especialistas_tratamentos\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Médicos especialistas com quem já se tratou\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":100},{\"chave\":\"especialistas_necessarios\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Médicos especialistas de que necessita atualmente\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":110},{\"chave\":\"orteses_proteses\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Necessita de órteses e próteses? Quais?\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":120},{\"chave\":\"cuidados_personalizados\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Necessita de cuidados personalizados? Quais?\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":130},{\"chave\":\"envolvimento_substancias\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Há indícios de envolvimento do acolhido com\",\"tipo\":\"multi\",\"opcoes\":\"álcool|tabaco|outras drogas|outros\",\"ordem\":140},{\"chave\":\"envolvimento_outros\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Outros envolvimentos: especificar\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":150},{\"chave\":\"documentos_familia\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"A família possui todos os documentos pessoais?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":10},{\"chave\":\"renda_familiar\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Renda familiar\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"institucionalizacao_familiar\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Há institucionalização de membro da família nuclear?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":30},{\"chave\":\"local_institucionalizacao\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Local da institucionalização\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":40},{\"chave\":\"encaminhamento_rede\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Encaminhamento da família para a rede intersetorial do município\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":50},{\"chave\":\"relacao_familia_acolhido\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Relação família\\/acolhido\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":60},{\"chave\":\"familiares_visita\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Quais familiares inicialmente visitarão o acolhido?\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":70},{\"chave\":\"visita_em_casa\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"A família pode receber visita do acolhido em casa?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":80},{\"chave\":\"dificuldades_visita_casa\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Dificuldades para receber visita em casa\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":90},{\"chave\":\"frequencia_visitas\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Frequência de visitas na instituição\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":100},{\"chave\":\"dificuldades_visitas_instituicao\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Dificuldades para realizar visitas na instituição\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":110},{\"chave\":\"afinidade\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Há familiar ou amigo com quem tem mais afinidade?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":120},{\"chave\":\"afinidade_nome\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Nome do familiar ou amigo\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":130},{\"chave\":\"afinidade_endereco\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Endereço do familiar ou amigo\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":140},{\"chave\":\"risco_convivio\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"A manutenção do convívio familiar oferece risco? Por quê?\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":150},{\"chave\":\"pia_servico_nome\",\"secao\":\"identificacao_servico\",\"origem\":\"planejamento\",\"rotulo\":\"Nome do serviço de acolhimento institucional\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"pia_servico_endereco\",\"secao\":\"identificacao_servico\",\"origem\":\"planejamento\",\"rotulo\":\"Endereço do serviço de acolhimento\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"pia_servico_telefone\",\"secao\":\"identificacao_servico\",\"origem\":\"planejamento\",\"rotulo\":\"Telefone do serviço\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":30},{\"chave\":\"ficha_evolucao\",\"secao\":\"plano\",\"origem\":\"planejamento\",\"rotulo\":\"Ficha de evolução\",\"tipo\":\"evolution\",\"opcoes\":\"\",\"ordem\":20}]',NULL,1,'2026-10-13 15:50:31','2026-11-22 12:47:29'),(3,1,NULL,'{}','[]',NULL,NULL,NULL,NULL),(4,2,'2026-11-22','{}','[]',NULL,1,NULL,'2026-11-22 11:28:56'),(5,5,NULL,'{}','[]',NULL,NULL,NULL,NULL),(6,7,NULL,'{}','[]',NULL,NULL,NULL,NULL),(7,8,NULL,'{}','[]',NULL,NULL,NULL,NULL),(8,4,NULL,'{}','[]',NULL,NULL,NULL,NULL),(9,6,NULL,'{}','[]',NULL,NULL,NULL,NULL),(10,10,'2026-11-22','{\"origem_encaminhamento\":[\"CREAS\"],\"numero_processo\":\"\",\"impedimento_visitacao\":\"\",\"quem_impedido\":\"\",\"beneficios\":[],\"composicao_familiar\":[],\"plano_atividades\":{\"convivencia\":\"\",\"desenvolvimento\":\"\",\"documentacao\":\"\",\"esporte_cultura\":\"\",\"geracao_renda\":\"\",\"mobilizacao_rede\":\"\",\"transferencia_renda\":\"\",\"vinculos\":\"\",\"saude\":\"\",\"servicos\":\"\"},\"procedimentos_familia\":\"\",\"procedimentos_acolhido\":\"\",\"rede_servicos\":[],\"observacao_rede\":\"\",\"envolvimento_substancias\":[],\"documentos_familia\":\"\",\"renda_familiar\":\"\",\"institucionalizacao_familiar\":\"\",\"local_institucionalizacao\":\"\",\"encaminhamento_rede\":\"\",\"relacao_familia_acolhido\":\"\",\"familiares_visita\":\"\",\"visita_em_casa\":\"\",\"dificuldades_visita_casa\":\"\",\"frequencia_visitas\":\"\",\"dificuldades_visitas_instituicao\":\"\",\"afinidade\":\"\",\"afinidade_nome\":\"\",\"afinidade_endereco\":\"\",\"risco_convivio\":\"\",\"ficha_evolucao\":[],\"transtorno_mental\":\"\",\"qual_transtorno\":\"\"}','[{\"chave\":\"origem_encaminhamento\",\"secao\":\"acolhimento\",\"origem\":\"planejamento\",\"rotulo\":\"Origem do encaminhamento\",\"tipo\":\"multi\",\"opcoes\":\"Ministério Público|Judiciário|CREAS\",\"ordem\":10},{\"chave\":\"motivo_acolhimento\",\"secao\":\"acolhimento\",\"origem\":\"planejamento\",\"rotulo\":\"Motivo do acolhimento\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"numero_processo\",\"secao\":\"acolhimento\",\"origem\":\"planejamento\",\"rotulo\":\"Número do processo\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":30},{\"chave\":\"impedimento_visitacao\",\"secao\":\"acolhimento\",\"origem\":\"planejamento\",\"rotulo\":\"Há impedimento ou proibição de visitação?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":40},{\"chave\":\"quem_impedido\",\"secao\":\"acolhimento\",\"origem\":\"planejamento\",\"rotulo\":\"Quem está impedido de visitar?\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":50},{\"chave\":\"beneficios\",\"secao\":\"beneficios\",\"origem\":\"planejamento\",\"rotulo\":\"Renda e benefícios\",\"tipo\":\"benefits\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"beneficio_atividade\",\"secao\":\"beneficios\",\"origem\":\"planejamento\",\"rotulo\":\"Atividade laborativa remunerada: qual?\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"beneficio_outro\",\"secao\":\"beneficios\",\"origem\":\"planejamento\",\"rotulo\":\"Outro benefício ou renda\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":30},{\"chave\":\"administra_recursos\",\"secao\":\"beneficios\",\"origem\":\"planejamento\",\"rotulo\":\"O acolhido administra seus recursos?\",\"tipo\":\"select\",\"opcoes\":\"sim, sozinho|sim, com auxílio|não\",\"ordem\":40},{\"chave\":\"responsavel_recursos\",\"secao\":\"beneficios\",\"origem\":\"planejamento\",\"rotulo\":\"Pessoa que administra ou auxilia na administração dos recursos\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":50},{\"chave\":\"composicao_familiar\",\"secao\":\"composicao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Composição familiar\",\"tipo\":\"family\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"documentos\",\"secao\":\"documentos\",\"origem\":\"planejamento\",\"rotulo\":\"Situação documental\",\"tipo\":\"documents\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"plano_atividades\",\"secao\":\"plano\",\"origem\":\"planejamento\",\"rotulo\":\"Plano de atividades\",\"tipo\":\"activities\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"procedimentos_familia\",\"secao\":\"procedimentos\",\"origem\":\"planejamento\",\"rotulo\":\"Procedimentos referentes à família\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"procedimentos_acolhido\",\"secao\":\"procedimentos\",\"origem\":\"planejamento\",\"rotulo\":\"Procedimentos referentes ao acolhido\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"rede_servicos\",\"secao\":\"rede\",\"origem\":\"planejamento\",\"rotulo\":\"Instituições e serviços da rede socioassistencial\",\"tipo\":\"network\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"observacao_rede\",\"secao\":\"rede\",\"origem\":\"planejamento\",\"rotulo\":\"Observação e\\/ou outros serviços\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"laudo_medico\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Possui laudo médico?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":10},{\"chave\":\"cid10\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"CID 10\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"transtorno_mental\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Doença ou transtorno mental?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":30},{\"chave\":\"qual_transtorno\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Qual doença ou transtorno?\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":40},{\"chave\":\"dst\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Possui alguma IST?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":50},{\"chave\":\"qual_dst\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Qual IST?\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":60},{\"chave\":\"restricao_alimentar\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Tem alguma restrição alimentar?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":70},{\"chave\":\"qual_restricao\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Se sim, qual?\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":80},{\"chave\":\"doencas_cronicas\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Outras doenças crônicas\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":90},{\"chave\":\"especialistas_tratamentos\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Médicos especialistas com quem já se tratou\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":100},{\"chave\":\"especialistas_necessarios\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Médicos especialistas de que necessita atualmente\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":110},{\"chave\":\"orteses_proteses\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Necessita de órteses e próteses? Quais?\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":120},{\"chave\":\"cuidados_personalizados\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Necessita de cuidados personalizados? Quais?\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":130},{\"chave\":\"envolvimento_substancias\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Há indícios de envolvimento do acolhido com\",\"tipo\":\"multi\",\"opcoes\":\"álcool|tabaco|outras drogas|outros\",\"ordem\":140},{\"chave\":\"envolvimento_outros\",\"secao\":\"saude\",\"origem\":\"planejamento\",\"rotulo\":\"Outros envolvimentos: especificar\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":150},{\"chave\":\"documentos_familia\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"A família possui todos os documentos pessoais?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":10},{\"chave\":\"renda_familiar\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Renda familiar\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"institucionalizacao_familiar\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Há institucionalização de membro da família nuclear?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":30},{\"chave\":\"local_institucionalizacao\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Local da institucionalização\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":40},{\"chave\":\"encaminhamento_rede\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Encaminhamento da família para a rede intersetorial do município\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":50},{\"chave\":\"relacao_familia_acolhido\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Relação família\\/acolhido\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":60},{\"chave\":\"familiares_visita\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Quais familiares inicialmente visitarão o acolhido?\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":70},{\"chave\":\"visita_em_casa\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"A família pode receber visita do acolhido em casa?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":80},{\"chave\":\"dificuldades_visita_casa\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Dificuldades para receber visita em casa\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":90},{\"chave\":\"frequencia_visitas\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Frequência de visitas na instituição\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":100},{\"chave\":\"dificuldades_visitas_instituicao\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Dificuldades para realizar visitas na instituição\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":110},{\"chave\":\"afinidade\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Há familiar ou amigo com quem tem mais afinidade?\",\"tipo\":\"select\",\"opcoes\":\"sim|não\",\"ordem\":120},{\"chave\":\"afinidade_nome\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Nome do familiar ou amigo\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":130},{\"chave\":\"afinidade_endereco\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"Endereço do familiar ou amigo\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":140},{\"chave\":\"risco_convivio\",\"secao\":\"situacao_familiar\",\"origem\":\"planejamento\",\"rotulo\":\"A manutenção do convívio familiar oferece risco? Por quê?\",\"tipo\":\"textarea\",\"opcoes\":\"\",\"ordem\":150},{\"chave\":\"pia_servico_nome\",\"secao\":\"identificacao_servico\",\"origem\":\"planejamento\",\"rotulo\":\"Nome do serviço de acolhimento institucional\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":10},{\"chave\":\"pia_servico_endereco\",\"secao\":\"identificacao_servico\",\"origem\":\"planejamento\",\"rotulo\":\"Endereço do serviço de acolhimento\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":20},{\"chave\":\"pia_servico_telefone\",\"secao\":\"identificacao_servico\",\"origem\":\"planejamento\",\"rotulo\":\"Telefone do serviço\",\"tipo\":\"text\",\"opcoes\":\"\",\"ordem\":30},{\"chave\":\"ficha_evolucao\",\"secao\":\"plano\",\"origem\":\"planejamento\",\"rotulo\":\"Ficha de evolução\",\"tipo\":\"evolution\",\"opcoes\":\"\",\"ordem\":20}]',NULL,1,NULL,'2026-11-22 12:47:19');
/*!40000 ALTER TABLE `pia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pia_assinaturas`
--

DROP TABLE IF EXISTS `pia_assinaturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pia_assinaturas` (
  `idAssinatura` int(11) NOT NULL AUTO_INCREMENT,
  `pia_idPIA` int(11) NOT NULL,
  `usuario_idUsuario` int(11) NOT NULL,
  `modo` varchar(20) NOT NULL,
  `nome_assinante` varchar(180) NOT NULL,
  `atuacao_profissional` varchar(120) DEFAULT NULL,
  `registro_profissional` varchar(40) DEFAULT NULL,
  `cargo_conselho` varchar(120) DEFAULT NULL,
  `inicio_mandato` date DEFAULT NULL,
  `fim_mandato` date DEFAULT NULL,
  `ordem` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`idAssinatura`),
  KEY `idx_pia_assinaturas_pia` (`pia_idPIA`,`ordem`),
  KEY `fk_pia_assinaturas_usuario` (`usuario_idUsuario`),
  CONSTRAINT `fk_pia_assinaturas_pia` FOREIGN KEY (`pia_idPIA`) REFERENCES `pia` (`idPIA`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pia_assinaturas_usuario` FOREIGN KEY (`usuario_idUsuario`) REFERENCES `usuario` (`idUsuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pia_assinaturas`
--

LOCK TABLES `pia_assinaturas` WRITE;
/*!40000 ALTER TABLE `pia_assinaturas` DISABLE KEYS */;
INSERT INTO `pia_assinaturas` VALUES (14,10,1,'ambos','Natan de Paula Batista','Coordenador',NULL,'Presidente','2025-07-09','2029-07-09',0),(15,10,3,'profissional','Fisio Fábio','Fisioterapeuta','12121',NULL,NULL,NULL,1),(16,10,4,'nome','Cuidador Teste',NULL,NULL,NULL,NULL,NULL,2),(17,2,3,'profissional','Fisio Fábio','Fisioterapeuta','12121',NULL,NULL,NULL,0),(18,2,1,'ambos','Natan de Paula Batista','Coordenador',NULL,'Presidente','2025-07-09','2029-07-09',1),(19,2,4,'nome','Cuidador Teste',NULL,NULL,NULL,NULL,NULL,2),(20,1,1,'ambos','Natan de Paula Batista','Coordenador',NULL,'Presidente','2025-07-09','2029-07-09',0),(21,1,3,'profissional','Fisio Fábio','Fisioterapeuta','12121',NULL,NULL,NULL,1);
/*!40000 ALTER TABLE `pia_assinaturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pia_campos`
--

DROP TABLE IF EXISTS `pia_campos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pia_campos` (
  `idCampo` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(80) NOT NULL,
  `secao` varchar(40) NOT NULL,
  `origem` enum('cadastro','planejamento') NOT NULL DEFAULT 'cadastro',
  `rotulo` varchar(180) NOT NULL,
  `tipo` varchar(30) NOT NULL,
  `opcoes` text DEFAULT NULL,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_por_login` int(11) DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idCampo`),
  UNIQUE KEY `uq_pia_campos_chave` (`chave`),
  KEY `idx_pia_campos_secao_ordem` (`secao`,`ativo`,`ordem`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pia_campos`
--

LOCK TABLES `pia_campos` WRITE;
/*!40000 ALTER TABLE `pia_campos` DISABLE KEYS */;
INSERT INTO `pia_campos` VALUES (1,'data_nascimento','acolhido','cadastro','Data de nascimento','date',NULL,10,1,NULL,'2026-10-13 14:09:59'),(2,'cor','acolhido','cadastro','Cor','select','branca|preta|parda|amarela',20,1,NULL,'2026-10-13 14:09:59'),(3,'orientacao_sexual','acolhido','cadastro','Orientação sexual','select','heterossexual|homossexual|outros',30,1,NULL,'2026-10-13 14:09:59'),(4,'orientacao_sexual_outros','acolhido','cadastro','Se outros, especificar','text',NULL,40,1,NULL,'2026-10-13 14:09:59'),(5,'estado_civil','acolhido','cadastro','Estado civil','text',NULL,50,1,NULL,'2026-10-13 14:09:59'),(6,'escolaridade','acolhido','cadastro','Escolaridade','text',NULL,60,1,NULL,'2026-10-13 14:09:59'),(7,'nacionalidade','acolhido','cadastro','Nacionalidade','text',NULL,70,1,NULL,'2026-10-13 14:09:59'),(8,'naturalidade','acolhido','cadastro','Naturalidade','text',NULL,80,1,NULL,'2026-10-13 14:09:59'),(9,'documentos','documentos','planejamento','Situação documental','documents',NULL,10,1,NULL,'2026-10-13 14:09:59'),(10,'beneficios','beneficios','planejamento','Renda e benefícios','benefits',NULL,10,1,NULL,'2026-10-13 14:09:59'),(11,'beneficio_atividade','beneficios','planejamento','Atividade laborativa remunerada: qual?','text',NULL,20,1,NULL,'2026-10-13 14:09:59'),(12,'beneficio_outro','beneficios','planejamento','Outro benefício ou renda','text',NULL,30,1,NULL,'2026-10-13 14:09:59'),(13,'administra_recursos','beneficios','planejamento','O acolhido administra seus recursos?','select','sim, sozinho|sim, com auxílio|não',40,1,NULL,'2026-10-13 14:09:59'),(14,'responsavel_recursos','beneficios','planejamento','Pessoa que administra ou auxilia na administração dos recursos','textarea',NULL,50,1,NULL,'2026-10-13 14:09:59'),(15,'laudo_medico','saude','planejamento','Possui laudo médico?','select','sim|não',10,1,NULL,'2026-10-13 14:09:59'),(16,'cid10','saude','planejamento','CID 10','text',NULL,20,1,NULL,'2026-10-13 14:09:59'),(17,'transtorno_mental','saude','planejamento','Doença ou transtorno mental?','select','sim|não',30,1,NULL,'2026-10-13 14:09:59'),(18,'qual_transtorno','saude','planejamento','Qual doença ou transtorno?','text',NULL,40,1,NULL,'2026-10-13 14:09:59'),(19,'dst','saude','planejamento','Tem alguma DST?','select','sim|não',50,1,NULL,'2026-10-13 14:09:59'),(20,'qual_dst','saude','planejamento','Se sim, qual?','text',NULL,60,1,NULL,'2026-10-13 14:09:59'),(21,'restricao_alimentar','saude','planejamento','Tem alguma restrição alimentar?','select','sim|não',70,1,NULL,'2026-10-13 14:09:59'),(22,'qual_restricao','saude','planejamento','Se sim, qual?','text',NULL,80,1,NULL,'2026-10-13 14:09:59'),(23,'doencas_cronicas','saude','planejamento','Outras doenças crônicas','textarea',NULL,90,1,NULL,'2026-10-13 14:09:59'),(24,'especialistas_tratamentos','saude','planejamento','Médicos especialistas com quem já se tratou','textarea',NULL,100,1,NULL,'2026-10-13 14:09:59'),(25,'especialistas_necessarios','saude','planejamento','Médicos especialistas de que necessita atualmente','textarea',NULL,110,1,NULL,'2026-10-13 14:09:59'),(26,'orteses_proteses','saude','planejamento','Necessita de órteses e próteses? Quais?','textarea',NULL,120,1,NULL,'2026-10-13 14:09:59'),(27,'cuidados_personalizados','saude','planejamento','Necessita de cuidados personalizados? Quais?','textarea',NULL,130,1,NULL,'2026-10-13 14:09:59'),(28,'envolvimento_substancias','saude','planejamento','Há indícios de envolvimento do acolhido com','multi','álcool|tabaco|outras drogas|outros',140,1,NULL,'2026-10-13 14:09:59'),(29,'envolvimento_outros','saude','planejamento','Outros envolvimentos: especificar','text',NULL,150,1,NULL,'2026-10-13 14:09:59'),(30,'composicao_familiar','composicao_familiar','planejamento','Composição familiar','family',NULL,10,1,NULL,'2026-10-13 14:09:59'),(31,'documentos_familia','situacao_familiar','planejamento','A família possui todos os documentos pessoais?','select','sim|não',10,1,NULL,'2026-10-13 14:09:59'),(32,'renda_familiar','situacao_familiar','planejamento','Renda familiar','textarea',NULL,20,1,NULL,'2026-10-13 14:09:59'),(33,'institucionalizacao_familiar','situacao_familiar','planejamento','Há institucionalização de membro da família nuclear?','select','sim|não',30,1,NULL,'2026-10-13 14:09:59'),(34,'local_institucionalizacao','situacao_familiar','planejamento','Local da institucionalização','text',NULL,40,1,NULL,'2026-10-13 14:09:59'),(35,'encaminhamento_rede','situacao_familiar','planejamento','Encaminhamento da família para a rede intersetorial do município','textarea',NULL,50,1,NULL,'2026-10-13 14:09:59'),(36,'relacao_familia_acolhido','situacao_familiar','planejamento','Relação família/acolhido','textarea',NULL,60,1,NULL,'2026-10-13 14:09:59'),(37,'familiares_visita','situacao_familiar','planejamento','Quais familiares inicialmente visitarão o acolhido?','textarea',NULL,70,1,NULL,'2026-10-13 14:09:59'),(38,'visita_em_casa','situacao_familiar','planejamento','A família pode receber visita do acolhido em casa?','select','sim|não',80,1,NULL,'2026-10-13 14:09:59'),(39,'dificuldades_visita_casa','situacao_familiar','planejamento','Dificuldades para receber visita em casa','textarea',NULL,90,1,NULL,'2026-10-13 14:09:59'),(40,'frequencia_visitas','situacao_familiar','planejamento','Frequência de visitas na instituição','text',NULL,100,1,NULL,'2026-10-13 14:09:59'),(41,'dificuldades_visitas_instituicao','situacao_familiar','planejamento','Dificuldades para realizar visitas na instituição','textarea',NULL,110,1,NULL,'2026-10-13 14:09:59'),(42,'afinidade','situacao_familiar','planejamento','Há familiar ou amigo com quem tem mais afinidade?','select','sim|não',120,1,NULL,'2026-10-13 14:09:59'),(43,'afinidade_nome','situacao_familiar','planejamento','Nome do familiar ou amigo','text',NULL,130,1,NULL,'2026-10-13 14:09:59'),(44,'afinidade_endereco','situacao_familiar','planejamento','Endereço do familiar ou amigo','textarea',NULL,140,1,NULL,'2026-10-13 14:09:59'),(45,'risco_convivio','situacao_familiar','planejamento','A manutenção do convívio familiar oferece risco? Por quê?','textarea',NULL,150,1,NULL,'2026-10-13 14:09:59'),(46,'origem_encaminhamento','acolhimento','planejamento','Origem do encaminhamento','multi','Ministério Público|Judiciário|CREAS',10,1,NULL,'2026-10-13 14:09:59'),(47,'motivo_acolhimento','acolhimento','planejamento','Motivo do acolhimento','textarea',NULL,20,1,NULL,'2026-10-13 14:09:59'),(48,'numero_processo','acolhimento','planejamento','Número do processo','text',NULL,30,1,NULL,'2026-10-13 14:09:59'),(49,'impedimento_visitacao','acolhimento','planejamento','Há impedimento ou proibição de visitação?','select','sim|não',40,1,NULL,'2026-10-13 14:09:59'),(50,'quem_impedido','acolhimento','planejamento','Quem está impedido de visitar?','text',NULL,50,1,NULL,'2026-10-13 14:09:59'),(51,'rede_servicos','rede','planejamento','Instituições e serviços da rede socioassistencial','network',NULL,10,1,NULL,'2026-10-13 14:09:59'),(52,'observacao_rede','rede','planejamento','Observação e/ou outros serviços','textarea',NULL,20,1,NULL,'2026-10-13 14:09:59'),(53,'procedimentos_familia','procedimentos','planejamento','Procedimentos referentes à família','textarea',NULL,10,1,NULL,'2026-10-13 14:09:59'),(54,'procedimentos_acolhido','procedimentos','planejamento','Procedimentos referentes ao acolhido','textarea',NULL,20,1,NULL,'2026-10-13 14:09:59'),(55,'plano_atividades','plano','planejamento','Plano de atividades','activities',NULL,10,1,NULL,'2026-10-13 14:09:59'),(56,'ficha_evolucao','plano','planejamento','Ficha de evolução','evolution',NULL,20,1,NULL,'2026-10-13 14:09:59'),(57,'personalizado_1e59514e75e8cc25','acolhido','cadastro','Nome social','text',NULL,90,0,1,'2026-10-13 14:56:07'),(58,'pia_servico_nome','identificacao_servico','planejamento','Nome do serviço de acolhimento institucional','text',NULL,10,1,NULL,'2026-11-15 23:20:25'),(59,'pia_servico_endereco','identificacao_servico','planejamento','Endereço do serviço de acolhimento','text',NULL,20,1,NULL,'2026-11-15 23:20:25'),(60,'pia_servico_telefone','identificacao_servico','planejamento','Telefone do serviço','text',NULL,30,1,NULL,'2026-11-15 23:20:25'),(61,'pia_nome_acolhido','acolhido','cadastro','Nome completo','text',NULL,110,1,NULL,'2026-11-15 23:20:25'),(62,'pia_cpf_acolhido','acolhido','cadastro','CPF','text',NULL,120,1,NULL,'2026-11-15 23:20:25'),(63,'pia_matricula_acolhido','acolhido','cadastro','Matrícula','text',NULL,130,1,NULL,'2026-11-15 23:20:25'),(64,'pia_data_acolhimento','acolhido','cadastro','Data de acolhimento','date',NULL,140,1,NULL,'2026-11-15 23:20:25'),(65,'pia_data_desligamento','acolhido','cadastro','Data de desligamento','date',NULL,145,1,NULL,'2026-11-15 23:20:25'),(66,'pia_motivo_desligamento','acolhido','cadastro','Motivo do desligamento','textarea',NULL,146,1,NULL,'2026-11-15 23:20:25'),(67,'pia_origem_acolhimento','acolhido','cadastro','Origem do acolhimento','text',NULL,150,1,NULL,'2026-11-15 23:20:25'),(68,'pia_nome_mae','acolhido','cadastro','Nome da mãe','text',NULL,160,1,NULL,'2026-11-15 23:20:25'),(69,'pia_nome_pai','acolhido','cadastro','Nome do pai','text',NULL,170,1,NULL,'2026-11-15 23:20:25'),(70,'pia_situacao_acolhido','acolhido','cadastro','Situação do acolhimento','text',NULL,180,1,NULL,'2026-11-15 23:20:25'),(71,'pia_plano_assistencial','acolhido','cadastro','Plano assistencial ou funerário','textarea',NULL,190,1,NULL,'2026-11-15 23:20:25'),(72,'pia_info_saude','acolhido','cadastro','Comorbidades, alergias e restrições','textarea',NULL,200,1,NULL,'2026-11-15 23:20:25'),(73,'pia_info_nutricional','acolhido','cadastro','Informações alimentares e nutricionais','textarea',NULL,210,1,NULL,'2026-11-15 23:20:25'),(74,'pia_motivo_institucionalizacao','acolhido','cadastro','Motivo da institucionalização','textarea',NULL,220,1,NULL,'2026-11-15 23:20:25'),(75,'pia_historico_acolhido','acolhido','cadastro','Histórico e observações gerais','textarea',NULL,230,1,NULL,'2026-11-15 23:20:25'),(76,'pia_endereco_origem','acolhido','cadastro','Endereço de origem','text',NULL,240,1,NULL,'2026-11-15 23:20:25'),(78,'sexo','acolhido','cadastro','Sexo','text',NULL,115,1,NULL,'2026-11-22 11:23:59');
/*!40000 ALTER TABLE `pia_campos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pia_secoes`
--

DROP TABLE IF EXISTS `pia_secoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pia_secoes` (
  `chave` varchar(40) NOT NULL,
  `titulo` varchar(180) NOT NULL,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `fixa` tinyint(1) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_por_login` int(11) DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`chave`),
  KEY `idx_pia_secoes_ordem` (`ordem`,`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pia_secoes`
--

LOCK TABLES `pia_secoes` WRITE;
/*!40000 ALTER TABLE `pia_secoes` DISABLE KEYS */;
INSERT INTO `pia_secoes` VALUES ('acolhido','2. INFORMAÇÕES SOBRE O ACOLHIDO',20,1,1,NULL,'2026-11-15 23:20:25'),('acolhimento','8. INFORMAÇÕES ACERCA DO ACOLHIMENTO',70,0,1,NULL,'2026-11-15 23:20:25'),('beneficios','4. RENDA/BENEFÍCIOS',31,1,1,NULL,'2026-11-15 23:20:25'),('composicao_familiar','6. COMPOSIÇÃO FAMILIAR',50,1,1,NULL,'2026-11-15 23:20:25'),('documentos','3. SITUAÇÃO DOCUMENTAL',30,1,1,NULL,'2026-11-15 23:20:25'),('identificacao_servico','1. IDENTIFICAÇÃO DO SERVIÇO DE ACOLHIMENTO',10,1,1,NULL,'2026-11-15 23:20:25'),('plano','11. PLANO DE ATIVIDADES E FICHA DE EVOLUÇÃO',100,0,1,NULL,'2026-11-15 23:20:25'),('procedimentos','10. PROCEDIMENTOS REALIZADOS PELA EQUIPE TÉCNICA E COORDENAÇÃO DO SERVIÇO',90,0,1,NULL,'2026-11-15 23:20:25'),('rede','9. REDE SOCIOASSISTENCIAL',80,0,1,NULL,'2026-11-15 23:20:25'),('saude','5. SAÚDE',40,1,1,NULL,'2026-11-15 23:20:25'),('situacao_familiar','7. SITUAÇÃO FAMILIAR',60,0,1,NULL,'2026-11-15 23:20:25');
/*!40000 ALTER TABLE `pia_secoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pia_servico_configuracao`
--

DROP TABLE IF EXISTS `pia_servico_configuracao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pia_servico_configuracao` (
  `id` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `nome` varchar(180) NOT NULL DEFAULT 'Associação Beneficente Dias Melhores – ABDM',
  `endereco` varchar(255) NOT NULL DEFAULT 'Rua Mariana de Souza Barros, 01. Celina – Alegre/ES',
  `telefone` varchar(60) DEFAULT '28 99937-8167',
  `atualizado_por_login` int(11) DEFAULT NULL,
  `atualizado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_pia_servico_config_login` (`atualizado_por_login`),
  CONSTRAINT `fk_pia_servico_config_login` FOREIGN KEY (`atualizado_por_login`) REFERENCES `login` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pia_servico_configuracao`
--

LOCK TABLES `pia_servico_configuracao` WRITE;
/*!40000 ALTER TABLE `pia_servico_configuracao` DISABLE KEYS */;
INSERT INTO `pia_servico_configuracao` VALUES (1,'Associação Beneficente Dias Melhores – ABDM','Rua Mariana de Souza Barros, 01. Celina – Alegre/ES','(28) 99937-8167',1,'2026-11-22 13:09:49');
/*!40000 ALTER TABLE `pia_servico_configuracao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prescricao_horarios`
--

DROP TABLE IF EXISTS `prescricao_horarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prescricao_horarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prescricao_id` int(11) NOT NULL,
  `horario` time DEFAULT NULL,
  `periodo_id` int(11) NOT NULL,
  `quantidade_por_dose` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unidade_dose` varchar(50) NOT NULL DEFAULT 'comprimido',
  PRIMARY KEY (`id`),
  KEY `fk_horarios_prescricao` (`prescricao_id`),
  KEY `fk_ph_periodo` (`periodo_id`),
  CONSTRAINT `fk_horarios_prescricao` FOREIGN KEY (`prescricao_id`) REFERENCES `prescricoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ph_periodo` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescricao_horarios`
--

LOCK TABLES `prescricao_horarios` WRITE;
/*!40000 ALTER TABLE `prescricao_horarios` DISABLE KEYS */;
INSERT INTO `prescricao_horarios` VALUES (57,19,'11:00:00',2,1.00,'comprimido'),(61,22,'09:00:00',1,1.00,'comprimido'),(66,21,'14:00:00',3,1.00,'comprimido'),(67,21,'20:00:00',5,1.00,'comprimido'),(68,18,'08:00:00',1,1.00,'ml'),(69,18,'20:00:00',5,1.00,'ml'),(70,20,'09:00:00',1,1.00,'comprimido'),(71,20,'20:00:00',5,1.00,'comprimido'),(72,27,'11:00:00',2,1.00,'comprimido'),(75,28,'08:00:00',1,2.00,'ml'),(76,28,'20:00:00',5,1.00,'ml'),(77,29,'08:00:00',1,1.00,'comprimido'),(78,30,'08:00:00',1,1.00,'comprimido'),(84,24,'09:00:00',1,1.50,'comprimido'),(85,26,'20:00:00',1,1.00,'gota'),(86,31,'20:00:00',10,1.00,'gota');
/*!40000 ALTER TABLE `prescricao_horarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prescricoes`
--

DROP TABLE IF EXISTS `prescricoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prescricoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acolhido_id` int(11) NOT NULL,
  `medicamento_id` int(11) NOT NULL,
  `tipo_posologia` enum('horario_fixo','intervalo') NOT NULL DEFAULT 'horario_fixo',
  `intervalo_horas` int(11) DEFAULT NULL COMMENT 'Ex: 8 (para de 8 em 8 horas)',
  `intervalo_inicio` time DEFAULT NULL COMMENT 'Ex: 07:00:00 (hor??rio do primeiro uso do dia)',
  `tipo_uso` enum('continuo','eventual','periodo_definido','se_necessario') NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date DEFAULT NULL,
  `doses_por_dia` int(11) NOT NULL COMMENT 'Quantas vezes ao dia o medicamento ?? administrado',
  `medico_responsavel` varchar(255) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `instrucao_posologia` text DEFAULT NULL,
  `status` enum('ativa','suspensa','finalizada') NOT NULL DEFAULT 'ativa',
  `data_status_alterado` datetime DEFAULT NULL,
  `motivo_suspensao` text DEFAULT NULL,
  `suspensao_data_inicio` date DEFAULT NULL,
  `suspensao_data_fim` date DEFAULT NULL,
  `via_administracao` varchar(100) DEFAULT NULL,
  `quantidade_por_dose` decimal(10,2) DEFAULT NULL,
  `unidade_dose` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `acolhido_id` (`acolhido_id`),
  KEY `medicamento_id` (`medicamento_id`),
  CONSTRAINT `prescricoes_ibfk_1` FOREIGN KEY (`acolhido_id`) REFERENCES `acolhido` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prescricoes_ibfk_2` FOREIGN KEY (`medicamento_id`) REFERENCES `medicamentos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescricoes`
--

LOCK TABLES `prescricoes` WRITE;
/*!40000 ALTER TABLE `prescricoes` DISABLE KEYS */;
INSERT INTO `prescricoes` VALUES (18,6,13,'horario_fixo',NULL,NULL,'continuo','2026-06-14',NULL,2,'Dr. Rafael','Antitermico',NULL,'ativa',NULL,NULL,NULL,NULL,'Oral',NULL,NULL),(19,6,16,'horario_fixo',NULL,NULL,'continuo','2026-06-14',NULL,1,NULL,NULL,NULL,'ativa',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(20,6,15,'horario_fixo',NULL,NULL,'continuo','2026-06-14',NULL,2,NULL,NULL,NULL,'suspensa','2026-11-23 23:45:47','teste',NULL,NULL,NULL,NULL,NULL),(21,8,15,'horario_fixo',NULL,NULL,'continuo','2026-06-23',NULL,2,NULL,NULL,NULL,'ativa',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(22,5,18,'horario_fixo',NULL,NULL,'continuo','2026-06-09',NULL,1,'Dr. Rafael',NULL,NULL,'ativa',NULL,NULL,NULL,NULL,'Oral',NULL,NULL),(23,5,13,'intervalo',8,'08:00:00','','2026-06-23','2026-06-26',3,NULL,NULL,NULL,'finalizada','2026-11-14 10:43:59',NULL,NULL,NULL,'Oral',15.00,'ml'),(24,5,15,'horario_fixo',NULL,NULL,'continuo','2026-10-03',NULL,1,NULL,NULL,NULL,'ativa',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(26,5,13,'horario_fixo',NULL,NULL,'continuo','2026-11-15',NULL,1,NULL,NULL,NULL,'ativa',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(27,8,24,'horario_fixo',NULL,NULL,'continuo','2026-11-22',NULL,1,NULL,NULL,NULL,'ativa',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(28,7,13,'horario_fixo',NULL,NULL,'continuo','2026-11-23','2027-01-31',2,'Dr. João','Febre........','Tomar um comprimido com água','ativa',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(29,3,18,'horario_fixo',NULL,NULL,'continuo','2026-11-16','2026-11-20',1,NULL,NULL,NULL,'finalizada','2026-11-23 23:48:11',NULL,NULL,NULL,NULL,NULL,NULL),(30,5,24,'horario_fixo',NULL,NULL,'continuo','2026-11-02',NULL,1,NULL,NULL,NULL,'suspensa','2026-11-23 23:49:44','teste',NULL,NULL,NULL,NULL,NULL),(31,3,26,'horario_fixo',NULL,NULL,'continuo','2026-11-27',NULL,1,NULL,NULL,NULL,'ativa',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `prescricoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relatos_diarios`
--

DROP TABLE IF EXISTS `relatos_diarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `relatos_diarios` (
  `idRelato` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `data_relato` date NOT NULL,
  `periodo` enum('dia','noite') NOT NULL,
  `conteudo` text NOT NULL,
  `autorLogin_idLogin` int(11) NOT NULL,
  `criado_em` datetime NOT NULL,
  `editado_em` datetime DEFAULT NULL,
  `editado_por_login` int(11) DEFAULT NULL,
  PRIMARY KEY (`idRelato`),
  KEY `idx_relatos_diarios_data_periodo_hora` (`data_relato`,`periodo`,`criado_em`),
  KEY `idx_relatos_diarios_autor` (`autorLogin_idLogin`),
  KEY `idx_relatos_diarios_editor` (`editado_por_login`),
  CONSTRAINT `fk_relatos_diarios_autor` FOREIGN KEY (`autorLogin_idLogin`) REFERENCES `login` (`idLogin`) ON UPDATE CASCADE,
  CONSTRAINT `fk_relatos_diarios_editor` FOREIGN KEY (`editado_por_login`) REFERENCES `login` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relatos_diarios`
--

LOCK TABLES `relatos_diarios` WRITE;
/*!40000 ALTER TABLE `relatos_diarios` DISABLE KEYS */;
INSERT INTO `relatos_diarios` VALUES (1,'2026-10-08','dia','testes rtestes',1,'2026-10-08 00:17:24',NULL,NULL),(2,'2026-10-08','noite','Teste sem mencionar',3,'2026-10-08 00:18:52',NULL,NULL),(3,'2026-10-08','dia','asgahsgahgahghad',1,'2026-10-08 00:22:29',NULL,NULL),(4,'2026-10-08','dia','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut lobortis mauris eget facilisis tempor. Morbi consequat hendrerit lacus, a porta eros porttitor vel. Etiam ornare pretium imperdiet. Vivamus velit ipsum, consectetur eget egestas a, lacinia quis arcu. Quisque dapibus pretium ante, eget sagittis risus pretium id. Aenean feugiat et lorem et dignissim. Aliquam erat volutpat. Etiam libero tortor, convallis id lorem non, blandit feugiat nibh.',1,'2026-10-08 00:23:28',NULL,NULL),(5,'2026-10-08','dia','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut lobortis mauris eget facilisis tempor. Morbi consequat hendrerit lacus, a porta eros porttitor vel. Etiam ornare pretium imperdiet. Vivamus velit ipsum, consectetur eget egestas a, lacinia quis arcu. Quisque dapibus pretium ante, eget sagittis risus pretium id. Aenean feugiat et lorem et dignissim. Aliquam erat volutpat. Etiam libero tortor, convallis id lorem non, blandit feugiat nibh.Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut lobortis mauris eget facilisis tempor. Morbi consequat hendrerit lacus, a porta eros porttitor vel. Etiam ornare pretium imperdiet. Vivamus velit ipsum, consectetur eget egestas a, lacinia quis arcu. Quisque dapibus pretium ante, eget sagittis risus pretium id. Aenean feugiat et lorem et dignissim. Aliquam erat volutpat. Etiam libero tortor, convallis id lorem non, blandit feugiat nibh.',3,'2026-10-08 00:24:49',NULL,NULL),(6,'2026-10-13','dia','Bom dia ,\r\n\r\nO @Jo??o da Silva FEZJAHJAHJAHJAHJAHJAHAJHAJH',1,'2026-10-13 09:32:43',NULL,NULL),(7,'2026-10-13','dia','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam dolor sem, dapibus vitae egestas in, dictum eget dolor. Curabitur mi nisl, dignissim quis enim sed, gravida tincidunt est. @Jo??o da Silva Donec vitae vulputate elit. Vestibulum at magna risus. Etiam vitae quam ut justo feugiat bibendum. Nunc nulla risus, venenatis nec accumsan sed, vestibulum et nulla. @Josefa Santos Suspendisse eget sollicitudin turpis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec leo commodo, consectetur massa vitae, vehicula mi. Nam et felis sit amet arcu ultrices auctor sed in magna.',3,'2026-10-13 09:57:59',NULL,NULL),(8,'2026-10-13','noite','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam dolor sem, dapibus vitae egestas in, dictum eget dolor. Curabitur mi nisl, dignissim quis enim sed, gravida tincidunt est. Donec vitae vulputate elit. Vestibulum at magna risus. Etiam vitae quam ut justo feugiat bibendum. Nunc nulla risus, venenatis nec accumsan sed, vestibulum et nulla. Suspendisse eget sollicitudin turpis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec leo commodo, consectetur massa vitae, vehicula mi. Nam et felis sit amet arcu ultrices auctor sed in magna.Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam dolor sem, dapibus vitae egestas in, dictum eget dolor. Curabitur mi nisl, dignissim quis enim sed, gravida tincidunt est. Donec vitae vulputate elit. Vestibulum at magna risus. Etiam vitae quam ut justo feugiat bibendum. Nunc nulla risus, venenatis nec accumsan sed, vestibulum et nulla. Suspendisse eget sollicitudin turpis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec leo commodo, consectetur massa vitae, vehicula mi. Nam et felis sit amet arcu ultrices auctor sed in magna.',3,'2026-10-13 09:58:08',NULL,NULL),(9,'2026-10-13','dia','HGHGHGHGHGHGGDSSFDSDSXDS SFSFDS FDFGSDgfdgfdgfdfg fd fdfdfgd',3,'2026-10-13 15:33:31',NULL,NULL),(10,'2026-11-09','dia','kjsdfjJHDHZJDHajdhajdshJDFHJSDHJd,MDNMDNMndmDNMndmND\r\nkdnmdnjDNMdnmNDmnm @Acolhido teste 2\r\n\r\nhghghg',1,'2026-11-09 19:01:31','2026-11-09 19:30:24',1),(11,'2026-11-09','noite','bhbhbhbhgfhghhbhbhbhbhbhbhbhbhbhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh\r\njjnjnjnjnjnj\r\njnjnjnjn',1,'2026-11-09 19:43:37',NULL,NULL),(12,'2026-11-02','dia','hghghghghghghghghg',1,'2026-11-09 20:04:02',NULL,NULL),(13,'2026-11-09','dia','@Josefa Santos : Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus accumsan eu elit non ultrices. Praesent sed tellus gravida, congue ex at, molestie augue. Vestibulum pulvinar nisi nibh, eget ultrices magna cursus vel. In scelerisque nibh eget justo commodo, condimentum placerat augue laoreet. Lorem ipsum dolor sit amet, consectetur adipiscing elit. \r\n@Jo??o da Silva Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Proin ullamcorper aliquet tincidunt. Aliquam eu mi ut ipsum consectetur auctor. Aliquam non sem sed ante bibendum iaculis vitae a mi. Duis quis tortor rutrum, elementum justo sit amet, rhoncus dolor. Nullam vitae tempus est. Maecenas bibendum euismod risus, eget commodo neque bibendum a. Sed tincidunt vestibulum dolor. Mauris congue arcu at turpis posuere porttitor. Suspendisse lacus dui, rutrum eget porta accumsan, condimentum id diam. In hac habitasse platea dictumst.',3,'2026-11-15 15:47:27',NULL,NULL),(14,'2026-11-15','dia','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus accumsan eu elit non ultrices. Praesent sed tellus gravida, congue ex at, molestie augue. Vestibulum pulvinar nisi nibh, eget ultrices magna cursus vel. In scelerisque nibh eget justo commodo, condimentum placerat augue laoreet. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Proin ullamcorper aliquet tincidunt. Aliquam eu mi ut ipsum consectetur auctor. Aliquam non sem sed ante bibendum iaculis vitae a mi. Duis quis tortor rutrum, elementum justo sit amet, rhoncus dolor. Nullam vitae tempus est. Maecenas bibendum euismod risus, eget commodo neque bibendum a. Sed tincidunt vestibulum dolor. Mauris congue arcu at turpis posuere porttitor. Suspendisse lacus dui, rutrum eget porta accumsan, condimentum id diam. In hac habitasse platea dictumst.',3,'2026-11-15 15:47:42',NULL,NULL),(15,'2026-11-15','dia','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus accumsan eu elit non ultrices. Praesent sed tellus gravida, congue ex at, molestie augue. Vestibulum pulvinar nisi nibh, eget ultrices magna cursus vel. In scelerisque nibh eget justo commodo, condimentum placerat augue laoreet. Lorem ipsum dolor sit amet, consectetur adipiscing elit. @Ana Pereira Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Proin ullamcorper aliquet tincidunt. Aliquam eu mi ut ipsum consectetur auctor. Aliquam non sem sed ante bibendum iaculis vitae a mi. Duis quis tortor rutrum, elementum justo sit amet, rhoncus dolor. Nullam vitae tempus est. Maecenas bibendum euismod risus, eget commodo neque bibendum a. Sed tincidunt vestibulum dolor. Mauris congue arcu at turpis posuere porttitor. Suspendisse lacus dui, rutrum eget porta accumsan, condimentum id diam. In hac habitasse platea dictumst.',1,'2026-11-15 16:27:45',NULL,NULL),(16,'2026-11-03','dia','@Ana Pereira : Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus accumsan eu elit non ultrices. Praesent sed tellus gravida, congue ex at, molestie augue. Vestibulum pulvinar nisi nibh, eget ultrices magna cursus vel. In scelerisque nibh eget justo commodo, condimentum placerat augue laoreet. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Proin ullamcorper aliquet tincidunt. Aliquam eu mi ut ipsum consectetur auctor. Aliquam non sem sed ante bibendum iaculis vitae a mi. Duis quis tortor rutrum, elementum justo sit amet, rhoncus dolor. Nullam vitae tempus est. Maecenas bibendum euismod risus, eget commodo neque bibendum a. Sed tincidunt vestibulum dolor. Mauris congue arcu at turpis posuere porttitor. Suspendisse lacus dui, rutrum eget porta accumsan, condimentum id diam. In hac habitasse platea dictumst.',1,'2026-11-15 16:28:10',NULL,NULL),(17,'2026-11-24','dia','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus nec enim et erat dictum scelerisque. Pellentesque molestie vehicula massa vel imperdiet. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque eu sagittis leo. Sed mi ex, tempus sit amet est vitae, vehicula sollicitudin enim. Sed quis bibendum nibh. Praesent tincidunt lorem eget mi aliquam euismod.\r\n\r\nAenean molestie rhoncus dui, ac cursus nunc. Proin quis purus nunc. Praesent commodo ultrices quam in rutrum. Pellentesque luctus quam nulla, quis congue nunc semper et. Etiam lectus mauris, egestas ac porttitor consectetur, pulvinar eget tellus. Cras malesuada, nisi at pulvinar malesuada, neque ex fermentum leo, sit amet volutpat sem justo ut enim. Proin cursus leo quis ligula volutpat mollis. Nam hendrerit ut lectus ut sodales. Aenean vel dolor tincidunt ex tristique blandit. Donec convallis iaculis tristique. Cras in ligula vulputate nulla dignissim tempus.',3,'2026-11-24 00:07:44',NULL,NULL);
/*!40000 ALTER TABLE `relatos_diarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relatos_diarios_acolhidos`
--

DROP TABLE IF EXISTS `relatos_diarios_acolhidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `relatos_diarios_acolhidos` (
  `idRelato` bigint(20) unsigned NOT NULL,
  `acolhido_id` int(11) NOT NULL,
  PRIMARY KEY (`idRelato`,`acolhido_id`),
  KEY `idx_relatos_diarios_acolhidos_acolhido` (`acolhido_id`),
  CONSTRAINT `fk_relato_mencao_acolhido` FOREIGN KEY (`acolhido_id`) REFERENCES `acolhido` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_relato_mencao_relato` FOREIGN KEY (`idRelato`) REFERENCES `relatos_diarios` (`idRelato`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relatos_diarios_acolhidos`
--

LOCK TABLES `relatos_diarios_acolhidos` WRITE;
/*!40000 ALTER TABLE `relatos_diarios_acolhidos` DISABLE KEYS */;
INSERT INTO `relatos_diarios_acolhidos` VALUES (1,7),(3,5),(4,7),(6,4),(7,4),(7,7),(10,3),(13,4),(13,7),(15,5),(16,5);
/*!40000 ALTER TABLE `relatos_diarios_acolhidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saude_acolhido_registros`
--

DROP TABLE IF EXISTS `saude_acolhido_registros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `saude_acolhido_registros` (
  `idSaude` int(11) NOT NULL AUTO_INCREMENT,
  `acolhido_id` int(11) NOT NULL,
  `tipo` varchar(40) NOT NULL,
  `data_evento` date NOT NULL,
  `titulo` varchar(180) NOT NULL,
  `profissional` varchar(180) DEFAULT NULL,
  `descricao` longtext NOT NULL,
  `detalhes_json` longtext DEFAULT NULL,
  `prescricao_id` int(11) DEFAULT NULL,
  `autor_login_id` int(11) DEFAULT NULL,
  `criado_em` datetime NOT NULL,
  PRIMARY KEY (`idSaude`),
  KEY `idx_saude_acolhido_data` (`acolhido_id`,`data_evento`,`criado_em`),
  KEY `idx_saude_tipo_data` (`tipo`,`data_evento`),
  KEY `idx_saude_prescricao` (`prescricao_id`),
  KEY `idx_saude_autor` (`autor_login_id`),
  CONSTRAINT `fk_saude_registro_acolhido` FOREIGN KEY (`acolhido_id`) REFERENCES `acolhido` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_saude_registro_autor` FOREIGN KEY (`autor_login_id`) REFERENCES `login` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saude_acolhido_registros`
--

LOCK TABLES `saude_acolhido_registros` WRITE;
/*!40000 ALTER TABLE `saude_acolhido_registros` DISABLE KEYS */;
INSERT INTO `saude_acolhido_registros` VALUES (1,3,'consulta','2026-11-11','Consulta Psiquiatrica',NULL,'Teste de descrição',NULL,NULL,1,'2026-11-11 21:16:06'),(5,8,'prescricao_alterada','2026-11-18','Prescrição atualizada: FENOBARBITAL',NULL,'Dados da prescrição alterados.','{\"alteracoes\":{\"Posologia\":{\"antes\":\"17:30 — 1 comprimido\",\"depois\":\"14:00 — 1 comprimido; 20:00 — 1 comprimido\"}},\"atual\":{\"_acolhido_id\":8,\"Acolhido\":\"Benedito Costa\",\"Medicamento\":\"FENOBARBITAL\",\"Princípio ativo\":\"FENOBARBITAL\",\"Apresentação\":\"100 MG COM\",\"Posologia\":\"14:00 — 1 comprimido; 20:00 — 1 comprimido\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"23/06/2026 sem data final\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"}}',21,1,'2026-11-18 01:46:21'),(6,6,'prescricao_alterada','2026-11-22','Prescrição atualizada: DIPIRONA',NULL,'Dados da prescrição alterados.','{\"alteracoes\":{\"Posologia\":{\"antes\":\"08:00 — 1 ml\",\"depois\":\"08:00 — 1 ml; 20:00 — 1 ml\"}},\"atual\":{\"_acolhido_id\":6,\"Acolhido\":\"Carlos Oliveira\",\"Medicamento\":\"DIPIRONA\",\"Princípio ativo\":\"DIPIRONA\",\"Apresentação\":\"500 MG/ML SOL INJ\",\"Posologia\":\"08:00 — 1 ml; 20:00 — 1 ml\",\"Via de administração\":\"Oral\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"14/06/2026 sem data final\",\"Prescritor\":\"Dr. Rafael\",\"Instruções\":\"\",\"Observações\":\"Antitermico\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"}}',18,1,'2026-11-22 13:13:16'),(7,6,'prescricao_alterada','2026-11-22','Prescrição atualizada: FENOBARBITAL',NULL,'Dados da prescrição alterados.','{\"alteracoes\":{\"Posologia\":{\"antes\":\"09:00 — 1 comprimido\",\"depois\":\"09:00 — 1 comprimido; 20:00 — 1 comprimido\"}},\"atual\":{\"_acolhido_id\":6,\"Acolhido\":\"Carlos Oliveira\",\"Medicamento\":\"FENOBARBITAL\",\"Princípio ativo\":\"FENOBARBITAL\",\"Apresentação\":\"100 MG COM\",\"Posologia\":\"09:00 — 1 comprimido; 20:00 — 1 comprimido\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"14/06/2026 sem data final\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"}}',20,1,'2026-11-22 13:14:10'),(8,8,'prescricao_criada','2026-11-22','Nova prescrição: NEULEPTIL',NULL,'Nova prescrição registrada no sistema.','{\"atual\":{\"_acolhido_id\":8,\"Acolhido\":\"Benedito Costa\",\"Medicamento\":\"NEULEPTIL\",\"Princípio ativo\":\"PERICIAZINA\",\"Apresentação\":\"10 MG COM REV\",\"Posologia\":\"11:00 — 1 comprimido\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"22/11/2026 sem data final\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"}}',27,1,'2026-11-22 14:26:22'),(9,7,'prescricao_criada','2026-11-23','Nova prescrição: DIPIRONA',NULL,'Nova prescrição registrada no sistema.','{\"atual\":{\"_acolhido_id\":7,\"Acolhido\":\"Josefa Santos\",\"Medicamento\":\"DIPIRONA\",\"Princípio ativo\":\"DIPIRONA\",\"Apresentação\":\"500 MG/ML SOL INJ\",\"Posologia\":\"08:00 — 1 ml; 20:00 — 1 ml\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"23/11/2026 a 31/01/2027\",\"Prescritor\":\"Dr. João\",\"Instruções\":\"Tomar um comprimido com água\",\"Observações\":\"Febre........\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"}}',28,3,'2026-11-23 22:10:02'),(10,7,'prescricao_alterada','2026-11-23','Prescrição atualizada: DIPIRONA',NULL,'Dados da prescrição alterados.','{\"alteracoes\":{\"Posologia\":{\"antes\":\"08:00 — 1 ml; 20:00 — 1 ml\",\"depois\":\"08:00 — 2 ml; 20:00 — 1 ml\"}},\"atual\":{\"_acolhido_id\":7,\"Acolhido\":\"Josefa Santos\",\"Medicamento\":\"DIPIRONA\",\"Princípio ativo\":\"DIPIRONA\",\"Apresentação\":\"500 MG/ML SOL INJ\",\"Posologia\":\"08:00 — 2 ml; 20:00 — 1 ml\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"23/11/2026 a 31/01/2027\",\"Prescritor\":\"Dr. João\",\"Instruções\":\"Tomar um comprimido com água\",\"Observações\":\"Febre........\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"}}',28,3,'2026-11-23 22:11:20'),(11,6,'medicamento_suspenso','2026-11-23','Prescrição suspensa: FENOBARBITAL',NULL,'Status alterado de Ativa para Suspensa. Motivo: teste','{\"antes\":{\"_acolhido_id\":6,\"Acolhido\":\"Carlos Oliveira\",\"Medicamento\":\"FENOBARBITAL\",\"Princípio ativo\":\"FENOBARBITAL\",\"Apresentação\":\"100 MG COM\",\"Posologia\":\"09:00 — 1 comprimido; 20:00 — 1 comprimido\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"14/06/2026 sem data final\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"},\"atual\":{\"_acolhido_id\":6,\"Acolhido\":\"Carlos Oliveira\",\"Medicamento\":\"FENOBARBITAL\",\"Princípio ativo\":\"FENOBARBITAL\",\"Apresentação\":\"100 MG COM\",\"Posologia\":\"09:00 — 1 comprimido; 20:00 — 1 comprimido\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"14/06/2026 sem data final\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Suspensa\",\"Motivo da suspensão\":\"teste\"}}',20,3,'2026-11-23 23:45:47'),(12,3,'prescricao_criada','2026-11-23','Nova prescrição: BROMAZEPAM',NULL,'Nova prescrição registrada no sistema.','{\"atual\":{\"_acolhido_id\":3,\"Acolhido\":\"Acolhido teste 2\",\"Medicamento\":\"BROMAZEPAM\",\"Princípio ativo\":\"BROMAZEPAM\",\"Apresentação\":\"3 MG COM\",\"Posologia\":\"08:00 — 1 comprimido\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"16/11/2026 a 20/11/2026\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"}}',29,3,'2026-11-23 23:48:11'),(13,3,'prescricao_finalizada_automaticamente','2026-11-23','Prescrição finalizada automaticamente: BROMAZEPAM',NULL,'A data final da prescrição foi atingida e o sistema encerrou o medicamento automaticamente.','{\"antes\":{\"_acolhido_id\":3,\"Acolhido\":\"Acolhido teste 2\",\"Medicamento\":\"BROMAZEPAM\",\"Princípio ativo\":\"BROMAZEPAM\",\"Apresentação\":\"3 MG COM\",\"Posologia\":\"08:00 — 1 comprimido\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"16/11/2026 a 20/11/2026\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"},\"atual\":{\"_acolhido_id\":3,\"Acolhido\":\"Acolhido teste 2\",\"Medicamento\":\"BROMAZEPAM\",\"Princípio ativo\":\"BROMAZEPAM\",\"Apresentação\":\"3 MG COM\",\"Posologia\":\"08:00 — 1 comprimido\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"16/11/2026 a 20/11/2026\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Finalizada\",\"Motivo da suspensão\":\"\"}}',29,NULL,'2026-11-23 23:48:11'),(14,5,'prescricao_criada','2026-11-23','Nova prescrição: NEULEPTIL',NULL,'Nova prescrição registrada no sistema.','{\"atual\":{\"_acolhido_id\":5,\"Acolhido\":\"Ana Pereira\",\"Medicamento\":\"NEULEPTIL\",\"Princípio ativo\":\"PERICIAZINA\",\"Apresentação\":\"10 MG COM REV\",\"Posologia\":\"08:00 — 1 comprimido\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"02/11/2026 sem data final\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"}}',30,3,'2026-11-23 23:49:33'),(15,5,'medicamento_suspenso','2026-11-23','Prescrição suspensa: NEULEPTIL',NULL,'Status alterado de Ativa para Suspensa. Motivo: teste','{\"antes\":{\"_acolhido_id\":5,\"Acolhido\":\"Ana Pereira\",\"Medicamento\":\"NEULEPTIL\",\"Princípio ativo\":\"PERICIAZINA\",\"Apresentação\":\"10 MG COM REV\",\"Posologia\":\"08:00 — 1 comprimido\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"02/11/2026 sem data final\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"},\"atual\":{\"_acolhido_id\":5,\"Acolhido\":\"Ana Pereira\",\"Medicamento\":\"NEULEPTIL\",\"Princípio ativo\":\"PERICIAZINA\",\"Apresentação\":\"10 MG COM REV\",\"Posologia\":\"08:00 — 1 comprimido\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"02/11/2026 sem data final\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Suspensa\",\"Motivo da suspensão\":\"teste\"}}',30,3,'2026-11-23 23:49:44'),(16,5,'prescricao_alterada','2026-11-24','Prescrição atualizada: DIPIRONA',NULL,'Dados da prescrição alterados.','{\"alteracoes\":{\"Posologia\":{\"antes\":\"20:00 — 15 gotas\",\"depois\":\"20:00 — 1.5 ml\"}},\"atual\":{\"_acolhido_id\":5,\"Acolhido\":\"Ana Pereira\",\"Medicamento\":\"DIPIRONA\",\"Princípio ativo\":\"DIPIRONA\",\"Apresentação\":\"500 MG/ML SOL INJ\",\"Posologia\":\"20:00 — 1.5 ml\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"15/11/2026 sem data final\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"}}',26,3,'2026-11-24 10:19:58'),(17,5,'prescricao_alterada','2026-11-24','Prescrição atualizada: DIPIRONA',NULL,'Dados da prescrição alterados.','{\"alteracoes\":{\"Posologia\":{\"antes\":\"20:00 — 1.5 ml\",\"depois\":\"20:00 — 2 ml\"}},\"atual\":{\"_acolhido_id\":5,\"Acolhido\":\"Ana Pereira\",\"Medicamento\":\"DIPIRONA\",\"Princípio ativo\":\"DIPIRONA\",\"Apresentação\":\"500 MG/ML SOL INJ\",\"Posologia\":\"20:00 — 2 ml\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"15/11/2026 sem data final\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"}}',26,3,'2026-11-24 10:21:04'),(18,5,'prescricao_alterada','2026-11-24','Prescrição atualizada: FENOBARBITAL',NULL,'Dados da prescrição alterados.','{\"alteracoes\":{\"Posologia\":{\"antes\":\"09:00 — 2 comprimidos\",\"depois\":\"09:00 — 1.5 comprimidos\"}},\"atual\":{\"_acolhido_id\":5,\"Acolhido\":\"Ana Pereira\",\"Medicamento\":\"FENOBARBITAL\",\"Princípio ativo\":\"FENOBARBITAL\",\"Apresentação\":\"100 MG COM\",\"Posologia\":\"09:00 — 1.5 comprimidos\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"03/10/2026 sem data final\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"}}',24,3,'2026-11-24 10:22:37'),(19,5,'prescricao_alterada','2026-11-24','Prescrição atualizada: DIPIRONA',NULL,'Dados da prescrição alterados.','{\"alteracoes\":{\"Posologia\":{\"antes\":\"20:00 — 2 ml\",\"depois\":\"20:00 — 1 gota\"}},\"atual\":{\"_acolhido_id\":5,\"Acolhido\":\"Ana Pereira\",\"Medicamento\":\"DIPIRONA\",\"Princípio ativo\":\"DIPIRONA\",\"Apresentação\":\"500 MG/ML SOL INJ\",\"Posologia\":\"20:00 — 1 gota\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"15/11/2026 sem data final\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"}}',26,3,'2026-11-24 15:57:17'),(20,3,'prescricao_criada','2026-11-27','Nova prescrição: LACRIFILM',NULL,'Nova prescrição registrada no sistema.','{\"atual\":{\"_acolhido_id\":3,\"Acolhido\":\"Acolhido teste 2\",\"Medicamento\":\"LACRIFILM\",\"Princípio ativo\":\"CARMELOSE SÓDICA\",\"Apresentação\":\"5 MG/ML SOL OFT\",\"Posologia\":\"20:00 — 1 gota\",\"Via de administração\":\"\",\"Tipo de uso\":\"Contínuo\",\"Vigência\":\"27/11/2026 sem data final\",\"Prescritor\":\"\",\"Instruções\":\"\",\"Observações\":\"\",\"Status atual\":\"Ativa\",\"Motivo da suspensão\":\"\"}}',31,3,'2026-11-27 19:13:45');
/*!40000 ALTER TABLE `saude_acolhido_registros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `idUsuario` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `cpf` varchar(255) NOT NULL,
  `dataNascimento` date NOT NULL,
  `email` varchar(45) DEFAULT NULL,
  `registroProfissional` varchar(10) DEFAULT NULL,
  `telefone` varchar(11) DEFAULT NULL,
  `atuacaoProfissional` varchar(120) DEFAULT NULL,
  `Endereco_idEndereco` int(11) NOT NULL,
  `Login_idLogin` int(11) NOT NULL,
  `Permisoes_idPermisoes` int(11) NOT NULL,
  `estadoCivil` varchar(45) DEFAULT NULL,
  `cargoConselho` varchar(45) DEFAULT NULL,
  `inicioMandatoConselho` date DEFAULT NULL,
  `fimMandatoConselho` date DEFAULT NULL,
  `dependentes` int(11) DEFAULT NULL,
  `comorbidades` varchar(200) DEFAULT NULL,
  `CBO` varchar(10) DEFAULT NULL,
  `matricula` varchar(12) NOT NULL,
  `IsFuncionario` tinyint(4) NOT NULL,
  PRIMARY KEY (`idUsuario`),
  KEY `fk_UsuarioTemporario_Endereco10` (`Endereco_idEndereco`),
  KEY `fk_UsuarioTemporario_Login10` (`Login_idLogin`),
  KEY `fk_UsuarioTemporario_Permisoes10` (`Permisoes_idPermisoes`),
  CONSTRAINT `fk_UsuarioTemporario_Endereco10` FOREIGN KEY (`Endereco_idEndereco`) REFERENCES `endereco` (`idEndereco`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_UsuarioTemporario_Login10` FOREIGN KEY (`Login_idLogin`) REFERENCES `login` (`idLogin`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_UsuarioTemporario_Permisoes10` FOREIGN KEY (`Permisoes_idPermisoes`) REFERENCES `permissao` (`idPermisoes`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'Natan de Paula Batista','SEthd1FUa1RvaFh2NEZuYlBLNnlaZz09Ojo0sBdoRJIJ7/ZqBmHCsTza','1999-10-07','natan@gmail.com',NULL,'28999000000','Coordenador',22,3,1,'Solteiro','Presidente','2025-07-09','2029-07-09',0,NULL,NULL,'F20261008838',1),(2,'Teste Desabitado','aDJmVlJoT2c1QWIzY0o1ZlJJbkRtQT09OjreAoJg0FaMBiCcCdb/5zW7','1999-01-01',NULL,NULL,NULL,NULL,23,4,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'F20261007308',1),(3,'Fisio Fábio','RSt6WG9NQTRHSHhPWUR2V29VVW4rQT09OjoMrwXO8agAURn0senE8JJ0','1999-01-01','fabiofisio@gmail.com','12121',NULL,'Fisioterapeuta',24,5,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'F20261013918',1),(4,'Cuidador Teste','MUdRcjFGNUtzZDNYWkJSbThKZ1lMUT09OjpMYwL4yOk0F2/50F0dGgor','1980-01-01',NULL,NULL,NULL,NULL,29,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'T20261014303',0);
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-27 22:24:41
