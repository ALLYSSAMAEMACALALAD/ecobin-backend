-- ============================================================
-- Eco-Bin sample data for development and demos.
-- Run AFTER schema.sql. All sample accounts use the password: password123
-- CHANGE these accounts before any real deployment.
-- ============================================================
USE ecobin_db;

INSERT INTO users (full_name, email, phone, password_hash, role, points_balance, status) VALUES
('Municipal Admin',       'admin@ecobin.test',     '09170000001', '$2y$10$6IGR5VGysBoYX8yZkgHWTON//7ACmPlYiAfDhRE2xXEUngucfm7rW', 'admin',     0,   'active'),
('Juan Dela Cruz',        'collector@ecobin.test', '09170000002', '$2y$10$6IGR5VGysBoYX8yZkgHWTON//7ACmPlYiAfDhRE2xXEUngucfm7rW', 'collector', 0,   'active'),
('Maria Santos',          'resident@ecobin.test',  '09170000003', '$2y$10$6IGR5VGysBoYX8yZkgHWTON//7ACmPlYiAfDhRE2xXEUngucfm7rW', 'resident',  25, 'active');

INSERT INTO collection_points (name, address, latitude, longitude, point_type) VALUES
('Drop-off Hub (Barangay Hall)', 'Barangay Hall, Purok 1', 14.5995000, 120.9842000, 'hub'),
('Purok 3 Bin #14',              'Purok 3, near the chapel', 14.6010000, 120.9860000, 'bin'),
('Purok 2 Hub Gate',             'Purok 2 main gate',    14.6003000, 120.9835000, 'hub');

INSERT INTO schedules (point_id, waste_type, collection_date, time_window, created_by) VALUES
(1, 'Biodegradable', DATE_ADD(CURDATE(), INTERVAL 7 DAY),  '7-9 AM', 1),
(2, 'Recyclables',   DATE_ADD(CURDATE(), INTERVAL 10 DAY), '7-9 AM', 1),
(3, 'Residual',      DATE_ADD(CURDATE(), INTERVAL 14 DAY), '1-3 PM', 1);

INSERT INTO rewards (name, points_cost, stock, is_active) VALUES
('Reusable tote bag', 150, 20, 1),
('Plant seedling',    100, 30, 1),
('Bus fare credit',   250, 10, 1);

INSERT INTO drop_off_logs (resident_id, point_id, recorded_by, material_type, weight_kg, points_awarded, qr_value) VALUES
(3, 1, 2, 'PET bottles', 2.50, 25, 'ECOID-0003');
