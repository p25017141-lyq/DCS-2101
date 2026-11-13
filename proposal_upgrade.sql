-- Repair AUTO_INCREMENT while preserving the existing data and restoring foreign keys.
-- Run while the local website is not being used. Back up eventseat first.
ALTER TABLE events DROP FOREIGN KEY fk_events_venue;
ALTER TABLE seats DROP FOREIGN KEY fk_seats_venue;
ALTER TABLE booking_seats DROP FOREIGN KEY fk_booking_seats_seat;

ALTER TABLE venues MODIFY venue_id INT UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE seats MODIFY seat_id INT UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE events ADD CONSTRAINT fk_events_venue FOREIGN KEY (venue_id) REFERENCES venues (venue_id);
ALTER TABLE seats ADD CONSTRAINT fk_seats_venue FOREIGN KEY (venue_id) REFERENCES venues (venue_id);
ALTER TABLE booking_seats ADD CONSTRAINT fk_booking_seats_seat FOREIGN KEY (seat_id) REFERENCES seats (seat_id);
