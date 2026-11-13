CREATE TABLE users (
 user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(80) NOT NULL, email VARCHAR(120) NOT NULL UNIQUE,
 phone VARCHAR(40) NOT NULL, password_hash VARCHAR(255) NOT NULL,
 role ENUM('user','admin') NOT NULL DEFAULT 'user',
 status ENUM('active','blocked') NOT NULL DEFAULT 'active',
 login_failures INT NOT NULL DEFAULT 0, locked_until DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE bookings ADD user_id INT UNSIGNED NULL, ADD CONSTRAINT fk_booking_user FOREIGN KEY(user_id) REFERENCES users(user_id);
ALTER TABLE promo_codes ADD user_id INT UNSIGNED NULL, ADD used_at DATETIME NULL,
 ADD CONSTRAINT fk_promo_user FOREIGN KEY(user_id) REFERENCES users(user_id);
UPDATE promo_codes SET status='inactive' WHERE user_id IS NULL;
CREATE TABLE site_settings (setting_key VARCHAR(50) PRIMARY KEY, setting_value TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO site_settings VALUES ('title','Your next great night starts here.'),('intro','Discover live music and comedy. Choose your moment, pick your seat.'),('contact','support@eventseat.example'),('promo_percent','20');
