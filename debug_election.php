<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log incoming request
error_log('Debug Election: Request Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Debug Election: Request URI: ' . $_SERVER['REQUEST_URI']);
error_log('Debug Election: POST data: ' . print_r($_POST, true));
error_log('Debug Election: GET data: ' . print_r($_GET, true));

// Include database connection
require_once 'configs/dbconnection.php';

echo "<h1>Election Database Debug</h1>";

// Check database connection
if ($conn->ping()) {
    echo "<p style='color:green'>✓ Database connection is active</p>";
} else {
    echo "<p style='color:red'>✗ Database connection failed</p>";
    echo "<p>Error: " . $conn->error . "</p>";
    die();
}

// Get table schema
echo "<h2>Elections Table Schema</h2>";
$schema_result = $conn->query("DESCRIBE elections");
if ($schema_result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $schema_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] !== null ? $row['Default'] : 'NULL') . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>Failed to get table schema: " . $conn->error . "</p>";
}

// Get sample elections
echo "<h2>Sample Elections</h2>";
$sample_result = $conn->query("SELECT * FROM elections LIMIT 5");
if ($sample_result && $sample_result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    
    // Table headers
    $fields = $sample_result->fetch_fields();
    echo "<tr>";
    foreach ($fields as $field) {
        echo "<th>" . $field->name . "</th>";
    }
    echo "</tr>";
    
    // Reset result pointer
    $sample_result->data_seek(0);
    
    // Table data
    while ($row = $sample_result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>No elections found or query failed: " . $conn->error . "</p>";
}

// Test update query
if (isset($_GET['test_update']) && isset($_GET['id'])) {
    $test_id = (int)$_GET['id'];
    
    echo "<h2>Testing Update Query for Election ID: $test_id</h2>";
    
    // Get current election data
    $stmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
    $stmt->bind_param('i', $test_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "<p style='color:red'>Election not found with ID: $test_id</p>";
    } else {
        $election = $result->fetch_assoc();
        
        echo "<p>Current data:</p>";
        echo "<pre>" . print_r($election, true) . "</pre>";
        
        // Prepare test update
        try {
            // No actual changes, just testing the query execution
            $name = $election['name'];
            $startDate = $election['startDate'];
            $endDate = $election['endDate'];
            $status = $election['status'];
            $visibility = $election['visibility'] ?? 'Public';
            
            $sql = "UPDATE elections SET 
                name = ?, 
                startDate = ?, 
                endDate = ?, 
                status = ?, 
                visibility = ? 
                WHERE electionID = ?";
            
            echo "<p>SQL Query: " . htmlspecialchars($sql) . "</p>";
            
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            echo "<p>Binding parameters...</p>";
            
            $stmt->bind_param('sssssi', 
                $name,
                $startDate,
                $endDate,
                $status,
                $visibility,
                $test_id
            );
            
            echo "<p>Executing query...</p>";
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            echo "<p style='color:green'>Update successful. Affected rows: " . $stmt->affected_rows . "</p>";
            $stmt->close();
            
        } catch (Exception $e) {
            echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
        }
    }
}

echo "<p><a href='debug_election.php?test_update=1&id=1'>Test update for Election ID 1</a></p>";
echo "<p><a href='election.php'>Back to Elections</a></p>";

$conn->close();
?> 