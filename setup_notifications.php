<?php
require_once 'configs/dbconnection.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        notification_id int(11) NOT NULL AUTO_INCREMENT,
        user_id int(11) NOT NULL COMMENT 'Can be studentID or admin ID',
        user_type enum('student','admin') NOT NULL DEFAULT 'student',
        title varchar(100) NOT NULL,
        message text NOT NULL,
        type enum('election','vote','result','system','reminder','candidate') NOT NULL DEFAULT 'system',
        related_election int(11) DEFAULT NULL,
        related_candidate int(11) DEFAULT NULL,
        is_read tinyint(1) NOT NULL DEFAULT 0,
        created_at datetime NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (notification_id),
        KEY related_election (related_election),
        KEY related_candidate (related_candidate),
        CONSTRAINT notifications_ibfk_1 FOREIGN KEY (related_election) REFERENCES elections (electionID) ON DELETE SET NULL,
        CONSTRAINT notifications_ibfk_2 FOREIGN KEY (related_candidate) REFERENCES candidates (candidateID) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    if ($conn->query($sql)) {
        echo "Notifications table created successfully";
    } else {
        throw new Exception("Error creating table: " . $conn->error);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>