# Smart Parish Management System

## Install (XAMPP)
1. Copy `smart-parish` into `C:\xampp\htdocs\`.
2. Start Apache and MySQL.
3. In phpMyAdmin, create/import a fresh database using `database/smart_parish.sql`.
4. Visit `http://localhost/smart-parish/database/create-admin.php` once.
5. Delete `database/create-admin.php` after the admin is created.
6. Open `http://localhost/smart-parish/`.

Admin starter credentials:
- Email: admin@smartparish.local
- Password: Admin123!

## Parishioner features
Registration/login, protected dashboard, service booking, resource conflict checks, appointment history and cancellation, appointment detail/timeline and printable slip, calendar, announcements, profile editing, password changes, logout.

## Important schema note
This build uses `users.full_name`, `contact_number`, `birthday`, and `gender`. If you imported an older Smart Parish schema that used `first_name`, `last_name`, or `phone`, use a fresh development database or migrate it before running this build.

## Philippine address selector
Registration and parishioner profile use cascading Province → City/Municipality → Barangay selectors and a typed Purok/Street field. For an existing database created by an older build, run `database/migrate_address_fields.sql` once. Fresh installs already include the new columns in `database/smart_parish.sql`.
The cascading list is loaded from PSGC Cloud in the browser, so the registration/profile page needs internet access while selecting an address.

## Payment + appointment QR upgrade
For an existing database, import `database/migrate_payment_qr.sql` once before using the upgraded project.
For a fresh installation, `database/smart_parish.sql` already contains the payment tables/columns.

Admin setup:
1. Open **Payment Settings** and enter the GCash account name and number, then upload the GCash QR image.
2. Online payment automatically stays disabled for parishioners until all three are configured.
3. For an online booking, open **Appointments**, view the protected receipt, and click **Verify payment** before approving it.
4. Approved appointments show the parishioner an appointment QR/reference. At check-in, use **Verify QR / Reference** to look up the reference and confirm it. Confirmation marks the approved appointment completed (only on/after its appointment date).

The appointment QR image is rendered through an online QR image service, so displaying that QR requires internet access. The reference number is always shown below it as a fallback for admin verification.

## Wedding booking wizard migration
For the 4-step Wedding booking form, import `database/migrate_wedding_booking.sql` once into the `smart_parish` database. It creates `wedding_booking_details` for the couple, ceremony, and optional document-upload information.
