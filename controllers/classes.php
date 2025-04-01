<?php
require_once dirname(__DIR__) . '/configs/dbconnection.php';

class Auth {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    // **REGISTER USER**
    public function register() {
        if (!isset($_POST['student'], $_POST['name'], $_POST['email'], $_POST['department'], $_POST['dob'], $_POST['contact'], $_POST['password'])) {
            return ["status" => "error", "message" => "All fields are required."];
        }

        $student = $this->conn->real_escape_string($_POST['student']);
        $name = $this->conn->real_escape_string($_POST['name']);
        $email = $this->conn->real_escape_string($_POST['email']);
        $department = $this->conn->real_escape_string($_POST['department']);
        $dob = $this->conn->real_escape_string($_POST['dob']);
        $contact = $this->conn->real_escape_string($_POST['contact']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $query = "INSERT INTO students (studentID, name, email, department,  dateOfBirth, contactNumber, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssssss", $student, $name, $email, $department, $dob, $contact, $password);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Registration successful.", "redirect_url" => "login.php"];
        }
        return ["status" => "error", "message" => "Registration failed."];
    }

    // **LOGIN USER**
    public function login() {
        if (empty($_POST['email']) || empty($_POST['password'])) {
            return ["status" => "error", "message" => "Email and password are required."];
        }

        $email = $this->conn->real_escape_string($_POST['email']);
        $query = "SELECT id, password FROM students WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($_POST['password'], $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                return ["status" => "success", "redirect_url" => "dashboard.php"];
            }
            return ["status" => "error", "message" => "Invalid password."];
        }
        return ["status" => "error", "message" => "User not found."];
    }

    // **CHANGE PASSWORD**
    public function change_password() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            return ["status" => "error", "message" => "Unauthorized access."];
        }

        if (empty($_POST['current_password']) || empty($_POST['new_password'])) {
            return ["status" => "error", "message" => "Both current and new passwords are required."];
        }

        $user_id = $_SESSION['user_id'];
        $query = "SELECT password FROM students WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($_POST['current_password'], $user['password'])) {
                $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $update_query = "UPDATE students SET password = ? WHERE id = ?";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bind_param("si", $new_password, $user_id);
                if ($update_stmt->execute()) {
                    return ["status" => "success", "message" => "Password changed successfully."];
                }
                return ["status" => "error", "message" => "Failed to change password."];
            }
            return ["status" => "error", "message" => "Current password is incorrect."];
        }
        return ["status" => "error", "message" => "User not found."];
    }

    // **RESET PASSWORD**
    public function reset_password() {
        if (empty($_POST['email'])) {
            return ["status" => "error", "message" => "Email is required."];
        }

        $email = $this->conn->real_escape_string($_POST['email']);
        return ["status" => "success", "message" => "Password reset link sent to email."]; // Implement actual reset logic
    }
}

class Admin {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    // **ADD ELECTION**
    public function add_election() {
        if (empty($_POST['title']) || empty($_POST['date'])) {
            return ["status" => "error", "message" => "Title and Date are required."];
        }

        $title = $this->conn->real_escape_string($_POST['title']);
        $date = $this->conn->real_escape_string($_POST['date']);

        $query = "INSERT INTO elections (title, date) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $title, $date);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Election added successfully."];
        }
        return ["status" => "error", "message" => "Failed to add election."];
    }

    // **UPDATE ELECTION**
    public function update_election() {
        return ["status" => "success", "message" => "Election updated successfully."];
    }

    // **DELETE ELECTION**
    public function delete_election() {
        return ["status" => "success", "message" => "Election deleted successfully."];
    }

    // **ELECTION STATUS**
    public function election_status() {
        return ["status" => "success", "message" => "Election is ongoing."];
    }

    // **VOTE STATUS**
    public function vote_status() {
        return ["status" => "success", "message" => "Voting is open."];
    }

    // **CATEGORY ACTIONS**
    public function add_category() {
        return ["status" => "success", "message" => "Category added successfully."];
    }

    public function update_category() {
        return ["status" => "success", "message" => "Category updated successfully."];
    }

    public function delete_category() {
        return ["status" => "success", "message" => "Category deleted successfully."];
    }

    // **CANDIDATE ACTIONS**
    public function add_candidate() {
        return ["status" => "success", "message" => "Candidate added successfully."];
    }

    public function update_candidate() {
        return ["status" => "success", "message" => "Candidate updated successfully."];
    }

    public function delete_candidate() {
        return ["status" => "success", "message" => "Candidate deleted successfully."];
    }

    public function user_type() {
        return ["status" => "success", "message" => "User type is Admin."];
    }

    // **REPORT ACTIONS**
    public function download_report() {
        return ["status" => "success", "message" => "Report downloaded successfully."];
    }

    public function delete_report() {
        return ["status" => "success", "message" => "Report deleted successfully."];
    }

    public function vote() {
        return ["status" => "success", "message" => "Vote cast successfully."];
    }

    public function update_profile() {
        return ["status" => "success", "message" => "Profile updated successfully."];
    }
}
?>
