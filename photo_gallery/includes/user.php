<?php
/**
 * Created by PhpStorm.
 * User: can you dig it
 * Date: 1/12/14
 * Time: 1:21 PM
 */
require_once(LIB_PATH.DS.'database.php');
class User extends DatabaseObject  {
    protected static $table_name= "users";
    protected static $db_fields = array('id', 'username', 'password', 'first_name', 'last_name');
    public  $username;
    public  $id ;
    public  $password;
    public $first_name;
    public $last_name;

    // common database methods
    public  static  function  authenticate($username="",$password=""){
        global $database;
        $username = $database->escape_value($username);
        $password = $database->escape_value($password);
        $sql = "SELECT * FROM users ";
        $sql .= "WHERE username = '{$username}' ";
        $sql .= "AND password = '{$password}' ";
        $sql .= "LIMIT 1";
        $result_array = self::find_by_sql($sql);
        return !empty($result_array) ? array_shift($result_array) : false;

    }
    public  function  full_name(){
        if(isset($this->first_name)&&($this->last_name)){
            return $this->first_name." ".$this->last_name;
        }else {
            return "";
        }
    }

    public static function find_all (){

    return self::find_by_sql("SELECT* FROM ".self::$table_name);
}
    public static function  find_by_id($id=0){
        global $database;
        $result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE id={$id} LIMIT 1");

        return !empty($result_array)? array_shift($result_array):false;
    }
    public  static  function  find_by_sql($sql=""){
        global $database ;
        $result_set = $database->query($sql);
        //$found = $database->fetch_array($result_set);
       $object_array = array();
        while($row = $database->fetch_array($result_set)){
            $object_array[]=self::instantiate($row);
        }
        return $object_array;
    }

    private  static function instantiate($record){
       //this is the long form approach to setting but his
        //could get very long after a while. below is the short.
        $object = new self;
//        $object->first_name     = $record['first_name'];
//        $object->last_name      = $record['last_name'];
//        $object->username       = $record['username'];
//        $object->id             = $record['id'];
//        $object->password       = $record['password'];
        //this is the short form approach
        // it is more dynamic
        foreach($record as $attribute=>$value){
            if($object ->has_attributte($attribute)){
                $object ->$attribute=$value;
            }
        }
        return $object;

        return $object;
    }
    private function has_attributte($attribute){
        //get_object_vars returns an associative array with all attributes including the private ones
        //as the keys and their current values as the value.
        $object_vars = get_object_vars($this);
        // we don't care about the value we just want to know they exist and return true or false.
        return array_key_exists($attribute,$object_vars);
    }
    protected function attributes() {
        // return an array of attribute names and their values
        $attributes = array();
        foreach(self::$db_fields as $field) {
            if(property_exists($this, $field)) {
                $attributes[$field] = $this->$field;
            }
        }
        return $attributes;
    }
    protected function sanitized_attributes(){
        global $database ;
        $clean_attributes = array();
        // sanitize the values before submitting
        // Note: this does not alter the value of the attribute
        foreach($this->attributes()as $key=>$value){
            $clean_attributes[$key]=$database->escape_value($value);
        }
        return $clean_attributes;
    }
    public  function save(){
        // determine if needs update or create by checking for an id, if it has one it needs update
        // if not it needs create
        return isset($this->id)? $this->update() : $this->create();
    }
    public function create(){
    global $database;
    $sql = "INSERT INTO users(";
    $sql .= "username, password, first_name, last_name";
    $sql .= ")VALUES ('";
    $sql .= $database->escape_value($this->username)."', '";
    $sql .= $database->escape_value($this->password)."', '";
    $sql .= $database->escape_value($this->first_name)."', '";
    $sql .= $database->escape_value($this->last_name)."')";
    if ($database->query($sql)){
        $this->id = $database->insert_id();
        return true;
    }else{
        return false;
    }
    }
    public function update() {
        global $database;
        // Don't forget your SQL syntax and good habits:
        // - UPDATE table SET key='value', key='value' WHERE condition
        // - single-quotes around all values
        // - escape all values to prevent SQL injection
        $sql = "UPDATE ".self::$table_name." SET ";
        $sql .= "username='". $database->escape_value($this->username) ."', ";
        $sql .= "password='". $database->escape_value($this->password) ."', ";
        $sql .= "first_name='". $database->escape_value($this->first_name) ."', ";
        $sql .= "last_name='". $database->escape_value($this->last_name) ."' ";
        $sql .= "WHERE id=". $database->escape_value($this->id);
        $database->query($sql);
        return ($database->affected_rows() == 1) ? true : false;
    }

    public function delete() {
        global $database;
        // Don't forget your SQL syntax and good habits:
        // - DELETE FROM table WHERE condition LIMIT 1
        // - escape all values to prevent SQL injection
        // - use LIMIT 1
        $sql = "DELETE FROM ".self::$table_name;
        $sql .= " WHERE id=". $database->escape_value($this->id);
        $sql .= " LIMIT 1";
        $database->query($sql);
        return ($database->affected_rows() == 1) ? true : false;

        // NB: After deleting, the instance of User still
        // exists, even though the database entry does not.
        // This can be useful, as in:
        //   echo $user->first_name . " was deleted";
        // but, for example, we can't call $user->update()
        // after calling $user->delete().
    }
}