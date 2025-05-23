CREATE DATABASE IF NOT EXISTS lottery_db;
USE lottery_db;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lotteries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  photo VARCHAR(255),
  winning_price DECIMAL(15,2) NOT NULL,
  money_to_raise DECIMAL(15,2) NOT NULL,
  total_raised DECIMAL(15,2) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lottery_id INT NOT NULL,
  ticket_number VARCHAR(50) NOT NULL,
  user_id INT NOT NULL,
  purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  price DECIMAL(10,2) NOT NULL,
  is_winner TINYINT(1) DEFAULT 0,
  FOREIGN KEY (lottery_id) REFERENCES lotteries(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE lottery_winners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lottery_id INT NOT NULL,
  ticket_id INT NOT NULL,
  winner_user_id INT NOT NULL,
  winning_amount DECIMAL(15,2) NOT NULL,
  drawn_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (lottery_id) REFERENCES lotteries(id) ON DELETE CASCADE,
  FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
  FOREIGN KEY (winner_user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lottery_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    expiry_date DATE NOT NULL,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (lottery_id) REFERENCES lotteries(id)
);
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    payment_status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    lottery_id INT NOT NULL,
    title VARCHAR(255),
    price DECIMAL(10,2),
    quantity INT,
    total DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(id)
);
CREATE TABLE referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    admin_comment TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);


