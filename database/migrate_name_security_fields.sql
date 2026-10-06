USE smart_parish;
ALTER TABLE users
  ADD COLUMN first_name VARCHAR(80) NULL AFTER id,
  ADD COLUMN middle_name VARCHAR(80) NULL AFTER first_name,
  ADD COLUMN last_name VARCHAR(80) NULL AFTER middle_name;
-- Existing accounts keep full_name. New registrations populate all name fields.
-- After reviewing/migrating existing names, first_name/last_name may be changed to NOT NULL.
