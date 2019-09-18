<?php 
session_start();

$hostname = "localhost";
$username = "root";
$password = "";
$database = "dbname";

$con = new mysqli($hostname, $username, $password, $database);

// Check the connection for errors
if ($con->connect_errno) {
    echo "Failed to connect to MySQL:" . $con->connect_errno;
} 
error_reporting(1);

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function display_errors($errors){
    foreach ($errors as $error) {
        return $error;
    }
}

//USER SIGN UP
$password1 = $password2 = $signup_report = $last_name = $last_email = "";

if (isset($_POST['signup'])) {

    $fullname = $con->real_escape_string(test_input($_POST['name'])); 
    if (!preg_match("/^[a-zA-Z ]*$/", $fullname)) {
        array_push($errors, "Sorry!! Name can only contain letters and white space");
    }

    $email = $con->real_escape_string(test_input($_POST['email']));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        array_push($errors, "Invalid email format");
    }

    $password1 = $con->real_escape_string(test_input($_POST['password']));
    $password2 = $con->real_escape_string(test_input($_POST['password_repeat']));
    if ($password1 !== $password2){
        array_push($errors, "Password mismatch, please try again.");
    }

    // Ensure a user does not register twice
    $email_exist = "SELECT * FROM `users` WHERE `email` = '$email'";
    if (mysqli_num_rows($con->query($email_exist)) != 0) {
        array_push($errors, "This email is associated with an existing user!!<br> <a href='#'>Forgot Your Password?</a>");
    }

    if (count($errors) > 0){
        $signup_report = display_errors($errors);
        $last_name = $fullname;
        $last_email = $email;
    }
    else{
        $password = md5($password1);
        $display_name = substr($fullname, 0, 7);
        $sql = "INSERT INTO users (username, display_name, email, userpass, reg_date) VALUE ('$fullname', '$display_name', '$email', '$password', NOW())";
        if ($con->query($sql) === TRUE){
            $signup_report = 'Your Sign up was Successful. Please <a href="login.php">login</a>';
        }
    }
}

// USER LOGIN
$password = $report = '';

if (isset($_POST['login'])){

    $email = $con->real_escape_string(test_input($_POST['email']));
    $password1 = $con->real_escape_string(test_input($_POST['password']));

    $password = md5($password1);
    $sql = "SELECT * FROM `users` WHERE `email` = '$email' AND `userpass` = '$password'";
    $query = $con->query($sql);
    $result = mysqli_num_rows($query);
    $row = $query->fetch_assoc();

    if ($result == 1){
        $_SESSION['user_id'] = $row['id'];
        header("location: dashboard.php");
    }
    else{
        $rep = 'Wrong username/password combination';
    }
}

function userAuth(){
    if (!isset($_SESSION['user_id'])){
        header("location: ../login.php");
    }
    
}

function logOut() {
    if (isset($_POST['logout'])) {
        header("location: ../login.php");
    }
}

?>