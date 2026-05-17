INSERT INTO users (name, email, password)
VALUES ('Administrator', 'admin@usjr.edu.ph', '$2y$12$tfS/LR87jt1/grMpojtZ/utcqNsNRNimrQ1aF7BcUZl8TImzEYqX2');

INSERT INTO students (student_number, first_name, last_name, course, year_level, email, phone, address, status, created_at, updated_at)
VALUES
('2026-0001', 'Lyka', 'Entera', 'BSIT', 3, 'lyka.entera@usjr.edu.ph', '09170000001', 'Duljo', 'Active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('2026-0002', 'John Anthony', 'Romeo', 'BSCS', 2, 'johnanthony.romeo@usjr.edu.ph', '09170000002', 'Bulacao', 'Active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('2026-0003', 'Kea', 'Abaquita', 'BSIT', 4, 'kea.abaquita@usjr.edu.ph', '09170000003', 'Naga', 'Graduated', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);