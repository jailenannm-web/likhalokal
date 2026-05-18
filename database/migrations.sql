-- LikhaLokal incremental migrations
-- Safe to run on existing likhalokal_db (run once; ignore duplicate column/table errors on re-run)

USE likhalokal_db;

SET NAMES utf8mb4;

-- Users: Google OAuth & nullable password
ALTER TABLE users MODIFY COLUMN password_hash VARCHAR(255) NULL;
-- run once; ignore duplicate column error:
-- ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL AFTER email;
-- ALTER TABLE users ADD COLUMN auth_provider ENUM('local','google') NOT NULL DEFAULT 'local' AFTER google_id;
-- ALTER TABLE users ADD COLUMN last_seen_at DATETIME NULL AFTER email_verified_at;

ALTER TABLE users ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) NULL AFTER email;
ALTER TABLE users ADD COLUMN IF NOT EXISTS auth_provider ENUM('local','google') NOT NULL DEFAULT 'local' AFTER google_id;
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_seen_at DATETIME NULL;

-- Optional unique indexes (ignore errors if they already exist)
-- ALTER TABLE users ADD UNIQUE INDEX idx_users_email (email);
-- ALTER TABLE users ADD UNIQUE INDEX idx_users_google_id (google_id);

-- Businesses
ALTER TABLE businesses ADD COLUMN IF NOT EXISTS business_category VARCHAR(120) NULL AFTER business_type;
ALTER TABLE businesses ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,7) NULL;
ALTER TABLE businesses ADD COLUMN IF NOT EXISTS longitude DECIMAL(10,7) NULL;
ALTER TABLE businesses ADD COLUMN IF NOT EXISTS auto_reply_enabled TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE businesses ADD COLUMN IF NOT EXISTS auto_reply_message TEXT NULL;
ALTER TABLE businesses ADD COLUMN IF NOT EXISTS faq_price TEXT NULL;
ALTER TABLE businesses ADD COLUMN IF NOT EXISTS faq_availability TEXT NULL;
ALTER TABLE businesses ADD COLUMN IF NOT EXISTS faq_location TEXT NULL;
ALTER TABLE businesses ADD COLUMN IF NOT EXISTS faq_payment TEXT NULL;
ALTER TABLE businesses ADD COLUMN IF NOT EXISTS faq_delivery TEXT NULL;
ALTER TABLE businesses ADD COLUMN IF NOT EXISTS faq_hours TEXT NULL;
ALTER TABLE businesses ADD COLUMN IF NOT EXISTS faq_custom TEXT NULL;

-- Tourist attractions detail fields
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS history TEXT NULL;
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS travel_guide TEXT NULL;
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS entrance_fee VARCHAR(120) NULL;
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS best_time_to_visit VARCHAR(255) NULL;
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS address VARCHAR(255) NULL;
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,7) NULL;
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS longitude DECIMAL(10,7) NULL;
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS image VARCHAR(255) NULL;

-- Messages
ALTER TABLE messages ADD COLUMN IF NOT EXISTS product_id INT UNSIGNED NULL;
ALTER TABLE messages ADD COLUMN IF NOT EXISTS is_read TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE messages ADD COLUMN IF NOT EXISTS is_auto_reply TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE messages ADD COLUMN IF NOT EXISTS attachment_path VARCHAR(255) NULL;
ALTER TABLE messages ADD COLUMN IF NOT EXISTS attachment_type VARCHAR(50) NULL;
ALTER TABLE messages ADD COLUMN IF NOT EXISTS inquiry_context VARCHAR(255) NULL;
ALTER TABLE messages ADD COLUMN IF NOT EXISTS conversation_type ENUM('business_inquiry','admin_support') NOT NULL DEFAULT 'business_inquiry';

-- Password resets
CREATE TABLE IF NOT EXISTS password_resets (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  used_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_password_resets_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Remember me tokens
CREATE TABLE IF NOT EXISTS user_remember_tokens (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  selector VARCHAR(255) NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY idx_remember_selector (selector),
  KEY idx_remember_user (user_id),
  CONSTRAINT fk_remember_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
