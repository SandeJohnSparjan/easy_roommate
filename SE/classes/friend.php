<?php

class Friend{

    protected $db;

    public function __construct($db_connection){
        $this->db= $db_connection;
    }

    //friends already
    function is_already_friends($my_id, $friend_id){
        try{
            $sql = "SELECT * FROM friends WHERE (user_one = :my_id && user_two = :friend_id) OR (user_one = :friend_id && user_two = :my_id)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':my_id',$my_id, PDO::PARAM_INT);
            $stmt->bindValue(':friend_id',$friend_id, PDO::PARAM_INT);
            $stmt->execute();

            if($stmt->rowCount() === 1){
                return true;
            }
            else{
                return false;
            }
        }
        catch (PDOException $errMsg){
            die($errMsg->getMessage());
        }
    }

    //request sender
    public function req_sender($my_id, $user_id){
        try{
            $sql = "SELECT * FROM requests WHERE sender = ? AND receiver=?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$my_id, $user_id]);

            if($stmt->rowCount() === 1){
                return true;
            }
            else{
                return false;
            }
        }
        catch (PDOException $errMsg){
            die($errMsg->getMessage());
        }
    }

    //request receiver
    public function req_receiver($my_id, $user_id){
        try{
            $sql = "SELECT * FROM requests WHERE sender = ? AND receiver=?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id, $my_id]);

            if($stmt->rowCount() === 1){
                return true;
            }
            else{
                return false;
            }

        }
        catch(PDOException $errMsg){
            die($errMsg->getMessage());
        }
    }

    //checking if request has been sent already
    public function req_already_sent($my_id, $user_id){
        try{
            $sql = "SELECT * FROM requests WHERE (sender = :my_id AND receiver = :frnd_id) OR (sender =:frnd_id AND receiver =:my_id)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':my_id',$my_id, PDO::PARAM_INT);
            $stmt->bindValue(':frnd_id',$user_id, PDO::PARAM_INT);
            $stmt->execute();

            if($stmt->rowCount() === 1){
                return true;
            }
            else{
                return false;
            }
        }
        catch(PDOException $errMsg){
            die($errMsg->getMessage());
        }
    }

    //make pending friends (send frnd requests)
    public function make_pending_friends($my_id, $user_id){
        try{
            $sql = "INSERT INTO requests (sender, receiver) VALUES(?,?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$my_id, $user_id]);
            header('Location: user_profile.php?id='.$user_id);
            exit;
        }
        catch(PDOException $errMsg){
            die($errMsg->getMessage());
        }
    }

    //cancel friend request
    public function cancel_req($my_id, $user_id){
        try{
            $sql = "DELETE FROM requests WHERE (sender = :my_id AND receiver = :frnd_id) OR (sender =:frnd_id AND receiver =:my_id)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':my_id', $my_id, PDO::PARAM_INT);
            $stmt->bindValue(':frnd_id',$user_id,PDO::PARAM_INT);
            $stmt->execute();
            header('Location: user_profile.php?id='.$user_id);
            exit;
        }
        catch(PDOException $errMsg){
            die($errMsg->getMessage());
        }
    }

    //make friends
    public function make_friends($my_id, $user_id){
        try{
            $delete_pending_friends = "DELETE FROM requests WHERE (sender = :my_id AND receiver = :frnd_id) OR (sender =:frnd_id AND receiver =:my_id)";
            $delete_stmt =$this->db->prepare($delete_pending_friends);
            $delete_stmt->bindValue(':my_id',$my_id, PDO::PARAM_INT);
            $delete_stmt->bindValue(':frnd_id', $user_id, PDO::PARAM_INT);
            $delete_stmt->execute();
            if($delete_stmt->execute()){
                $sql = "INSERT INTO friends (user_one,user_two) VALUES (?,?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$my_id,$user_id]);
                header('Location: user_profile.php?id='.$user_id);
                exit;
            }
        }
        catch(PDOException $errMsg){
            die($errMsg->getMessage());
        }
    }

    //delete friends
    public function delete_friends($my_id, $user_id){
        try{
            $delete_friends = "DELETE FROM friends WHERE (user_one= :my_id AND user_two = :frnd_id) OR (user_one= :frnd_id AND user_two = :my_id)";
            $delete_stmt = $this->db->prepare($delete_friends);
            $delete_stmt->bindValue(':my_id',$my_id,PDO::PARAM_INT);
            $delete_stmt->bindValue(':frnd_id',$user_id,PDO::PARAM_INT);
            $delete_stmt->execute();
            header('Location: user_profile.php?id='.$user_id);
            exit;
        }
        catch(PDOException $errMsg){
            die($errMsg->getMessage());
        }
    }

    //request Notifications
    public function req_notification($my_id, $send_data){
        try{
            $sql ="SELECT sender, username, user_image FROM requests JOIN users ON requests.sender = users.id WHERE receiver = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$my_id]);
            if($send_data){
                return $stmt->fetchAll(PDO::FETCH_OBJ);
            }
            else{
                return $stmt->rowCount();
            }
        }
        catch (PDOException $errMsg){
            die($errMsg->getMessage());
        }
    }

    //all users
    public function get_all_friends($my_id, $send_data){
        try{
            $sql = "SELECT * FROM friends WHERE user_one = :my_id OR user_two = :my_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':my_id',$my_id, PDO::PARAM_INT);
            $stmt->execute();

                if($send_data){
                    $return_data = [];
                    $all_users = $stmt->fetchAll(PDO::FETCH_OBJ);

                    foreach($all_users as $row){
                        if($row->user_one == $my_id){
                            $get_user = "SELECT id, username, user_image FROM users WHERE id = ?";
                            $get_user_stmt = $this->db->prepare($get_user);
                            $get_user_stmt->execute([$row->user_two]);
                            array_push($return_data, $get_user_stmt->fetch(PDO::FETCH_OBJ));
                        }else{
                            $get_user = "SELECT id, username, user_image FROM users WHERE id = ?";
                            $get_user_stmt = $this->db->prepare($get_user);
                            $get_user_stmt->execute([$row->user_one]);
                            array_push($return_data, $get_user_stmt->fetch(PDO::FETCH_OBJ));
                        }
                }
                    return $return_data;
        }
                else{
                return $stmt->rowCount();
                }
        }
        catch (PDOException $errMsg){
            die($errMsg->getMessage());
        }
    }
}