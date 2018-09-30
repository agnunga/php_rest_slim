<?php

class DbOperation
{
    private $con;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        $db = new DbConnect();
        $this->con = $db->connect();
    }

    //Method to create a new user
    function registerUser($name, $email, $pass, $gender)
    {
        if (!$this->isUserExist($email)) {
            $password = md5($pass);
//            echo "pass: " . $password . " user : " . $name . " email : " . $email . " gender : " . $gender;
            $stmt = $this->con->prepare("INSERT INTO users (name, email, password, gender) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $name, $email, $password, $gender);
            if ($stmt->execute())
                return USER_CREATED;
            return USER_CREATION_FAILED;
        }
        return USER_EXIST;
    }

    //Method for user login
    function userLogin($email, $pass)
    {
        $password = md5($pass);
        $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    //Method to send a message to another user
    function sendMessage($from, $to, $title, $message)
    {
        $stmt = $this->con->prepare("INSERT INTO messages (from_users_id, to_users_id, title, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $from, $to, $title, $message);
        if ($stmt->execute())
            return true;
        return false;
    }

    //Method to update profile of user
    function updateProfile($id, $name, $email, $pass, $gender)
    {
        $password = md5($pass);
        $stmt = $this->con->prepare("UPDATE users SET name = ?, email = ?, password = ?, gender = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $email, $password, $gender, $id);
        if ($stmt->execute())
            return true;
        return false;
    }

    //Method to get messages of a particular user
    function getMessages($userid)
    {
        $stmt = $this->con->prepare("SELECT m.id, (SELECT u.name FROM users u WHERE u.id = m.from_users_id) as `from`, (SELECT u.name FROM users u WHERE u.id = m.to_users_id) as `to`, m.title, m.message, m.sentat FROM messages m WHERE m.to_users_id = ? ORDER BY m.sentat DESC;");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $stmt->bind_result($id, $from, $to, $title, $message, $sent);

        $messages = array();

        while ($stmt->fetch()) {
            $temp = array();

            $temp['id'] = $id;
            $temp['from'] = $from;
            $temp['to'] = $to;
            $temp['title'] = $title;
            $temp['message'] = $message;
            $temp['sent'] = $sent;

            array_push($messages, $temp);
        }

        return $messages;
    }

    //Method to get user by email
    function getUserByEmail($email)
    {
        $stmt = $this->con->prepare("SELECT id, name, email, gender FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($id, $name, $email, $gender);
        $stmt->fetch();
        $user = array();
        $user['id'] = $id;
        $user['name'] = $name;
        $user['email'] = $email;
        $user['gender'] = $gender;
        return $user;
    }

    //Method to get all users
    function getAllUsers(){
        $stmt = $this->con->prepare("SELECT id, name, email, gender FROM users");
        $stmt->execute();
        $stmt->bind_result($id, $name, $email, $gender);
        $users = array();
        while($stmt->fetch()){
            $temp = array();
            $temp['id'] = $id;
            $temp['name'] = $name;
            $temp['email'] = $email;
            $temp['gender'] = $gender;
            array_push($users, $temp);
        }
        return $users;
    }

    //Method to check if email already exist
    function isUserExist($email)
    {
        $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    //Method to get all accounts
    function getAllAccounts(){
        $stmt = $this->con->prepare("SELECT id, title, first_name, last_name, email, password, role_id, status FROM account");
        $stmt->execute();
        $stmt->bind_result($id, $title, $first_name, $last_name, $email, $password, $role_id, $status);
        $accounts = array();
        while($stmt->fetch()){
            $temp = array();
            $temp['id'] = $id;
            $temp['title'] = $title;
            $temp['first_name'] = $first_name;
            $temp['last_name'] = $last_name;
            $temp['email'] = $email;
            $temp['password'] = $password;
            $temp['role_id'] = $role_id;
            $temp['status'] = $status;

            array_push($accounts, $temp);
        }
        return $accounts;
    }

    public function getAllTimesheets()
    {
        $stmt = $this->con->prepare("SELECT t.id, t.activity_id, t.comments, t.date, t.date_submitted, t.employee_id, t.time_from, t.time_to FROM timesheet t");
        $stmt->execute();
        $stmt->bind_result($id, $activity_id, $comments, $date, $date_submitted, $employee_id, $time_from, $time_to);
        $timesheets = array();
        while($stmt->fetch()){
            $temp = array();
            $temp['id'] = $id;
            $temp['activity_id'] = $activity_id;
            $temp['comments'] = $comments;
            $temp['date'] = $date;
            $temp['date_submitted'] = $date_submitted;
            $temp['employee_id'] = $employee_id;
            $temp['time_from'] = $time_from;
            $temp['time_to'] = $time_to;

            array_push($timesheets, $temp);
        }
        return $timesheets;
    }

    public function getAllActivities()
    {
        $stmt = $this->con->prepare("SELECT a.id, a.code, a.project_id, a.other_details, a.start_date, a.end_date FROM activity a");
        $stmt->execute();
        $stmt->bind_result( $id, $code, $project_id, $other_details, $start_date, $end_date);
        $activities = array();
        while($stmt->fetch()){
            $temp = array();
            $temp['id'] = $id;
            $temp['code'] = $code;
            $temp['project_id'] = $project_id;
            $temp['other_details'] = $other_details;
            $temp['start_date'] = $start_date;
            $temp['end_date'] = $end_date;

            array_push($activities, $temp);
        }
        return $activities;
    }

    public function getAllCostCentres()
    {
        $stmt = $this->con->prepare("SELECT c.id, c.name, c.order_details, c.order_details, c.description, c.date_created, c.date_modified FROM cost_center c");
        $stmt->execute();
        $stmt->bind_result( $id, $name, $order_details, $order_details, $description, $date_created, $date_modified);
        $cost_centres = array();
        while($stmt->fetch()){
            $temp = array();
            $temp['id'] = $id;
            $temp['name'] = $name;
            $temp['order_details'] = $order_details;
            $temp['description'] = $description;
            $temp['description'] = $description;
            $temp['date_created'] = $date_created;
            $temp['date_modified'] = $date_modified;

            array_push($cost_centres, $temp);
        }
        return $cost_centres;
    }

    public function getAllProjects()
    {
        $stmt = $this->con->prepare("SELECT p.id, p.name, p.start_date, p.end_date, p.other_details, p.location, p.manager_id, p.project_code  FROM project p");
        $stmt->execute();
        $stmt->bind_result($id, $name, $start_date, $end_date, $other_details, $location, $manager_id, $project_code );
        $projects = array();
        while($stmt->fetch()){
            $temp = array();
            $temp['id'] = $id;
            $temp['name'] = $name;
            $temp['start_date'] = $start_date;
            $temp['description'] = $end_date;
            $temp['other_details'] = $other_details;
            $temp['location'] = $location;
            $temp['manager_id'] = $manager_id;
            $temp['project_code'] = $project_code;

            array_push($projects, $temp);
        }
        return $projects;
    }

    public function getAllRoles()
    {
        $stmt = $this->con->prepare("SELECT r.id, r.role FROM role r");
        $stmt->execute();
        $stmt->bind_result($id, $role );
        $roles = array();
        while($stmt->fetch()){
            $temp = array();
            $temp['id'] = $id;
            $temp['role'] = $role;

            array_push($roles, $temp);
        }
        return $roles;
    }


}