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
    
    // Your GitHub Repo URL
    $github_repo_name = 'http://github.com/ArthurD/PHP-FogBugz-API-Wrapper'; // We're setting this because the 'Service Hooks' apply to ALL repos, but in our case we only want to use this hook for 1 Repo...
    // Modify the above as needed, the logic that references it is around line #37...
    
    // Quick function to convert Git Committer Names to FogBugz User IDs.  Modify as needed.
    function getIDfromUsername($username) { 
        if(stripos($username, 'Arthur') !== false) { 
            return 2;
        } else { 
            return null;
        }
    }

    // The Commit Data (GitHub Payload)
    $commit_data = json_decode($_POST['payload']);
    
    // Initiliaze Our FogBugz Class
    $fb = new FogBugz($fogbugz_url, $fogbugz_username, $fogbugz_password);
    
    // Is this a commit to the Repo that we care about?
    if((string) $commit_data->repository->url != $github_repo_url) { 
        die('Error - Invalid Repo!');
    }
    
    // Get Commit Data
    foreach($commit_data->commits as $commit) { 
        
        // Commit Data
        $data['committer_name'] = (string) $commit->author->name;
        $data['committer_email'] = (string) $commit->author->email;
        $data['commit_url'] = (string) $commit->url;
        $data['commit_id'] = (string) $commit->id;
        $data['commit_message'] = (string) $commit->message;
        
        //// The next three blocks compile the Modified/Added/Removed files into a single string that is seperated by line breaks (\n)

        foreach($commit->added as $filename) { 
            $data['commit_files'] .= "[a] ".$filename."\n";     // Files Added
        }
        foreach($commit->modified as $filename) { 
            $data['commit_files'] .= "[m] ".$filename."\n";     // Files Modified
        }
        foreach($commit->removed as $filename) { 
            $data['commit_files'] .= "[d] ".$filename."\n";     // Files Deleted
        }
        
        // Check to see if this commit message references a ticket via:  [#939] OR [#939 resolved]
            // If you want to add additional commit-message functionality, you'd want to do it within this block of code...
        if(stripos($data['commit_message'], '[#') !== false) { 
            
            // Parse out Case ID #
            $get_id = explode('[#',$data['commit_message']);
            $get_id2 = explode(']', $get_id[1]);
            $get_id3 = explode(' ', $get_id[1]);
            if(is_numeric($get_id2[0])) { 
                $case_id = $get_id2[0];
            } else { 
                $case_id = $get_id3[0];
            }

            // We set the 'File' that we committed as the date + _COMMITTER-NAME
                // We do this because FogBugz is designed for more traditional (CVS, SVN, etc) version control systems.  This approach leaves us with a cleaner view of changes made.  Customize as needed!
            $formatted_payload['sFile'] = date('Y-m-d_g:i:s_A').'_'.str_replace(' ','_',$data['committer_name']);
            $formatted_payload['sPrev'] = $data['commit_id'];
            $formatted_payload['sNew'] = $data['commit_id'];
            
            // We now call the 'triggerCommit' method which will add a `commit` to the proper FogBugz Case
            $fb->triggerCommit($case_id, $formatted_payload, getIDfromUsername($data['committer_name']));
            
            /*  Did the Commit Message contain the word 'resolved' within the brackets?  
                     If so, we're going to call the resolveCase() method which resolves the case && adds a reply to the case.  Currently, the reply will contain:
                        Resolved via Commit
                        $commit_message
                        $commit_url
            */
            if(stripos($get_id[1], 'resolved') !== false) { 
                $fb->resolveCase($case_id, "Resolved via Commit\n".$data['commit_message']."\n".$data['commit_url'],getIDfromUsername($data['committer_name']));
            }

        } 
    }
?>