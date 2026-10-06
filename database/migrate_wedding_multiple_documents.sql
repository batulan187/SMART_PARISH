USE smart_parish;

-- Allow each wedding requirement to store a JSON array of uploaded document paths.
ALTER TABLE wedding_booking_details
  MODIFY psa_birth_certificates TEXT NULL,
  MODIFY cenomar TEXT NULL,
  MODIFY baptism_confirmation TEXT NULL,
  MODIFY pmc_certificate TEXT NULL,
  MODIFY marriage_document TEXT NULL;
