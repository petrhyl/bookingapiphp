--database
--CREATE DATABASE booking;

--user
--CREATE USER 'admin'@'localhost' IDENTIFIED BY '';
--GRANT ALL PRIVILEGES ON booking.* TO 'admin'@'localhost';

CREATE TABLE booking.rooms (
	ID_room int NOT NULL, 
	ID_type int NOT NULL,
	description varchar(255),
	active bool NOT NULL DEFAULT true,
	PRIMARY KEY (ID_room)
);
CREATE TABLE booking.types (
	ID_type int NOT NULL AUTO_INCREMENT,
	beds_number int NOT NULL,
	double_bed bool NOT NULL,
	business bool NOT NULL,
	name varchar(255) NOT NULL,
	description varchar(500) NOT NULL,
	PRIMARY KEY (ID_type)
);
CREATE TABLE booking.reservations (
	ID_reservation int NOT NULL AUTO_INCREMENT,
	date_from date NOT NULL,
	date_to date NOT NULL,
	paid bool NOT NULL DEFAULT false,
	ID_room int NOT NULL, 
	ID_guest int NOT NULL,
	PRIMARY KEY (ID_reservation)
);
CREATE TABLE booking.guests (
	ID_guest int NOT NULL AUTO_INCREMENT,
	firstname varchar(50) NOT NULL,
	lastname varchar(50) NOT NULL,
	email varchar(255) NOT NULL,
	address varchar(255),
	city varchar(100),
	country varchar(100),
	PRIMARY KEY (ID_guest)
);

ALTER TABLE booking.rooms
	ADD FOREIGN KEY (ID_type) REFERENCES booking.types(ID_type);
ALTER TABLE booking.reservations
	ADD FOREIGN KEY (ID_room) REFERENCES booking.rooms(ID_room);
ALTER TABLE booking.reservations
	ADD FOREIGN KEY (ID_guest) REFERENCES booking.guests(ID_guest);
	
	