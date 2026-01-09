<?php
class DB {
    private $host = 'localhost';
    private $dbname = 'cicsinvsystem';
    private $username = 'root';
    private $password = '';
    private $db;
      
    public function __construct(){
        if(!isset($this->db)){
            // Connect to the database
            $conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);
            if($conn->connect_error){
                die("Failed to connect with MySQL: " . $conn->connect_error);
            }else{
                $this->db = $conn;
            }
        }
    }
 
    public function is_table_empty() {
        $result = $this->db->query("SELECT id FROM oauth_tokens WHERE provider = 'google'");
        if($result->num_rows) {
            return false;
        }
    
        return true;
    }

    public function get_access_token() {
        $sql = $this->db->query("SELECT provider_value FROM oauth_tokens WHERE provider='google'");
        $result = $sql->fetch_assoc();
        return json_decode($result['provider_value']);
    }
    
    public function get_refresh_token() {
        $result = $this->get_access_token();
        return $result->refresh_token;
    }
    
    public function update_access_token($token) {
        if($this->is_table_empty()) {
            $sql = sprintf("INSERT INTO oauth_tokens(provider, provider_value) VALUES('%s', '%s')", 'google', $this->db->real_escape_string($token));
            $this->db->query($sql);
        } else {
            $sql = sprintf("UPDATE oauth_tokens SET provider_value = '%s' WHERE provider = '%s'", $this->db->real_escape_string($token), 'google');
            $this->db->query($sql);
        }
    }
}
