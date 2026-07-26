-- =====================================================================
-- RAILWAY PASSENGER & TICKETING SYSTEM (railway_system_db)
-- Engine: MySQL 8.0+ / MariaDB 10.5+ (ROLE support required)
-- =====================================================================

DROP DATABASE IF EXISTS railway_system_db;
CREATE DATABASE railway_system_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE railway_system_db;

-- =====================================================================
-- 1. TABLES, COLUMNS & RELATIONSHIPS
-- =====================================================================

-- 1.1 passengers (master table)
CREATE TABLE passengers (
    passenger_id     INT AUTO_INCREMENT PRIMARY KEY,
    identity_number  VARCHAR(30)  NOT NULL UNIQUE,
    full_name        VARCHAR(100) NOT NULL,
    email            VARCHAR(100) UNIQUE,
    phone_number     VARCHAR(20),
    passenger_type   ENUM('ADULT','INFANT') NOT NULL DEFAULT 'ADULT',
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 1.2 passenger_details (1:1 with passengers)
CREATE TABLE passenger_details (
    detail_id                 INT AUTO_INCREMENT PRIMARY KEY,
    passenger_id              INT NOT NULL UNIQUE,   -- UNIQUE enforces 1:1
    address                   VARCHAR(255),
    emergency_contact_name    VARCHAR(100),
    emergency_contact_phone   VARCHAR(20),
    CONSTRAINT fk_detail_passenger
        FOREIGN KEY (passenger_id) REFERENCES passengers(passenger_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 1.3 trains
CREATE TABLE trains (
    train_id     INT AUTO_INCREMENT PRIMARY KEY,
    train_code   VARCHAR(20)  NOT NULL UNIQUE,
    train_name   VARCHAR(100) NOT NULL,
    train_class  ENUM('EXECUTIVE','BUSINESS','ECONOMY') NOT NULL,
    total_seats  INT NOT NULL
) ENGINE=InnoDB;

-- 1.4 schedules (1:N with trains -> one train has many schedules)
CREATE TABLE schedules (
    schedule_id        INT AUTO_INCREMENT PRIMARY KEY,
    train_id           INT NOT NULL,
    origin_station     VARCHAR(100) NOT NULL,
    destination_station VARCHAR(100) NOT NULL,
    departure_time     DATETIME NOT NULL,
    arrival_time       DATETIME NOT NULL,
    base_ticket_price  DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_schedule_train
        FOREIGN KEY (train_id) REFERENCES trains(train_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 1.5 bookings (N:M bridge table between passengers and schedules)
CREATE TABLE bookings (
    booking_id      INT AUTO_INCREMENT PRIMARY KEY,
    passenger_id    INT NOT NULL,
    schedule_id     INT NOT NULL,
    booking_code    VARCHAR(20) NOT NULL UNIQUE,
    seat_number     VARCHAR(10) NOT NULL,
    booking_status  ENUM('CONFIRMED','CANCELLED','COMPLETED') NOT NULL DEFAULT 'CONFIRMED',
    total_paid      DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_booking_passenger
        FOREIGN KEY (passenger_id) REFERENCES passengers(passenger_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_booking_schedule
        FOREIGN KEY (schedule_id) REFERENCES schedules(schedule_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    -- Requirement 5.1: inline composite index during CREATE TABLE
    INDEX idx_booking_passenger_schedule (passenger_id, schedule_id)
) ENGINE=InnoDB;

-- 1.6 passenger_travel_logs (designed to hold 1,000 - 10,000+ rows for indexing tests)
CREATE TABLE passenger_travel_logs (
    log_id        INT AUTO_INCREMENT PRIMARY KEY,
    passenger_id  INT NOT NULL,
    action_type   ENUM('GATE_IN','GATE_OUT','BOARDING') NOT NULL,
    gate_number   VARCHAR(10),
    scanned_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_passenger
        FOREIGN KEY (passenger_id) REFERENCES passengers(passenger_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- 5. INDEXES (Composite Keys) - #2 and #3 of the 3 required
-- =====================================================================

-- 5.2 CREATE INDEX on passenger_travel_logs (speeds up per-passenger scan history queries)
CREATE INDEX idx_log_passenger_scan
    ON passenger_travel_logs (passenger_id, scanned_at);

-- 5.3 ALTER TABLE index on schedules (speeds up route search: origin -> destination)
ALTER TABLE schedules
    ADD INDEX idx_schedule_route (origin_station, destination_station);

-- =====================================================================
-- 2. FUNCTIONS
-- =====================================================================

DELIMITER $$

-- 2.1 fn_get_total_executive_trains() - no parameters
CREATE FUNCTION fn_get_total_executive_trains()
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_total INT;
    SELECT COUNT(*) INTO v_total
    FROM trains
    WHERE train_class = 'EXECUTIVE';
    RETURN v_total;
END $$

-- 2.2 fn_calculate_discounted_ticket(p_schedule_id, p_discount_rate) - 2 parameters
CREATE FUNCTION fn_calculate_discounted_ticket(
    p_schedule_id INT,
    p_discount_rate DECIMAL(5,2)
)
RETURNS DECIMAL(12,2)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_base_price DECIMAL(12,2);
    DECLARE v_final_price DECIMAL(12,2);

    SELECT base_ticket_price INTO v_base_price
    FROM schedules
    WHERE schedule_id = p_schedule_id;

    IF v_base_price IS NULL THEN
        RETURN 0;
    END IF;

    SET v_final_price = v_base_price - (v_base_price * (p_discount_rate / 100));
    RETURN v_final_price;
END $$

DELIMITER ;

-- =====================================================================
-- 3. STORED PROCEDURES
-- =====================================================================

DELIMITER $$

-- 3.1 sp_archive_cancelled_bookings() - CURSOR + LOOP + IF, no parameters
-- "Archives" cancelled bookings by zeroing out total_paid (refund settled)
-- for every CANCELLED booking that still shows an outstanding paid amount.
CREATE PROCEDURE sp_archive_cancelled_bookings()
BEGIN
    DECLARE v_done INT DEFAULT FALSE;
    DECLARE v_booking_id INT;
    DECLARE v_total_paid DECIMAL(12,2);

    DECLARE cur_cancelled CURSOR FOR
        SELECT booking_id, total_paid
        FROM bookings
        WHERE booking_status = 'CANCELLED';

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = TRUE;

    OPEN cur_cancelled;

    read_loop: LOOP
        FETCH cur_cancelled INTO v_booking_id, v_total_paid;

        IF v_done THEN
            LEAVE read_loop;
        END IF;

        IF v_total_paid > 0 THEN
            UPDATE bookings
            SET total_paid = 0
            WHERE booking_id = v_booking_id;
        END IF;
    END LOOP read_loop;

    CLOSE cur_cancelled;
END $$

-- 3.2 sp_book_ticket(IN p_passenger_id, IN p_schedule_id) - IN params + IF control flow
CREATE PROCEDURE sp_book_ticket(
    IN p_passenger_id INT,
    IN p_schedule_id INT
)
BEGIN
    DECLARE v_total_seats INT;
    DECLARE v_booked_seats INT;
    DECLARE v_new_seat_number VARCHAR(10);
    DECLARE v_booking_code VARCHAR(20);
    DECLARE v_price DECIMAL(12,2);

    -- capacity of the train assigned to this schedule
    SELECT t.total_seats INTO v_total_seats
    FROM schedules s
    JOIN trains t ON s.train_id = t.train_id
    WHERE s.schedule_id = p_schedule_id;

    -- seats already taken (cancelled bookings free up a seat)
    SELECT COUNT(*) INTO v_booked_seats
    FROM bookings
    WHERE schedule_id = p_schedule_id
      AND booking_status IN ('CONFIRMED','COMPLETED');

    IF v_total_seats IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Schedule not found.';
    ELSEIF v_booked_seats >= v_total_seats THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No seats available for this schedule.';
    ELSE
        SELECT base_ticket_price INTO v_price
        FROM schedules WHERE schedule_id = p_schedule_id;

        SET v_new_seat_number = CONCAT('S', LPAD(v_booked_seats + 1, 3, '0'));
        SET v_booking_code = CONCAT('BK', p_schedule_id, '-', UNIX_TIMESTAMP());

        INSERT INTO bookings (passenger_id, schedule_id, booking_code, seat_number, booking_status, total_paid)
        VALUES (p_passenger_id, p_schedule_id, v_booking_code, v_new_seat_number, 'CONFIRMED', v_price);
    END IF;
END $$

DELIMITER ;

-- =====================================================================
-- 4. TRIGGERS
-- =====================================================================

DELIMITER $$

-- 4.1 trg_before_booking_insert (BEFORE INSERT) - validates seat availability using NEW
CREATE TRIGGER trg_before_booking_insert
BEFORE INSERT ON bookings
FOR EACH ROW
BEGIN
    DECLARE v_total_seats INT;
    DECLARE v_booked_seats INT;

    SELECT t.total_seats INTO v_total_seats
    FROM schedules s
    JOIN trains t ON s.train_id = t.train_id
    WHERE s.schedule_id = NEW.schedule_id;

    SELECT COUNT(*) INTO v_booked_seats
    FROM bookings
    WHERE schedule_id = NEW.schedule_id
      AND booking_status IN ('CONFIRMED','COMPLETED');

    IF v_booked_seats >= v_total_seats THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Cannot insert booking: schedule is fully booked.';
    END IF;
END $$

-- 4.2 trg_after_booking_status_update (AFTER UPDATE) - uses OLD and NEW
-- Logs a GATE_OUT event whenever a booking transitions into COMPLETED
CREATE TRIGGER trg_after_booking_status_update
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
    IF NEW.booking_status = 'COMPLETED' AND OLD.booking_status <> 'COMPLETED' THEN
        INSERT INTO passenger_travel_logs (passenger_id, action_type, gate_number, scanned_at)
        VALUES (NEW.passenger_id, 'GATE_OUT', NULL, NOW());
    END IF;
END $$

DELIMITER ;

-- =====================================================================
-- 6. VIEWS
-- =====================================================================

-- 6.1 v_confirmed_bookings (Horizontal View: filters ROWS, keeps most columns of one table)
CREATE VIEW v_confirmed_bookings AS
SELECT
    booking_id,
    passenger_id,
    schedule_id,
    booking_code,
    seat_number,
    total_paid
FROM bookings
WHERE booking_status = 'CONFIRMED';

-- 6.2 v_public_train_schedules (Vertical View: exposes a limited set of COLUMNS across a join,
-- hiding internal identifiers such as train_id; safe for public browsing)
CREATE VIEW v_public_train_schedules AS
SELECT
    s.schedule_id,
    t.train_name,
    t.train_class,
    s.origin_station,
    s.destination_station,
    s.departure_time,
    s.arrival_time,
    s.base_ticket_price
FROM schedules s
JOIN trains t ON s.train_id = t.train_id;

-- 6.3 v_executive_confirmed_bookings (View built ON TOP OF v_confirmed_bookings, i.e. "view inside view")
-- Uses a WHERE subquery (not a JOIN) so the view remains updatable, allowing CASCADED CHECK OPTION
-- to actually enforce that any UPDATE/INSERT through this view must keep satisfying "EXECUTIVE class".
CREATE VIEW v_executive_confirmed_bookings AS
SELECT *
FROM v_confirmed_bookings
WHERE schedule_id IN (
    SELECT s.schedule_id
    FROM schedules s
    JOIN trains t ON s.train_id = t.train_id
    WHERE t.train_class = 'EXECUTIVE'
)
WITH CASCADED CHECK OPTION;

-- =====================================================================
-- 7. DATABASE SECURITY (Users, Roles, Privileges)
-- =====================================================================

-- 7.1 Roles
CREATE ROLE IF NOT EXISTS 'role_railway_admin';
CREATE ROLE IF NOT EXISTS 'role_station_officer';
CREATE ROLE IF NOT EXISTS 'role_passenger_user';

-- 7.2 Users  (CHANGE these passwords before using in production/exam demo)
CREATE USER IF NOT EXISTS 'railway_admin'@'localhost'    IDENTIFIED BY 'Adm1n_Str0ngPass!';
CREATE USER IF NOT EXISTS 'station_officer'@'localhost'  IDENTIFIED BY 'Off1cer_Str0ngPass!';
CREATE USER IF NOT EXISTS 'passenger_user'@'localhost'   IDENTIFIED BY 'Pass3nger_Str0ngPass!';

-- 7.3 Privileges per role
-- Admin: full control over the entire schema
GRANT ALL PRIVILEGES ON railway_system_db.* TO 'role_railway_admin';

-- Station Officer: can read/update the operational views and run the booking procedure
GRANT SELECT, UPDATE ON railway_system_db.v_confirmed_bookings TO 'role_station_officer';
GRANT SELECT, UPDATE ON railway_system_db.v_executive_confirmed_bookings TO 'role_station_officer';
GRANT SELECT ON railway_system_db.v_public_train_schedules TO 'role_station_officer';
GRANT EXECUTE ON PROCEDURE railway_system_db.sp_book_ticket TO 'role_station_officer';
GRANT EXECUTE ON PROCEDURE railway_system_db.sp_archive_cancelled_bookings TO 'role_station_officer';

-- Passenger: read-only access to public information
GRANT SELECT ON railway_system_db.v_public_train_schedules TO 'role_passenger_user';
GRANT SELECT ON railway_system_db.v_confirmed_bookings TO 'role_passenger_user';

-- 7.4 Assign roles to users and activate them by default
GRANT 'role_railway_admin'   TO 'railway_admin'@'localhost';
GRANT 'role_station_officer' TO 'station_officer'@'localhost';
GRANT 'role_passenger_user'  TO 'passenger_user'@'localhost';

SET DEFAULT ROLE 'role_railway_admin'   TO 'railway_admin'@'localhost';
SET DEFAULT ROLE 'role_station_officer' TO 'station_officer'@'localhost';
SET DEFAULT ROLE 'role_passenger_user'  TO 'passenger_user'@'localhost';

FLUSH PRIVILEGES;

-- =====================================================================
-- END OF SCRIPT
-- =====================================================================
