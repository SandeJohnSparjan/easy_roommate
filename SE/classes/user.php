<?php

class User{
    protected $db;
    protected $user_name;
    protected $user_email;
    protected $user_pass;
    protected $has_pass;

    function __construct($db_connection)
    {
        $this->db = $db_connection;
    }

    // signUp User
    function signUp($username, $email, $password){
        try {
            $this->user_name = trim($username);
            $this->user_email = trim($email);
            $this->user_pass = trim($password);
            if (!empty($this->user_name) && !empty($this->user_email) && !empty($this->user_pass)) {
                if (filter_var($this->user_email, FILTER_VALIDATE_EMAIL)) {
                    $check_email = $this->db->prepare("SELECT * FROM users WHERE user_email = ?");
                    $check_email->execute([$this->user_email]);

                    if ($check_email->rowCount() > 0) {
                        return ['errorMessage' => 'This email address is already registered!! Try another one!'];
                    } else {

                        $user_image = rand(1, 12);

                        $this->hash_pass = password_hash($this->user_pass, PASSWORD_DEFAULT);
                        $sql = "INSERT INTO users (username, user_email, user_password, user_image) VALUES (:username, :user_email, :user_pass, :user_image)";

                        $register_stmt = $this->db->prepare($sql);
                        $register_stmt->bindValue(':username', htmlspecialchars($this->user_name), PDO::PARAM_STR);
                        $register_stmt->bindValue(':user_email', $this->user_email, PDO::PARAM_STR);
                        $register_stmt->bindValue(':user_pass', $this->hash_pass, PDO::PARAM_STR);

                        $register_stmt->bindValue(':user_image', $user_image . '.png', PDO::PARAM_STR);

                        $register_stmt->execute();
                        return ['successMessage' => 'You have signed up successfully.'];
                    }
                } else {
                    return ['errorMessage' => 'Invalid email address!'];

                }
            } else {
                return ['errorMessage' => 'Please fill in all required fields.'];
            }
        }
        catch ( PDOException $errorMsg){
            die($errorMsg->getMessage());

            }
        }

    //login
    function login($email, $password){
        try{
            $this->user_email = trim($email);
            $this->user_pass = trim($password);

            $find_email = $this->db->prepare("SELECT * FROM users WHERE user_email = ?");
            $find_email->execute([$this->user_email]);

            if($find_email -> rowCount() ===1){
                $row = $find_email-> fetch(PDO::FETCH_ASSOC);

                $match_pass = password_verify($this->user_pass, $row['user_password']);
                if($match_pass){
                    $_SESSION = [
                        'user_id' => $row['id'],
                        'email' => $row['user_email']
                    ];
                    header('Location: profile.php');
                }
                else{
                    return ['errorMessage' => 'Invalid password'];
                }
            }
            else{
                return ['errorMessage' => 'Invalid email address!'];
            }
        }
        catch (PDOException $errorMsg){
            die($errorMsg->getMessage());
        }
    }

    //find user by ID
    function find_user_by_id($id){
        try{
            $find_user = $this->db->prepare("SELECT * FROM users WHERE id=?");
            $find_user->execute([$id]);
            if($find_user->rowCount() === 1){
                return $find_user->fetch(PDO::FETCH_OBJ);
            }
            else{
                return false;
            }
        }
        catch(PDOException $errMsg){
            die($errMsg->getMessage());
        }
    }

    //Fetch all users
    function all_users($id){
        try{
            $get_users = $this->db->prepare("SELECT id,username,user_image FROM users WHERE id != ?");
            $get_users->execute([$id]);
            if($get_users->rowCount() >0){
                return $get_users->fetchAll(PDO::FETCH_OBJ);
            }
            else{
                return false;
            }
        }
        catch( PDOException $errMsg){
            die($errMsg->getMessage());
        }
    }
}