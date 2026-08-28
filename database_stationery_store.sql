CREATE DATABASE IF NOT EXISTS stationery_store;

-- Use the database
USE stationery_store;

-- Categories Table
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    product_name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    image_url VARCHAR(255),
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- Users Table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    address TEXT,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart Table
CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Orders Table
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2),
    status VARCHAR(50) DEFAULT 'Pending',
    shipping_address TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Order Items Table
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT,
    price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

INSERT INTO categories (category_name) VALUES 
('Pens & Writing'),
('Notebooks & Journals'),
('Art Supplies'),
('Office Supplies'),
('Desk Accessories');

INSERT INTO products (product_name, description, price, stock_quantity, category_id, image_url) VALUES

-- Pens & Writing (Category 1)
('Ball Pen 10-Pack', 'Smooth writing ball pens, perfect for daily use. Pack of 10 assorted colors.', 8.90, 100, 1, 'https://m.media-amazon.com/images/I/61Fh2N16caL._AC_UF894,1000_QL80_.jpg'),
('Fountain Pen Set', 'Elegant fountain pen set with 5 ink cartridges. Ideal for calligraphy and formal writing.', 55.00, 25, 1, 'https://cdn11.bigcommerce.com/s-caae1tt33v/images/stencil/1280x1280/products/5084/11085/PilotMetropolitan-Fine-lineup-web__73331.1733523514.jpg?c=1'),
('Highlighter Set', 'Bright fluorescent highlighter pens in 6 colors. Perfect for studying and marking important notes.', 17.50, 60, 1, 'https://i5.walmartimages.com/seo/ZEYAR-Highlighter-Pastel-Colors-Chisel-Tip-Marker-Pen-Assorted-Colors-Water-Based-Quick-Dry-6-Macaron-Colors_046eb321-14a8-45ac-b325-bdf8a2b48576.af349b71f77e4e8efc7cb1ef6a4899f2.jpeg'),
('Mechanical Pencil Set', 'Set of 3 mechanical pencils with 0.5mm lead.', 9.90, 45, 1, 'https://www.e-unison.com.my/image/eunison/image/cache/data/all_product_images/product-558/zFSNQB4c1592372084-1208x1485.jpg'),
('Permanent Markers', 'Set of 8 permanent markers in assorted colors. Long-lasting and waterproof.', 34.90, 35, 1, 'https://www.youlin.com.my/media/catalog/product/cache/9b17e1922cf06b5d4a424866db79a333/s/h/sharpie-510x600_1_1.jpg'),

-- Notebooks & Journals (Category 2)
('Spiral Notebook A4', 'A4 size spiral notebook with 200 sheets of quality paper. Ideal for note-taking and assignments.', 12.50, 80, 2, 'https://www.bookxcess.com/cdn/shop/products/200-page-a4-spiral-notebook-14-0037-5015934498873-28645093933234.jpg?v=1648794931'),
('Journal Book', 'Compact journal with leather cover. 120 pages of high-quality acid-free paper.', 22.00, 15, 2, 'https://thepapery.co.za/cdn/shop/files/premium-journals-grflexi_1200x1200.jpg?v=1729235021'),
('Sticky Notes Set', 'Sticky notes in various sizes and colors. Perfect for reminders and quick notes.', 8.90, 120, 2, 'https://media.rs-online.com/image/upload/b_rgb:FFFFFF,c_pad,dpr_2.625,f_auto,h_214,q_auto,w_380/c_pad,h_214,w_380/F1243408-01?pgw=1'),
('Sketchbook A5', 'A5 sketchbook with thick 200gsm paper. Suitable for pencil, ink, and light watercolor.', 28.50, 30, 2, 'https://m.media-amazon.com/images/I/71xa78NhI-L._AC_UF894,1000_QL80_.jpg'),

