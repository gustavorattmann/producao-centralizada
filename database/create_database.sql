USE producao_centralizada;

DROP TABLE IF EXISTS product_has_raw_material;
DROP TABLE IF EXISTS raw_materials;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS category;
DROP TABLE IF EXISTS users;

CREATE TABLE IF NOT EXISTS users
(
	id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    level TINYINT DEFAULT 2 NOT NULL, /* 0 - admin, 1 - manager, 2 - human resources, 3 - warehouse, 4 - employee */
    situation TINYINT DEFAULT 1 NOT NULL, /* 0 - inactive, 1 - active */
    
    CONSTRAINT pk_users PRIMARY KEY (id),
    CONSTRAINT un_email UNIQUE (email)
);

CREATE TABLE IF NOT EXISTS category
(
	id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    
    CONSTRAINT pk_category PRIMARY KEY (id),
    CONSTRAINT un_category_name UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS products
(
	id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    category INTEGER NOT NULL,
    
    CONSTRAINT pk_products PRIMARY KEY (id),
    CONSTRAINT un_products_name UNIQUE (name),
    CONSTRAINT fk_category FOREIGN KEY (category) REFERENCES category (id)
);

CREATE TABLE IF NOT EXISTS raw_materials
(
	id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    stock INTEGER NOT NULL,
    
    CONSTRAINT pk_raw_materials PRIMARY KEY (id),
	CONSTRAINT un_raw_materials_name UNIQUE (name)
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
    CONSTRAINT fk_products FOREIGN KEY (product) REFERENCES products (id),
    CONSTRAINT fk_raw_materials FOREIGN KEY (raw_material) REFERENCES raw_materials (id)
);