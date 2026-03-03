-- Run this if you get "Unknown column 'name' in 'field list'"
-- (e.g. users table already existed with a different structure)

ALTER TABLE users ADD COLUMN name VARCHAR(255) NOT NULL DEFAULT 'User' AFTER id;
