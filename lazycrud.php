<?php

$servername = "localhost"; // Database server name
$username = "root"; // Database username
$password = ""; // Database password
$dbname = "dbname"; // Database name

$conn = new mysqli($servername, $username, $password, $dbname); // Create a new database connection

if ($conn->connect_error) { // Check if there was an error connecting to the database
    die("Connection failed: " . $conn->connect_error); // Print an error message and terminate the script
}

$action = $_POST['action'] ?? ''; // Get the value of the 'action' parameter from the POST request

define('TABLE_NAME', 'employee'); // Define a constant variable for the table name in the database

// Function to insert data into the database
function insertData($data, $conn, $successMessage) {
    $columns = implode(',', array_keys($data)); // Get the column names
    $values = "'" . implode("','", array_map([$conn, 'real_escape_string'], $data)) . "'"; // Escape and concatenate the values

    // Exclude 'action' from columns and values
    unset($data['action']);
    $columns = implode(',', array_keys($data));
    $values = "'" . implode("','", array_map([$conn, 'real_escape_string'], $data)) . "'";

    $sql = "INSERT INTO " . TABLE_NAME . " ($columns) VALUES ($values)"; // Build the SQL query

    if ($conn->query($sql) === TRUE) { // Execute the query and check if it was successful
        $response = ['message' => $successMessage]; // Create a success response
    } else {
        $response = ['error' => "Error: " . $sql . "<br>" . $conn->error]; // Create an error response
    }

    return json_encode($response); // Return the response as JSON
}

// Function to select data from the database
function selectData($conn) {
    $sql = "SELECT * FROM " . TABLE_NAME; // Build the SQL query
    $result = $conn->query($sql); // Execute the query

    if ($result && $result->num_rows > 0) { // Check if there are rows returned from the query
        $data = $result->fetch_all(MYSQLI_ASSOC); // Fetch all rows as an associative array
        $response = $data; // Set the response as the fetched data
    } else {
        $response = ['error' => "Error: " . $sql . "<br>" . $conn->error]; // Create an error response
    }

    return json_encode($response); // Return the response as JSON
}

// Function to update data in the database
function updateData($data, $conn, $id) {
    $updates = [];
    foreach ($data as $column => $value) {
        if ($column !== 'action' && $column !== 'id') { // Exclude 'action' and 'id' from updates
            $updates[] = "$column = '" . $conn->real_escape_string($value) . "'";
        }
    }

    $sql = "UPDATE " . TABLE_NAME . " SET " . implode(', ', $updates) . " WHERE id = " . intval($id); // Build the SQL query

    if ($conn->query($sql) === TRUE) { // Execute the query and check if it was successful
        $response = ['message' => "Record updated successfully"]; // Create a success response
    } else {
        $response = ['error' => "Error: " . $sql . "<br>" . $conn->error]; // Create an error response
    }

    return json_encode($response); // Return the response as JSON
}

// Function to delete data from the database
function deleteData($id, $conn) {
    $sql = "DELETE FROM " . TABLE_NAME . " WHERE id = " . intval($id); // Build the SQL query

    if ($conn->query($sql) === TRUE) { // Execute the query and check if it was successful
        $response = ['message' => "Record deleted successfully"]; // Create a success response
    } else {
        $response = ['error' => "Error: " . $sql . "<br>" . $conn->error]; // Create an error response
    }

    return json_encode($response); // Return the response as JSON
}

// Check if the action is allowed
if ($action === 'insert') {
    $data = $_POST; // Get the data from the POST request
    $successMessage = "New record created successfully";

    echo insertData($data, $conn, $successMessage); // Call the insertData function and echo the response
} elseif ($action === 'select') {
    echo selectData($conn); // Call the selectData function and echo the response
} elseif ($action === 'update') {
    $data = $_POST; // Get the data from the POST request
    $id = $_POST['id'] ?? 0; // Get the ID from the POST request or set it to 0 if not provided
    
    echo updateData($data, $conn, $id); // Call the updateData function and echo the response
} elseif ($action === 'delete') {
    $id = $_POST['id'] ?? 0; // Get the ID from the POST request or set it to 0 if not provided

    echo deleteData($id, $conn); // Call the deleteData function and echo the response
} else {
    echo json_encode(['error' => "Invalid action specified"]); // Echo an error response for invalid action
}

$conn->close(); // Close the database connection

?>
