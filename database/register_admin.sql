USE producao_centralizada;

-- Cadastro de cargos

INSERT INTO roles
	(name)
VALUES
	('Administrador');

-- Cadastro de administrador

INSERT INTO users
	(name, email, password, level, situation)
VALUES
	('Administrador', 'admin@producaocentralizada.com.br', '$2y$10$UFo1Uk9sZSt6bTFwN1I1Ueh3NkIBSdMw53aczNsDUkcNC88CMxs3a', 1, 1);