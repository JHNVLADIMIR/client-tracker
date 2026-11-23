USE client_tracker_db;
ALTER TABLE automations ADD COLUMN image_path VARCHAR(255) NULL AFTER status;
