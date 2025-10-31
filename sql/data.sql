
USE dragonstone;

-- Categories
INSERT INTO categories (slug,title) VALUES
('cleaning-household','Cleaning & Household Supplies'),
('kitchen-dining','Kitchen & Dining'),
('home-living','Home Décor & Living'),
('bathroom-care','Bathroom & Personal Care'),
('lifestyle-wellness','Lifestyle & Wellness'),
('kids-pets','Kids & Pets'),
('outdoor-garden','Outdoor & Garden'),
('clothing','Clothing');

-- Users
INSERT INTO users (name,email,password,role,eco_points) VALUES
('Alice Founder','admin@dragonstone.local', '$2y$10$Q1pYg1yqZp1I2b9aYjGx9u1KqYbqF9xYx9vY1nZz9aYlQ1', 'admin', 1200),
('Boris Manager','manager@dragonstone.local', '$2y$10$Q1pYg1yqZp1I2b9aYjGx9u1KqYbqF9xYx9vY1nZz9aYlQ1', 'manager', 500),
('Carmen Analyst','analyst@dragonstone.local', '$2y$10$Q1pYg1yqZp1I2b9aYjGx9u1KqYbqF9xYx9vY1nZz9aYlQ1', 'analyst', 300),
('Elija Customer','elija@example.com', '$2y$10$Q1pYg1yqZp1I2b9aYjGx9u1KqYbqF9xYx9vY1nZz9aYlQ1', 'customer', 150);

-- Products (sample)
INSERT INTO products (sku,title,category_id,price,stock,short_desc,long_desc,image,carbon_material,carbon_manufacture,carbon_packaging,carbon_shipping_per_km,subscription_available)
VALUES
('DS-CLEAN-001','Compostable Cleaning Pods (Multi-surface)',1,79.99,120,'Eco cleaning pods','Long description here','assets/img/pod.jpg',0.8,0.4,0.05,0.002,1),
('DS-BAMB-UTL','Bamboo Kitchen Utensil Set',2,299.00,40,'Bamboo utensils','Long description here','assets/img/bamboo.jpg',2.1,1.2,0.15,0.005,0),
('DS-WOOL-DRY','Wool Dryer Balls (Set of 3)',1,149.00,80,'Reusable wool dryer balls','Long description here','assets/img/wool.jpg',0.5,0.2,0.03,0.002,1),
('DS-REGLASS-001','Recycled Glass Storage Jar (1L)',2,129.50,200,'Recycled glass jar','Long description here','assets/img/jar.jpg',1.8,0.5,0.1,0.004,0),
('DS-SOY-CNDL','Soy Wax Candle (Lavender)',3,199.00,150,'Soy wax candle','Long description here','assets/img/candle.jpg',1.0,0.3,0.05,0.003,0),
('DS-CLOTH-A1','Eco Clothing Item A1',8,199.99,50,'Sustainable clothing A1','Description for A1','assets/img/A1.jpg',1.5,0.8,0.1,0.003,0),
('DS-CLOTH-A2','Eco Clothing Item A2',8,249.99,40,'Sustainable clothing A2','Description for A2','assets/img/A2.jpg',1.6,0.9,0.12,0.003,0),
('DS-CLOTH-A3','Eco Clothing Item A3',8,299.99,30,'Sustainable clothing A3','Description for A3','assets/img/A3.jpg',1.7,1.0,0.14,0.003,0),
('DS-CLOTH-A4','Eco Clothing Item A4',8,349.99,20,'Sustainable clothing A4','Description for A4','assets/img/A4.jpg',1.8,1.1,0.16,0.003,0),
('DS-CLOTH-A5','Eco Clothing Item A5',8,399.99,10,'Sustainable clothing A5','Description for A5','assets/img/A5.jpg',1.9,1.2,0.18,0.003,0);

-- Orders & items (sample)
INSERT INTO orders (user_id,subtotal,shipping,tax,total,status) VALUES
(4,79.99,20.00,5.00,104.99,'paid'),
(4,149.00,20.00,6.00,175.00,'paid');

INSERT INTO order_items (order_id,product_id,qty,price,carbon_kg) VALUES
(1,1,1,79.99,1.25),
(2,3,1,149.00,0.8);

-- Subscriptions sample
INSERT INTO subscriptions (user_id,product_id,interval_months,next_charge,active) VALUES
(4,1,1, DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY),1),
(4,3,3, DATE_ADD(CURRENT_DATE, INTERVAL 90 DAY),1);

-- Posts
INSERT INTO posts (user_id,title,body,upvotes) VALUES
(4,'How to make compost at home','I started a worm farm and it has reduced my waste by half...',12),
(2,'Best eco paint tips','Use low-VOC primers and test colors in daylight.',5);
