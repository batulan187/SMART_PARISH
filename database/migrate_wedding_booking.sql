USE smart_parish;
CREATE TABLE IF NOT EXISTS wedding_booking_details (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  appointment_id BIGINT UNSIGNED NOT NULL UNIQUE,
  groom_full_name VARCHAR(245) NOT NULL,
  groom_dob DATE NOT NULL,
  groom_religion_parish VARCHAR(255) NOT NULL,
  bride_full_name VARCHAR(245) NOT NULL,
  bride_dob DATE NOT NULL,
  bride_religion_parish VARCHAR(255) NOT NULL,
  contact_number VARCHAR(30) NOT NULL,
  contact_email VARCHAR(190) NOT NULL,
  ceremony_type VARCHAR(80) NOT NULL,
  preferred_officiant VARCHAR(160) NULL,
  civil_requirement VARCHAR(40) NOT NULL DEFAULT 'license',
  psa_birth_certificates TEXT NULL,
  cenomar TEXT NULL,
  baptism_confirmation TEXT NULL,
  pmc_certificate TEXT NULL,
  marriage_document TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_wedding_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
);