-- Art Supplies (Category 3)
('Watercolor Brush Set', 'Professional watercolor brush set with 6 round brushes in various sizes.', 35.00, 20, 3, 'https://img.lazcdn.com/g/p/676b689f52820195013ef04d4161e79e.jpg_720x720q80.jpg'),
('Colored Pencil Set', 'Set of 24 premium colored pencils with vibrant pigments. Perfect for artists and students.', 42.00, 25, 3, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSRSVwJZ206FrxIv72_2iDKDxnNWRYVwu1RralOYBrOE_fHDZtajpre6c8&s=10'),
('Oil Pastel Set', 'Set of 24 oil pastels with rich, blendable colors. Ideal for artwork and illustrations.', 49.00, 18, 3, 'https://pentel.com.my/wp-content/uploads/2019/05/PHN-50-INSIDE.png'),
('Acrylic Paint Set', 'Acrylic paint set with 12 vibrant colors. Includes 3 brushes and a palette.', 38.50, 15, 3, 'https://cdn.store-assets.com/s/1328220/i/63256114.jpg?width=1024'),

-- Office Supplies (Category 4)
('Desk Tape Dispenser', 'Heavy-duty tape dispenser with non-slip base. Holds standard 1-inch tape rolls.', 16.50, 40, 4, 'https://unicornstationery.com/wp-content/uploads/2023/11/9557368041875-600x600.jpg'),
('Paper Trimmer', 'A4 paper trimmer with durable cutting blade. Can cut up to 10 sheets at once.', 65.00, 12, 4, 'https://www.youlin.com.my/media/catalog/product/cache/9b17e1922cf06b5d4a424866db79a333/i/m/image_11_.png'),
('Stapler Set', 'Professional stapler with 5000 staples. Includes staple remover.', 25.00, 30, 4, 'https://www.e-unison.com.my/image/eunison/image/data/all_product_images/product-951/wf6Tve6S1768465083.jpg'),
('Binder Clips Set', 'Assorted binder clips includes 12 clips for document organization.', 10.50, 80, 4, 'https://www.youlin.com.my/media/catalog/product/cache/03388b4016e9f594b8c7416215e10043/_/c/_clip-astar-dc19mm.jpg'),
('Document Folder', 'A4 document folder with clear cover and 20 pockets. Professional presentation folder.', 15.00, 50, 4, 'https://expressprint.com.my/wp-content/uploads/Document-Folder-1.webp'),

-- Desk Accessories (Category 5)
('Laptop Stand', 'Adjustable laptop stand with anti-slip pads. Ergonomic design for better posture.', 39.00, 15, 5, 'https://ergoworks.com.my/cdn/shop/files/CloudStation1.jpg?v=1760068064&width=1080'),
('Wireless Mouse', 'Ergonomic wireless mouse with silent click technology. Includes USB receiver.', 45.00, 25, 5, 'https://www.dasher.com.my/cdn/shop/products/Miiiw-Mouse-M15C-Shopify-Thumbnail.jpg?v=1674120807&width=1214'),
('Desk Lamp', 'LED desk lamp with adjustable brightness and color temperature. USB powered.', 55.00, 20, 5, 'https://www.ikea.com/my/en/images/products/roedflik-desk-lamp-grey-green__1327045_pe944344_s5.jpg?f=s'),
('Cable Organizer Set', 'Cable management set with clips, ties, and cable sleeves. Keep your workspace tidy.', 7.90, 60, 5, 'https://m.media-amazon.com/images/I/81jDakdFrvL.jpg'),
('Whiteboard Set', 'Desktop whiteboard with markers and eraser. Perfect for quick notes and reminders.', 32.00, 25, 5, 'https://kiang.com.my/wp-content/uploads/2023/08/KIDARIO-MAGNETIC-WHITEBOARD-SET-A4-20X30CM-3.jpg');

-- ==========================================
-- Online Stationery Store
-- ==========================================

-- 1. USERS TABLE
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    address TEXT,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- 2. CART TABLE
CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(user_id),

    FOREIGN KEY (product_id)
        REFERENCES products(product_id)
);


-- 3. ORDERS TABLE
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2),
    status VARCHAR(50) DEFAULT 'Pending',
    shipping_address TEXT,

    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
);


-- 4. ORDER ITEMS TABLE
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (order_id)
        REFERENCES orders(order_id),

    FOREIGN KEY (product_id)
        REFERENCES products(product_id)
);

-- ==================================================
-- CREATE CONTACT MESSAGES TABLE
-- ==================================================

CREATE TABLE IF NOT EXISTS contact_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    admin_notes TEXT NULL
);

-- ==================================================
-- OPTIONAL: ADD INDEXES FOR BETTER PERFORMANCE
-- ==================================================

ALTER TABLE contact_messages ADD INDEX idx_email (email);
ALTER TABLE contact_messages ADD INDEX idx_status (status);
ALTER TABLE contact_messages ADD INDEX idx_submitted_at (submitted_at);

-- ==================================================
-- SAMPLE INSERT DATA (OPTIONAL)
-- ==================================================

INSERT INTO contact_messages (name, email, message, status) VALUES
('John Doe', 'john@example.com', 'I love your stationery products! Do you have any discounts for students?', 'read'),
('Jane Smith', 'jane@example.com', 'When will the new notebooks be in stock?', 'unread'),
('Ahmad Bin Abdullah', 'ahmad@example.com', 'I want to return an item. What is the return policy?', 'replied'),
('Siti Nurhaliza', 'siti@example.com', 'Your website is very user-friendly. Keep up the good work!', 'unread'),
('Tan Wei Ming', 'tan@example.com', 'Do you offer international shipping to Singapore?', 'read');