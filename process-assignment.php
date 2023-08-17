<?php
// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get assignment details from form
    $title = $_POST["title"];
    $description = $_POST["description"];
    $due_date = $_POST["due_date"];
    
    // Code to save assignment details to a database
    
    //Send assignment details to students via email
    //$student = array(); 
    
    foreach ($student as $student) {
        $to = $student;
        $subject = "New Assignment: $title";
        $message = "Dear student,\n\nA new assignment has been added:\n\nTitle: $title\nDescription: $description\nDue Date: $due_date\n\nPlease log in to your account to view the details.";
        $headers = "From: your_email@example.com"; 
        
    }
    
    echo "Assignment added and sent to students successfully!";
} else {
    echo "Form submission error.";
}
?>