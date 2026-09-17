SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS churrascos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    data_churrasco DATE NOT NULL,
    quantidade_adultos INT UNSIGNED NOT NULL DEFAULT 0,
    quantidade_criancas INT UNSIGNED NOT NULL DEFAULT 0,
    duracao ENUM('2 horas', '4 horas', '6 horas ou mais') NOT NULL,
    tipo ENUM('Econômico', 'Tradicional', 'ChurrasTop') NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_churrasco_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS participantes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    telefone VARCHAR(30) NOT NULL,
    tipo ENUM('adulto', 'crianca') NOT NULL DEFAULT 'adulto'
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS churrasco_participante (
    churrasco_id INT UNSIGNED NOT NULL,
    participante_id INT UNSIGNED NOT NULL,
    valor_contribuicao DECIMAL(10,2) NOT NULL DEFAULT 0,
    status_pagamento ENUM('pago', 'pendente') NOT NULL DEFAULT 'pendente',
    PRIMARY KEY (churrasco_id, participante_id),
    FOREIGN KEY (churrasco_id) REFERENCES churrascos(id) ON DELETE CASCADE,
    FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS produtos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    categoria VARCHAR(60) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    unidade_medida VARCHAR(30) NOT NULL,
    quantidade_por_pessoa DECIMAL(10,3) NOT NULL DEFAULT 0,
    limite_repeticao INT UNSIGNED NOT NULL DEFAULT 2,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    CHECK (preco >= 0),
    CHECK (quantidade_por_pessoa >= 0)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS churrasco_produto (
    churrasco_id INT UNSIGNED NOT NULL,
    produto_id INT UNSIGNED NOT NULL,
    quantidade DECIMAL(10,3) NOT NULL DEFAULT 0,
    PRIMARY KEY (churrasco_id, produto_id),
    FOREIGN KEY (churrasco_id) REFERENCES churrascos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    CHECK (quantidade >= 0)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS solicitacoes_participacao (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    churrasco_id INT UNSIGNED NOT NULL,
    participante_id INT UNSIGNED NOT NULL,
    produto_id INT UNSIGNED NOT NULL,
    status ENUM('pendente', 'aprovada', 'recusada') NOT NULL DEFAULT 'pendente',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_solicitacao (churrasco_id, participante_id),
    FOREIGN KEY (churrasco_id) REFERENCES churrascos(id) ON DELETE CASCADE,
    FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO produtos (nome, categoria, preco, unidade_medida, quantidade_por_pessoa, limite_repeticao) VALUES
('Carne bovina', 'Carne bovina', 45.00, 'kg', 0.35, 2),
('Linguiça', 'Linguiça', 22.00, 'kg', 0.15, 2),
('Frango', 'Frango', 18.00, 'kg', 0.10, 2),
('Pão de alho', 'Pão de alho', 12.00, 'pacote', 0.50, 3),
('Farofa', 'Acompanhamento', 10.00, 'pacote', 0.10, 3),
('Refrigerante', 'Refrigerante', 9.00, 'litro', 0.60, 3),
('Cerveja', 'Cerveja', 5.50, 'litro', 1.00, 4),
('Água', 'Água', 3.00, 'litro', 0.50, 4),
('Gelo', 'Gelo', 10.00, 'kg', 0.50, 3);
