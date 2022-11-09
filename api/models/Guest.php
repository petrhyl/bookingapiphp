<?php

class Guest
{
    const TABLE_GUESTS = 'guests';

    private $conn;

    public ?int $ID_guest;
    public string $firstname;
    public string $lastname;
    public string $email;
    public ?string $address;
    public ?string $city;
    public ?string $country;

    public function __construct(PDO $db_connection)
    {
        $this->conn = $db_connection;
    }

    public function getId(): int|false
    {
        $query = 'SELECT ID_guest FROM ' . $this::TABLE_GUESTS . '
        WHERE firstname = :firstnm AND lastname = :lastnm AND email = :mail';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':firstnm', $this->firstname);
        $stmt->bindParam(':lastnm', $this->lastname);
        $stmt->bindParam(':mail', $this->email);

        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->closeCursor();

        if ($data === false) {
            return false;
        }

        return $data['ID_guest'];
    }

    public function createWithNamesAndEmailAttributes(): int|false
    {
        $id = $this->getId();

        if ($id !== false) {
            return $id;
        }

        $query = 'INSERT INTO ' . $this::TABLE_GUESTS . '
        (firstname, lastname, email) 
        VALUES (:firstnm, :lastnm, :mail)';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':firstnm', $this->firstname);
        $stmt->bindParam(':lastnm', $this->lastname);
        $stmt->bindParam(':mail', $this->email);

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

    public function createWithAllAttributes(): int|false
    {
        $id = $this->getId();

        if ($id !== false) {
            
           
            return $id;
        }

        $query = 'INSERT INTO ' . $this::TABLE_GUESTS . '
        (firstname, lastname, email, address, city, country) 
        VALUES (:firstnm, :lastnm, :mail, :adrs, :cit, :cou)';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':firstnm', $this->firstname);
        $stmt->bindParam(':lastnm', $this->lastname);
        $stmt->bindParam(':mail', $this->email);
        $stmt->bindParam(':adrs', $this->address);
        $stmt->bindParam(':cit', $this->city);
        $stmt->bindParam(':cou', $this->country);

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
