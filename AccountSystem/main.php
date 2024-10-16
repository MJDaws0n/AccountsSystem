<?php
namespace Net\MJDawson\AccountSystem;
use Exception;

class Accounts{
    public function __construct($conn, $tableName){
        $tableExists = $conn->query("SHOW TABLES LIKE '$tableName'");

        if ($tableExists->num_rows == 0) {
            $this->createAccountsTable($conn, $tableName);
        }
    }
    private function createAccountsTable($conn, $name) {
        $sql = "CREATE TABLE $name (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            session VARCHAR(255) NOT NULL,
            reset_token VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            additional_values TEXT NOT NULL DEFAULT '{}'
        )";

        if ($conn->query($sql) !== TRUE) {
            throw new Exception("Error creating table: " . $conn->error);
            exit();
        }
    }
}

class User{
    private $user;
    private $conn;
    private $tableName;

    public function __construct($conn, $tableName, $username = null, $password = null, $session = null, $id = null){
        $this->conn = $conn;
        if($username !== null && $password !== null){
            $sql = "SELECT * FROM `$tableName` WHERE `username` = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    $newRow = json_decode($row['additional_values'], true);
                    $newRow['id'] = $row['id'];
                    $newRow['username'] = $row['username'];
                    $newRow['password'] = $row['password'];
                    $newRow['session'] = $row['session'];
                    $newRow['reset_token'] = $row['reset_token'];
                    $newRow['created_at'] = $row['created_at'];
                    $this->user = $newRow;
                }
            }
            $stmt->close();
        }
        if($session !== null){
            $sql = "SELECT * FROM `$tableName` WHERE `session` = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $session);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $newRow = json_decode($row['additional_values'], true);
                $newRow['id'] = $row['id'];
                $newRow['username'] = $row['username'];
                $newRow['password'] = $row['password'];
                $newRow['session'] = $row['session'];
                $newRow['reset_token'] = $row['reset_token'];
                $newRow['created_at'] = $row['created_at'];
                $this->user = $newRow;
            }
            $stmt->close();
        }
        if($id !== null){
            $sql = "SELECT * FROM `$tableName` WHERE `id` = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $newRow = json_decode($row['additional_values'], true);
                $newRow['id'] = $row['id'];
                $newRow['username'] = $row['username'];
                $newRow['password'] = $row['password'];
                $newRow['session'] = $row['session'];
                $newRow['reset_token'] = $row['reset_token'];
                $newRow['created_at'] = $row['created_at'];
                $this->user = $newRow;
            }
            $stmt->close();
        }

        $this->tableName = $tableName;
    }
    public function get(){
        return $this->user;
    }
    public function create($username, $password, $additionalValues = []) {
        // Initialize count variable
        $count = 0;
    
        // Check if username is already in use
        $checkSql = "SELECT COUNT(*) FROM `".$this->tableName."` WHERE `username` = ?";
        $checkStmt = $this->conn->prepare($checkSql);
    
        if (!$checkStmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
    
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();
    
        if ($count > 0) {
            return null;
        }
    
        $sql = "INSERT INTO `".$this->tableName."`(`username`, `password`, `session`, `reset_token`, `additional_values`)
                VALUES (?, ?, ?, ?, ?)";
    
        $session = $this->createSession();
        $reset_token = $this->createResetToken();
    
        $stmt = $this->conn->prepare($sql);
    
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
    
        $additionalValues = json_encode($additionalValues);
        $password = $this->hashPassword($password);
        $stmt->bind_param("sssss", $username, $password, $session, $reset_token, $additionalValues);
    
        if (!$stmt->execute()) {
            throw new Exception("Error executing query: " . $stmt->error);
        }
    
        // Check if it's created
        if ($stmt->affected_rows == 1) {
            $this->user = [
                'username' => $username,
                'password' => $password,
                'session' => $session,
                'reset_token' => $reset_token,
                'additional_values' => json_decode($additionalValues, true)
            ];
        }
    
        $stmt->close();
        return $this->user;
    }
    
    private function createSession(){
        return bin2hex(random_bytes(16));
    }
    private function createResetToken(){
        return bin2hex(bin2hex(random_bytes(32)));
    }
    private function hashPassword($password){
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
