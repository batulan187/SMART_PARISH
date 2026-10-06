USE smart_parish;
ALTER TABLE users
  ADD COLUMN province_code VARCHAR(20) NULL AFTER contact_number,
  ADD COLUMN province VARCHAR(120) NULL AFTER province_code,
  ADD COLUMN city_municipality_code VARCHAR(20) NULL AFTER province,
  ADD COLUMN city_municipality VARCHAR(160) NULL AFTER city_municipality_code,
  ADD COLUMN barangay_code VARCHAR(20) NULL AFTER city_municipality,
  ADD COLUMN barangay VARCHAR(160) NULL AFTER barangay_code,
  ADD COLUMN street_purok VARCHAR(255) NULL AFTER barangay;
