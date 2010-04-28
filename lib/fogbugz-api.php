<?php  
// Created by Arthur D'Antonio III   (http://www.dantonio.info)

Class FogBugz { 
    public $api_url;
    public $api_username;
    public $api_password;
    public $api_token = '';
    public $curl;
    
    public function __construct($api_url, $api_username, $api_password) { 
        // Set Login / Auth Info
        $this->api_url = $api_url;
        $this->api_username = $api_username;
        $this->api_password = $api_password;
        
        // cURL Class for making HTTP Requests
        $this->curl = new curl();
        
        // Login & Store Token
        $this->login();
    }
    
    public function login() { 
        $response = $this->curl->sendRequest($this->buildURL());
        $this->api_token = $this->getTokenFromResponse($response);
        return $response;
    }
    
    public function logout() { 
        return $this->curl->sendRequest($this->buildURL('logout'));
    }
    
    public function resolveCase($case_id, $body=null, $resolved_as_id = null) { 
        if($resolved_as_id == null) { $add = ''; } else { $add = '&ixPersonEditedBy='.$resolved_as_id; }
        if($body != null) { $add .= '&sEvent='.urlencode($body); }
        $this->curl->sendRequest($this->buildURL('resolve').'&ixBug='.$case_id.$add);
    }
    
    public function triggerCommit($case_id, $payload, $edited_by_id = null) { 
        if($edited_by_id == null) { $add = ''; } else { $add = '&ixPersonEditedBy='.$edited_by_id; }        
        $query_string = '&ixBug='.$case_id.'&sFile='.$payload['sFile'].'&sPrev='.$payload['sPrev'].'&sNew='.$payload['sNew'].$add;
        return $this->curl->sendRequest($this->buildURL('newCheckin').$query_string);
    }
    
    public function addCaseReply($case_id, $body, $as_user_id = null) { 
        if($as_user_id == null) { $add = ''; } else { $add = '&ixPersonEditedBy='.$as_user_id; }
        $add .= '&sEvent='.urlencode($body);
        $this->curl->sendRequest($this->buildURL('resolve').'&ixBug='.$case_id.$add);
    }
    
    public function getCases($q, $cols, $max='5') { 
        if($max == null) { $max = ''; } else { $max = '&max='.$max; }
        $add = '&q='.$q.'&cols='.$cols.$max;
        $get_cases = $this->curl->sendRequest($this->buildURL('search').$add);
        return $get_cases;
    }
    
    public function createCase($title, $project, $area, $category, $priority_id, $body, $assignedTo_id = false) { 
        if($assignedTo_id) { 
            $params['ixPersonAssignedTo'] = $assignedTo_id;
        }
        $params['sTitle'] = $title;
        $params['sProject'] = $project;
        $params['sArea'] = $area;
        $params['sCategory'] = $category;
        $params['ixPriority'] = $priority_id;
        $params['sEvent'] = $body;
        
        $str_params = $this->curl->postVar2String($params);        
        $add_case = $this->curl->sendRequest($this->buildURL('new').'&'.$str_params);
        
        return $add_case;
    }
    
    private function buildURL($cmd = 'logon') { 
        if($this->api_token != '') { $and_token = '&token='.$this->api_token; }
        $url = $this->api_url.'?cmd='.$cmd.'&email='.$this->api_username.'&password='.$this->api_password.$and_token;
        return $url;
    }
    
    private function getTokenFromResponse($response) { 
        if(stripos($response, '<response><error') === false) { 
            $get_token = explode('<![CDATA[', $response);
            $get_token = explode(']', $get_token[1]);
            return $get_token[0];
        } else { 
            throw new Exception('Failed Login');
        }
    }
    
    public function __destruct() { 
        $this->logout();
    }
}

?>