-- Add login_attempts and is_locked columns to usercreds and admincreds tables
-- This migration adds the 3-attempt lockout feature to both user and admin login
-- Run this SQL script in phpMyAdmin or your MySQL client

-- Add login_attempts column to usercreds table
ALTER TABLE `usercreds` 
ADD COLUMN `login_attempts` INT(11) DEFAULT 0 AFTER `last_login`;

-- Add is_locked column to usercreds table
ALTER TABLE `usercreds` 
ADD COLUMN `is_locked` TINYINT(1) DEFAULT 0 AFTER `login_attempts`;

-- Add login_attempts column to admincreds table
ALTER TABLE `admincreds` 
ADD COLUMN `login_attempts` INT(11) DEFAULT 0;

-- Add is_locked column to admincreds table
ALTER TABLE `admincreds` 
ADD COLUMN `is_locked` TINYINT(1) DEFAULT 0 AFTER `login_attempts`;

-- Reset all existing accounts to unlocked state with 0 attempts
UPDATE `usercreds` SET `login_attempts` = 0, `is_locked` = 0;
UPDATE `admincreds` SET `login_attempts` = 0, `is_locked` = 0;

