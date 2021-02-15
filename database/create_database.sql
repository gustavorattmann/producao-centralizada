USE producao_centralizada;

DROP TABLE IF EXISTS production;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS status_orders;
DROP TABLE IF EXISTS raw_materials;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS category;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

CREATE TABLE IF NOT EXISTS roles
(
	id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    
    CONSTRAINT pk_roles PRIMARY KEY (id),
    CONSTRAINT un_roles_name UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS users
(
	id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    level INTEGER NULL,
    situation TINYINT DEFAULT 1 NOT NULL, /* 0 - inactive, 1 - active */
    
    CONSTRAINT pk_users PRIMARY KEY (id),
    CONSTRAINT un_email UNIQUE (email),
    CONSTRAINT fk_roles FOREIGN KEY (level) REFERENCES roles (id)
);

CREATE TABLE IF NOT EXISTS category
(
	id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    situation TINYINT DEFAULT 1 NOT NULL, /* 0 - inactive, 1 - active */
    
    CONSTRAINT pk_category PRIMARY KEY (id),
    CONSTRAINT un_category_name UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS products
(
	id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    category INTEGER NULL,
    situation TINYINT DEFAULT 1 NOT NULL, /* 0 - inactive, 1 - active */
    
    CONSTRAINT pk_products PRIMARY KEY (id),
    CONSTRAINT un_products_name UNIQUE (name),
    CONSTRAINT fk_category FOREIGN KEY (category) REFERENCES category (id)
);

CREATE TABLE IF NOT EXISTS raw_materials
(
	id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    stock INTEGER NOT NULL,
    situation TINYINT DEFAULT 1 NOT NULL, /* 0 - inactive, 1 - active */
    
    CONSTRAINT pk_raw_materials PRIMARY KEY (id),
	CONSTRAINT un_raw_materials_name UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS status_orders
(
	id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    situation TINYINT DEFAULT 1 NOT NULL, /* 0 - inactive, 1 - active */
    
    CONSTRAINT pk_status_orders PRIMARY KEY (id),
    CONSTRAINT un_status_orders_name UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS orders
(
    id INTEGER NOT NULL AUTO_INCREMENT,
    solicitor INTEGER NOT NULL,
    designated INTEGER NOT NULL,
    product INTEGER NOT NULL,
    raw_material INTEGER NOT NULL,
    quantity_product_requested INTEGER CHECK (quantity_product_requested > 0) NOT NULL,
    quantity_raw_material_limit INTEGER CHECK (quantity_raw_material_limit > 0) NOT NULL,
    status_order INTEGER NULL,
    situation TINYINT DEFAULT 1 NOT NULL, /* 0 - inactive, 1 - active */
    date_initial TIMESTAMP NOT NULL,
    date_final TIMESTAMP NULL,

    CONSTRAINT pk_orders PRIMARY KEY (id),
    CONSTRAINT fk_solicitor_orders FOREIGN KEY (solicitor) REFERENCES users (id),
    CONSTRAINT fk_designated_orders FOREIGN KEY (designated) REFERENCES users (id),
    CONSTRAINT fk_products FOREIGN KEY (product) REFERENCES products (id),
    CONSTRAINT fk_raw_materials FOREIGN KEY (raw_material) REFERENCES raw_materials (id),
    CONSTRAINT fk_status_orders FOREIGN KEY (status_order) REFERENCES status_orders (id)
);

CREATE TABLE IF NOT EXISTS production
(
	id INTEGER NOT NULL AUTO_INCREMENT,
    ordered INTEGER NOT NULL,
    quantity_product_produced INTEGER CHECK (quantity_product_produced > 0) NOT NULL,
    quantity_product_losted INTEGER CHECK (quantity_product_losted > 0) NOT NULL,
    quantity_raw_material_used INTEGER CHECK (quantity_raw_material_used > 0) NOT NULL,
    quantity_raw_material_losted INTEGER CHECK (quantity_raw_material_losted > 0) NOT NULL,
    justification TEXT NULL,
    situation TINYINT DEFAULT 1 NOT NULL, /* 0 - inactive, 1 - active */
    date_initial TIMESTAMP NOT NULL,
    date_final TIMESTAMP NULL,
    
    CONSTRAINT pk_production PRIMARY KEY (id),
    CONSTRAINT fk_orders FOREIGN KEY (ordered) REFERENCES orders (id)
);