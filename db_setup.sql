-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    price_dzd REAL NOT NULL,
    tailles TEXT NOT NULL,
    main_image TEXT NOT NULL
);

-- Product images table
CREATE TABLE IF NOT EXISTS product_images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    image_path TEXT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_name TEXT NOT NULL,
    phone TEXT NOT NULL,
    address TEXT NOT NULL,
    product_id INTEGER NOT NULL,
    taille TEXT NOT NULL,
    color TEXT NOT NULL,
    delivery_choice TEXT NOT NULL CHECK(delivery_choice IN ('domicile', 'bureau_noest')),
    wilaya TEXT NOT NULL,
    status TEXT DEFAULT 'pending' CHECK(status IN ('pending', 'confirmed')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Admin table
CREATE TABLE IF NOT EXISTS admin (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL
);

-- Insert default admin (username: shada, password: sofianhamza25)
INSERT OR IGNORE INTO admin (username, password_hash) VALUES (
    'shada',
    '$2y$10$' || hex(randomblob(22)) || '$' || hex(randomblob(32))
);

-- Insert sample product
INSERT OR IGNORE INTO products (id, name, description, price_dzd, tailles, main_image) VALUES 
(1, 'Élaya', 'Hijab Élaya léger, doux et respirant — parfait pour l''été. Confort garanti, style assuré.', 2500, 'Standard', '685ebcb456e7a_1000005347.jpg');

-- Insert sample product images
INSERT OR IGNORE INTO product_images (product_id, image_path) VALUES 
(1, '685ebcb457379_1000005309.jpg'),
(1, '685ebcb457674_1000005361.jpg');
