<?php

class Reservation
{
    const TABLE_RESERVATIONS = 'reservations';

    private $conn;

    public ?int $ID_reservation;
    public DateTime $date_from;
    public DateTime $date_to;
    public ?bool $paid = false;
    public int $ID_room;
    public int $ID_guest;

    public function __construct(PDO $db_connection)
    {
        $this->conn = $db_connection;
    }

    public function findReservationsByDateInterval(DateTime $from, DateTime $to): array|false
    {
        $query = 'SELECT * FROM ' . addslashes($this::TABLE_RESERVATIONS)
            . 'WHERE 
        (date_from >= :d_from AND date_from < :d_from) 
        OR
        (date_to <= :d_to AND date_to > :d_to)';

        $d_from = date_format($from, 'Y-m-d');
        $d_to = date_format($to, 'Y-m-d');

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':d_from', $d_from);
        $stmt->bindParam(':d_to', $d_to);

        $result = $stmt->execute();

        if ($result === false) {
            return false;
        }

        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $this->ID_reservation = $ID_reservation;
            $this->date_from = date_create($date_from);
            $this->date_to = date_create($date_to);
            $this->paid = (bool)$paid;
            $this->ID_room = $ID_room;
            $this->ID_guest = $ID_guest;

            $data = new $this;
        }

        return $data;
    }

    public function createNew(): int|false
    {
        $query = 'INSERT INTO ' . $this::TABLE_RESERVATIONS . '
        (date_from, date_to, ID_room, ID_guest) 
        VALUES (:d_from, :d_to, :id_room, :id_guest)';

        $stmt = $this->conn->prepare($query);

        $from = date_format($this->date_from, 'Y-m-d');
        $to = date_format($this->date_to, 'Y-m-d');

        $stmt->bindParam(':d_from', $from);
        $stmt->bindParam(':d_to', $to);
        $stmt->bindParam(':id_room', $this->ID_room);
        $stmt->bindParam(':id_guest', $this->ID_guest);

        $result = $stmt->execute();

        if ($result === false) {
            return false;
        }

        $stmt->closeCursor();

        $query = 'SELECT LAST_INSERT_ID() AS ID';

        $stmt=$this->conn->prepare($query);

        $result=$stmt->execute();

        if ($result === false) {
            return false;
        }

        $data=$stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data === false) {
            return false;
        }

        return $data['ID'];
    }
}
