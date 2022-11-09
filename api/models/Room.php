<?php

class Room
{
    const TABLE_ROOMS = 'rooms';

    private $conn;

    public int $ID_room;
    public int $ID_type;
    public string $description;
    public bool $active;

    public function __construct(PDO $db_connection)
    {
        $this->conn = $db_connection;
    }

    public function findFreeByDateInterval(DateTime $from, DateTime $to): array|false
    {
        $query = 'SELECT * FROM ' . addslashes($this::TABLE_ROOMS) . '
        WHERE 
        ID_room NOT IN (
            SELECT ID_room from ' . addslashes(Reservation::TABLE_RESERVATIONS) . '
            WHERE 
            (
                (date_from >= :d_from AND date_from < :d_to) 
                OR
                (date_to > :d_from AND date_to <= :d_to)
            )
            OR (date_from < :d_from AND date_to > :d_to)
        )';

        $stmt = $this->conn->prepare($query);

        $d_from = date_format($from, 'Y-m-d');
        $d_to = date_format($to, 'Y-m-d');

        $stmt->bindParam(':d_from', $d_from);
        $stmt->bindParam(':d_to', $d_to);

        $result = $stmt->execute();

        if ($result === false) {
            return false;
        }

        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row===false) {
                break;
            }

            extract($row);

            $this->ID_room = htmlspecialchars($ID_room);
            $this->ID_type = htmlspecialchars($ID_type);
            $this->description = htmlspecialchars($description);
            $this->active = htmlspecialchars($active);

            $data[] = clone ($this);
        }

        return $data;
    }

    public function findOneFreeByTypeIdAndDateInterval(DateTime $from, DateTime $to, int $type_id): Room|false
    {
        $query = 'SELECT * FROM ' . addslashes($this::TABLE_ROOMS) . '
        WHERE 
        ID_room NOT IN (
            SELECT ID_room from ' . addslashes(Reservation::TABLE_RESERVATIONS) . '
            WHERE 
            (
                (date_from >= :d_from AND date_from < :d_to) 
                OR
                (date_to > :d_from AND date_to <= :d_to)
            )
            OR (date_from < :d_from AND date_to > :d_to)
        ) 
        AND ID_type = :type_id
        AND active = true
        LIMIT 0,1';

        $stmt = $this->conn->prepare($query);

        $d_from = date_format($from, 'Y-m-d');
        $d_to = date_format($to, 'Y-m-d');

        $stmt->bindParam(':d_from', $d_from);
        $stmt->bindParam(':d_to', $d_to);
        $stmt->bindParam(':type_id', $type_id);

        $result = $stmt->execute();

        if ($result === false) {
            return false;
        }

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data===false) {
            return $this;
        }

        extract($data);

        $this->ID_room = htmlspecialchars($ID_room);
        $this->ID_type = htmlspecialchars($ID_type);
        $this->description = htmlspecialchars($description);
        $this->active = htmlspecialchars($active);

        return $this;
    }
}
