<?php
// Include the settings file where the $default_timezone is set
require '../constants/settings.php';

// Set the default timezone, falling back to UTC if $default_timezone is not set or empty
if (!isset($default_timezone) || empty($default_timezone)) {
    $default_timezone = 'UTC';  // Fallback to UTC if the timezone is not set
}
date_default_timezone_set($default_timezone);

// Get the current date in 'm/d/Y' format
$apply_date = date('m/d/Y');

// Start the session to check user login
session_start();

// Check if the user is logged in
if (isset($_SESSION['logged']) && $_SESSION['logged'] == true) {
    $myid = $_SESSION['myid'];    // User ID
    $myrole = $_SESSION['role'];  // User role
    $opt = $_GET['opt'];          // Job ID

    // Proceed only if the user is an employee
    if ($myrole == "employee") {
        // Include database configuration
        include '../constants/db_config.php';

        try {
            // Establish database connection
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if the employee has already applied for the job
            $stmt = $conn->prepare("SELECT * FROM tbl_job_applications WHERE member_no = :memberno AND job_id = :jobid");
            $stmt->bindParam(':memberno', $myid);
            $stmt->bindParam(':jobid', $opt);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $rec = count($result);

            // If no previous application, allow the user to apply
            if ($rec == 0) {
                // Insert the new job application
                $stmt = $conn->prepare("INSERT INTO tbl_job_applications (member_no, job_id, application_date) VALUES (:memberno, :jobid, :appdate)");
                $stmt->bindParam(':memberno', $myid);
                $stmt->bindParam(':jobid', $opt);
                $stmt->bindParam(':appdate', $apply_date);
                $stmt->execute();

                // Display success message
                echo '<br><div class="alert alert-success">You have successfully applied for this job.</div>';
            } else {
                // If already applied, display a warning message
                echo '<br><div class="alert alert-warning">You have already applied for this job before. You cannot apply again.</div>';
            }

        } catch (PDOException $e) {
            // Handle any database-related errors
            echo '<br><div class="alert alert-danger">Error occurred: ' . $e->getMessage() . '</div>';
        }
    }
}
?>
