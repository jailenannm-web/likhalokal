-- LikhaLokal incremental migrations
-- Safe to run on existing likhalokal_db (e.g. after importing likhalokal_db-3.sql)

USE likhalokal_db;

SET NAMES utf8mb4;

-- Allow Google-only accounts (no local password)
ALTER TABLE users MODIFY COLUMN password_hash VARCHAR(255) NULL;

-- Google OAuth columns (run once; ignore "Duplicate column" if already applied)
ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL AFTER email;
ALTER TABLE users ADD COLUMN auth_provider ENUM('local','google') NOT NULL DEFAULT 'local' AFTER google_id;

-- Optional unique index for google_id
-- ALTER TABLE users ADD UNIQUE INDEX idx_users_google_id (google_id);

-- Profile image for local users (ignore error if column already exists)
-- ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) NULL AFTER contact_number;

-- Business profile / marketplace consistency fields.
ALTER TABLE businesses ADD COLUMN IF NOT EXISTS business_category VARCHAR(120) NULL AFTER business_type;
ALTER TABLE products ADD COLUMN IF NOT EXISTS product_type ENUM('product','service','tour_package','accommodation','food','other') NOT NULL DEFAULT 'product' AFTER product_name;

-- Tourism attraction detail fields used by public cards and admin CRUD.
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS history TEXT NULL AFTER description;
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS travel_guide TEXT NULL AFTER history;
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS entrance_fee VARCHAR(120) NULL AFTER travel_guide;
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS best_time_to_visit VARCHAR(255) NULL AFTER entrance_fee;
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS address VARCHAR(255) NULL AFTER best_time_to_visit;
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,7) NULL AFTER address;
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS longitude DECIMAL(10,7) NULL AFTER latitude;
ALTER TABLE tourist_attractions ADD COLUMN IF NOT EXISTS image VARCHAR(255) NULL AFTER longitude;
