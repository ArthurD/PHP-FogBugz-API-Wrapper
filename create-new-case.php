<?php
// Created by Arthur D'Antonio III   (http://www.dantonio.info)
    
    /////////// 
    // Settings & Dependencies
    require('lib/fogbugz-api.php'); // Require the FogBugz API Class
    require('lib/curl.php');        // Require the CURL Class
    
    // Location of a file in which we want CURL to store the Cookie after successful login (MUST be writeable)
    define('COOKIEFILE','lib/cookie.txt');
    
    // Your FogBugz Username, Password, and API URL
    $fogbugz_url = 'http://YOURURL.fogbugz.com/api.asp';
    $fogbugz_username = 'YOUR_USERNAME';
    $fogbugz_password = 'YOUR_PASSWORD';

    /////////// 
    // Add a New FogBugz Case via the API
    /////////// 
    
    // Create FogBugz Object
    $fb = new FogBugz($fogbugz_url, $fogbugz_username, $fogbugz_password);
    
    $title = "Test New Ticket via API"; // New Case Title (string)
    $project = "PAS v3";                // Project (string)
    $area = "Other";                    // Area (string)
    $category = "Task";                 // Category (string)
    $priority_id = 6;                   // Priority ID (integer)
    $assignedTo_id = 2;                 // User ID of the user to Assign this case to (integer)
    
    // The BODY of the new FogBugz Case.  HTML is *not* accepted and all linebreaks (\n) are shown as such when viewed in a browser
    $body = "Just testing out the functionality \n\n hello world!\n'yes!'";
    
    // Send Request to Create the FogBugz Case
    $result = $fb->createCase($title, $project, $area, $category, $priority_id, $body, $assignedTo_id);
    
    /*
        $result will contain the XML reply from the FogBugz API (as a string).
        You may want to add functionality to parse this as needed, etc...
    */
?>