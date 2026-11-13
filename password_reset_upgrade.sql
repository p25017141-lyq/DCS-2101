-- Back up eventseat. Import ONCE into your upgraded database.
ALTER TABLE users ADD auth_version INT UNSIGNED NOT NULL DEFAULT 0;
CREATE TABLE password_resets (
 reset_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 token_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
 expires_at DATETIME NOT NULL,
 used_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX reset_user_created(user_id,created_at),
 CONSTRAINT fk_password_reset_user FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
