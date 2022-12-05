<?php

class Type
{
    const TABLE_TYPES = 'types';

    private $conn;

    public int $ID_type;
    public int $beds_number;
    public bool $double_bed;
    public bool $business;
    public string $name;
    public string $description;
    public string $picture;
    public ?int $numberOfAvailable;

    public function __construct(PDO $db_connection)
    {
        $this->conn = $db_connection;
    }

    public function findTypeById($id_type): Type|false
    {
        $query = 'SELECT * FROM ' . addslashes($this::TABLE_TYPES) . ' 
        WHERE ID_type = :id_type';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam('id_type', $id_type, PDO::PARAM_INT);

        $stmt->execute();

        $result = $stmt->execute();

        if ($result === false) {
            return false;
        }

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data === false) {
            return $this;
        }

        $this->ID_type = htmlspecialchars($data['ID_type']);
        $this->beds_number = htmlspecialchars($data['beds_number']);
        $this->double_bed = htmlspecialchars((bool)$data['double_bed']);
        $this->business = htmlspecialchars((bool)$data['business']);
        $this->name = htmlspecialchars($data['name']);
        $this->description = htmlspecialchars($data['description']);
        $this->picture = htmlspecialchars($data['picture']);

        return $this;
    }

    public function findFreeTypesByDateInterval(DateTime $from, DateTime $to): array|false
    {
        $query = 'SELECT * FROM ' . addslashes($this::TABLE_TYPES) . '
        WHERE ID_type IN (
            SELECT ID_type FROM ' . addslashes(Room::TABLE_ROOMS) . '
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

        $data = $this->fetchDataFromStatment($stmt);

        return $data;
    }

    public function findFreeTypesByDateIntervalAndMinBedsNumber(DateTime $from, DateTime $to, int $min_number_of_beds): array|false
    {
        $query = 
        'SELECT t.ID_type, t.beds_number, t.double_bed, t.business, t.name, t.description, t.picture, r.numberOfAvailable FROM 
        ' . addslashes($this::TABLE_TYPES) . ' t '. 
        'INNER JOIN (
         SELECT count(id_room) numberOfAvailable, ID_type FROM 
         ' . addslashes(Room::TABLE_ROOMS) . '
         WHERE ID_room NOT IN (
            SELECT ID_room FROM 
            ' . addslashes(Reservation::TABLE_RESERVATIONS) . '
            WHERE (
                (date_from >= :d_from and date_from < :d_to)
                or
                (date_to > :d_from and date_to <= :d_to)
            )
            or (date_from < :d_from and date_to > :d_to)
            ) 
         GROUP BY ID_type
         ) r
         ON r.ID_type = t.ID_type
         WHERE t.beds_number >= :beds_num';

        $stmt = $this->conn->prepare($query);

        $d_from = date_format($from, 'Y-m-d');
        $d_to = date_format($to, 'Y-m-d');

        $stmt->bindParam(':d_from', $d_from);
        $stmt->bindParam(':d_to', $d_to);
        $stmt->bindParam(':beds_num', $min_number_of_beds);

        $result = $stmt->execute();

        if ($result === false) {
            return false;
        }

        $data = $this->fetchDataFromStatment($stmt);

        return $data;
    }

    private function fetchDataFromStatment(PDOStatement $statement): array
    {
        $data = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if ($row === false) {
                break;
            }
            extract($row);

            $this->ID_type = htmlspecialchars($ID_type);
            $this->beds_number = htmlspecialchars($beds_number);
            $this->double_bed = htmlspecialchars((bool)$double_bed);
            $this->business = htmlspecialchars((bool)$business);
            $this->name = htmlspecialchars($name);
            $this->description = htmlspecialchars($description);
            $this->picture = htmlspecialchars($picture);
            $this->numberOfAvailable = htmlspecialchars($numberOfAvailable);

            $data[] = clone ($this);
        }

        return $data;
    }
}
