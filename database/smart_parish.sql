CREATE DATABASE IF NOT EXISTS smart_parish CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smart_parish;
CREATE TABLE users (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,first_name VARCHAR(80) NOT NULL,middle_name VARCHAR(80) NULL,last_name VARCHAR(80) NOT NULL,full_name VARCHAR(245) NOT NULL,email VARCHAR(190) NOT NULL UNIQUE,contact_number VARCHAR(30) NOT NULL,province_code VARCHAR(20) NULL,province VARCHAR(120) NULL,city_municipality_code VARCHAR(20) NULL,city_municipality VARCHAR(160) NULL,barangay_code VARCHAR(20) NULL,barangay VARCHAR(160) NULL,street_purok VARCHAR(255) NULL,address VARCHAR(500) NOT NULL,birthday DATE NOT NULL,gender ENUM('Male','Female','Other') NOT NULL,password VARCHAR(255) NOT NULL,profile_image VARCHAR(255),role ENUM('parishioner','admin') NOT NULL DEFAULT 'parishioner',status ENUM('active','inactive') NOT NULL DEFAULT 'active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP);
CREATE TABLE services (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,description TEXT,duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 60,fee DECIMAL(10,2) NOT NULL DEFAULT 0,image VARCHAR(255),status ENUM('active','inactive') NOT NULL DEFAULT 'active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE resources (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,description TEXT,capacity INT UNSIGNED,status ENUM('available','unavailable') DEFAULT 'available',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE appointments (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,reference_no VARCHAR(30) NOT NULL UNIQUE,user_id INT UNSIGNED NOT NULL,service_id INT UNSIGNED NOT NULL,resource_id INT UNSIGNED NULL,appointment_date DATE NOT NULL,start_time TIME NOT NULL,end_time TIME NOT NULL,notes TEXT,status ENUM('pending','approved','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',admin_note TEXT,payment_method ENUM('cash','online') NOT NULL DEFAULT 'cash',receipt_path VARCHAR(255) NULL,payment_status ENUM('not_required','pending','verified','rejected') NOT NULL DEFAULT 'not_required',payment_verified_at DATETIME NULL,payment_verified_by INT UNSIGNED NULL,qr_confirmed_at DATETIME NULL,qr_confirmed_by INT UNSIGNED NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,FOREIGN KEY(service_id) REFERENCES services(id),FOREIGN KEY(resource_id) REFERENCES resources(id) ON DELETE SET NULL,INDEX idx_schedule(appointment_date,start_time,end_time),INDEX idx_status(status));
CREATE TABLE wedding_booking_details (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,appointment_id BIGINT UNSIGNED NOT NULL UNIQUE,groom_full_name VARCHAR(245) NOT NULL,groom_dob DATE NOT NULL,groom_religion_parish VARCHAR(255) NOT NULL,bride_full_name VARCHAR(245) NOT NULL,bride_dob DATE NOT NULL,bride_religion_parish VARCHAR(255) NOT NULL,contact_number VARCHAR(30) NOT NULL,contact_email VARCHAR(190) NOT NULL,ceremony_type VARCHAR(80) NOT NULL,preferred_officiant VARCHAR(160) NULL,civil_requirement VARCHAR(40) NOT NULL DEFAULT 'license',psa_birth_certificates TEXT NULL,cenomar TEXT NULL,baptism_confirmation TEXT NULL,pmc_certificate TEXT NULL,marriage_document TEXT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(appointment_id) REFERENCES appointments(id) ON DELETE CASCADE);
CREATE TABLE appointment_history (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,appointment_id BIGINT UNSIGNED NOT NULL,status VARCHAR(30) NOT NULL,note TEXT,changed_by INT UNSIGNED,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,FOREIGN KEY(changed_by) REFERENCES users(id) ON DELETE SET NULL);
CREATE TABLE events (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,title VARCHAR(160) NOT NULL,description TEXT,event_date DATE NOT NULL,start_time TIME,end_time TIME,category VARCHAR(60),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE announcements (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,title VARCHAR(180) NOT NULL,content TEXT NOT NULL,image VARCHAR(255),published_by INT UNSIGNED,published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(published_by) REFERENCES users(id) ON DELETE SET NULL);
CREATE TABLE notifications (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id INT UNSIGNED NOT NULL,title VARCHAR(180) NOT NULL,message TEXT NOT NULL,is_read BOOLEAN NOT NULL DEFAULT FALSE,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE);
CREATE TABLE payment_settings (id TINYINT UNSIGNED PRIMARY KEY DEFAULT 1,gcash_account_name VARCHAR(160) NULL,gcash_number VARCHAR(30) NULL,gcash_qr_path VARCHAR(255) NULL,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP);
INSERT INTO payment_settings(id) VALUES(1);
CREATE TABLE activity_logs (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id INT UNSIGNED,action VARCHAR(190) NOT NULL,details TEXT,ip_address VARCHAR(45),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL);
INSERT INTO services(name,description,duration_minutes,fee) VALUES ('Baptism','Schedule a baptism and submit the required details.',60,0),('Wedding','Reserve a church wedding schedule.',120,0),('Blessing','Request a home, business, or object blessing.',60,0),('Mass Intention','Request a Mass intention.',60,0);
INSERT INTO resources(name,description,capacity) VALUES ('Parish Hall','Main parish function hall',150),('Room A','Meeting and formation room',35),('Room B','Small meeting room',20);
CREATE TABLE IF NOT EXISTS baptism_booking_details (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  appointment_id BIGINT UNSIGNED NOT NULL UNIQUE,
  child_full_name VARCHAR(245) NOT NULL,
  child_dob DATE NOT NULL,
  child_birth_place VARCHAR(255) NOT NULL,
  father_full_name VARCHAR(245) NOT NULL,
  mother_maiden_name VARCHAR(245) NOT NULL,
  parents_marriage_status VARCHAR(60) NOT NULL,
  contact_number VARCHAR(30) NOT NULL,
  contact_email VARCHAR(190) NOT NULL,
  baptism_type VARCHAR(40) NOT NULL,
  principal_male_godparent VARCHAR(245) NOT NULL,
  principal_female_godparent VARCHAR(245) NOT NULL,
  additional_godparents TEXT NULL,
  extra_godparent_count INT UNSIGNED NOT NULL DEFAULT 0,
  extra_godparent_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
  psa_birth_certificate VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
