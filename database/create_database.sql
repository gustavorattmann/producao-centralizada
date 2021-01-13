USE producao_centralizada;

DROP TABLE IF EXISTS product_has_raw_material;
DROP TABLE IF EXISTS raw_material;
DROP TABLE IF EXISTS product;
DROP TABLE IF EXISTS users;

CREATE TABLE IF NOT EXISTS users
(
	id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    level TINYINT DEFAULT 2 NOT NULL, /* 0 - admin, 1 - manager, 2 - employee */
    situation TINYINT DEFAULT 1 NOT NULL, /* 0 - inactive, 1 - active */
    
    CONSTRAINT pk_users PRIMARY KEY (id),
    CONSTRAINT un_email UNIQUE (email),
    CONSTRAINT un_password UNIQUE (password)
);

CREATE TABLE IF NOT EXISTS product
(
	id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    category TINYINT NOT NULL, /* 0 - epi, 1 - eletronic */
    
    CONSTRAINT pk_product PRIMARY KEY (id),
    CONSTRAINT un_product_name UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS raw_material
(
	id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    stock INTEGER NOT NULL,
    
    CONSTRAINT pk_raw_material PRIMARY KEY (id),
	CONSTRAINT un_raw_material_name UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS product_has_raw_material
(
	id INTEGER NOT NULL AUTO_INCREMENT,
    product INTEGER NOT NULL,
    raw_material INTEGER NOT NULL,
    quantity_product_requested INTEGER CHECK (quantity_product_requested > 0) NOT NULL,
    quantity_product_produced INTEGER CHECK (quantity_product_produced > 0) NOT NULL,
    quantity_product_losted INTEGER CHECK (quantity_product_losted > 0) NOT NULL,
    quantity_raw_material_limit INTEGER CHECK (quantity_raw_material_limit > 0) NOT NULL,
    quantity_raw_material_used INTEGER CHECK (quantity_raw_material_used > 0) NOT NULL,
    quantity_raw_material_losted INTEGER CHECK (quantity_raw_material_losted > 0) NOT NULL,
    justification TEXT NULL,
    date TIMESTAMP NOT NULL,
    
    CONSTRAINT pk_product_has_raw_material PRIMARY KEY (id),
    CONSTRAINT fk_product FOREIGN KEY (product) REFERENCES product (id),
    CONSTRAINT fk_raw_material FOREIGN KEY (raw_material) REFERENCES raw_material (id)
);