<?php
require 'includes/init.php';

//profile redirect function
function redirect_to_profile(){
    header('Location: profile.php');
    exit;
}

//If GET Action and Id parameters are set
if(isset($_GET['action']) && isset($_GET['id'])){
    //check user logged in or not
    if(isset($_SESSION['user_id']) && isset($_SESSION['email'])){
        //if parameter ID === my ID, redirect to profile (same person request)
        if($_GET['id'] == $_SESSION['user_id']){
            redirect_to_profile();
        }
        else{
            //operations with other friends
            $user_id = $_GET['id'];
            $my_id = $_SESSION['user_id'];

            //if action == send request
            if($_GET['action'] == 'send_req'){
                //request already sent
                if($friend_obj->req_already_sent($my_id,$user_id)){
                    redirect_to_profile();
                }
                //if already friends
                elseif ($friend_obj->is_already_friends($my_id,$user_id)){
                    redirect_to_profile();
                }
                //maake a request
                else{
                    $friend_obj->make_pending_friends($my_id, $user_id);
                }
            }
            //if request is a cancel request
            elseif ($_GET['action'] == 'cancel_req' || $_GET['action'] == 'ignore_req'){
                $friend_obj->cancel_req($my_id, $user_id);
            }
            //if request is an accept request
            elseif ($_GET['action'] == 'accept_req'){
                if($friend_obj->is_already_friends($my_id, $user_id)){
                    redirect_to_profile();
                }
                else{
                    $friend_obj->make_friends($my_id, $user_id);
                }
            }
            // if request is an unfriend request
            elseif ($_GET['action'] == 'unfriend_req'){
                $friend_obj->delete_friends($my_id, $user_id);
            }
            else{
                redirect_to_profile();
            }
        }
    }
    else{
        header('Location: logout.php');
        exit;
    }
}