-- ============================================================
-- Eco-Bin database schema (MySQL 8 / MariaDB 10.4+, InnoDB, utf8mb4)
-- Matches section 4 (Database Design) of the Checking 1 document.
-- Import with Navicat: File > Run SQL File, or run `mysql -u root < schema.sql`.
-- ============================================================

CREATE DATABASE IF NOT EXISTS ecobin_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecobin_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS audit_logs, notifications, dumping_reports, redemptions, rewards,
                     drop_off_logs, schedules, route_stops, routes, collection_points, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    user_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name      VARCHAR(120)  NOT NULL,
    email          VARCHAR(150)  NOT NULL UNIQUE,
    phone          VARCHAR(20)   NULL,
    password_hash  VARCHAR(255)  NOT NULL,
    role           ENUM('resident','collector','admin') NOT NULL DEFAULT 'resident',
    points_balance INT UNSIGNED  NOT NULL DEFAULT 0,
    status         ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_role (role)
) ENGINE=InnoDB;

CREATE TABLE collection_points (
    point_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    address     VARCHAR(255) NOT NULL,
    latitude    DECIMAL(10,7) NULL,
    longitude   DECIMAL(10,7) NULL,
    point_type  ENUM('hub','bin') NOT NULL DEFAULT 'bin',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE routes (
    route_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(120) NOT NULL,
    collector_id INT UNSIGNED NULL,
    route_date   DATE NOT NULL,
    status       ENUM('planned','in_progress','completed') NOT NULL DEFAULT 'planned',
    CONSTRAINT fk_routes_collector FOREIGN KEY (collector_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE route_stops (
    stop_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    route_id    INT UNSIGNED NOT NULL,
    point_id    INT UNSIGNED NOT NULL,
    sequence_no INT UNSIGNED NOT NULL,
    stop_status ENUM('pending','done') NOT NULL DEFAULT 'pending',
    CONSTRAINT fk_stops_route FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE,
    CONSTRAINT fk_stops_point FOREIGN KEY (point_id) REFERENCES collection_points(point_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE schedules (
    schedule_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    point_id        INT UNSIGNED NOT NULL,
    waste_type      VARCHAR(40) NOT NULL,
    collection_date DATE NOT NULL,
    time_window     VARCHAR(50) NOT NULL,
    created_by      INT UNSIGNED NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_schedules_point   FOREIGN KEY (point_id)   REFERENCES collection_points(point_id) ON DELETE CASCADE,
    CONSTRAINT fk_schedules_creator FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_schedules_date (collection_date)
) ENGINE=InnoDB;

CREATE TABLE drop_off_logs (
    log_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resident_id    INT UNSIGNED NOT NULL,
    point_id       INT UNSIGNED NOT NULL,
    recorded_by    INT UNSIGNED NULL,
    material_type  VARCHAR(60) NOT NULL,
    weight_kg      DECIMAL(8,2) NOT NULL,
    points_awarded INT UNSIGNED NOT NULL DEFAULT 0,
    qr_value       VARCHAR(100) NULL,
    logged_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dropoff_resident FOREIGN KEY (resident_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_dropoff_point    FOREIGN KEY (point_id)    REFERENCES collection_points(point_id) ON DELETE CASCADE,
    CONSTRAINT fk_dropoff_staff    FOREIGN KEY (recorded_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE rewards (
    reward_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    points_cost INT UNSIGNED NOT NULL,
    stock       INT UNSIGNED NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE redemptions (
    redemption_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resident_id   INT UNSIGNED NOT NULL,
    reward_id     INT UNSIGNED NOT NULL,
    points_used   INT UNSIGNED NOT NULL,
    status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    verified_by   INT UNSIGNED NULL,
    requested_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_redeem_resident FOREIGN KEY (resident_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_redeem_reward   FOREIGN KEY (reward_id)   REFERENCES rewards(reward_id) ON DELETE CASCADE,
    CONSTRAINT fk_redeem_admin    FOREIGN KEY (verified_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE dumping_reports (
    report_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resident_id INT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    location_text VARCHAR(255) NOT NULL,
    latitude    DECIMAL(10,7) NULL,
    longitude   DECIMAL(10,7) NULL,
    photo_url   VARCHAR(500) NULL,
    status      ENUM('Open','In Progress','Resolved') NOT NULL DEFAULT 'Open',
    assigned_to INT UNSIGNED NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_report_resident FOREIGN KEY (resident_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_report_assignee FOREIGN KEY (assigned_to) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
    notif_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id  INT UNSIGNED NOT NULL,
    channel  ENUM('SMS','Email') NOT NULL,
    message  TEXT NOT NULL,
    status   VARCHAR(20) NOT NULL DEFAULT 'queued',
    sent_at  DATETIME NULL,
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    log_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NULL,
    action     VARCHAR(100) NOT NULL,
    table_name VARCHAR(60) NULL,
    record_id  INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;
