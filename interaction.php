<?php
session_start();
include('config/db.php');

if(isset($_POST['action']) && $_POST['action'] != "") {
    
    if($_POST['action'] === "register") {
        
        if(isset($_POST['username']) && $_POST['username'] != "") {
            
            if(isset($_POST['password']) && $_POST['password'] != "") {
                
                $username = (isset($_POST['username']) && trim($_POST['username']) != '')? htmlspecialchars($_POST['username']) : null;
                $password = (isset($_POST['password']) && trim($_POST['password']) != '')? htmlspecialchars($_POST['password']) : null;
                $address = md5(uniqid($username, true));
                
                $query=$db->prepare("SELECT * FROM users WHERE username = :username");
                $query->bindValue(':username', $_POST['username'], PDO::PARAM_STR);
                $query->execute();
                $data=$query->fetchAll();
                
                if (!empty($data) ) {
                    echo "Username already taken.";
                } else { 
                    $query=$db->prepare("INSERT into users (username, password, wallet_address) VALUES (:username, :password, :wallet_address)");
                    $query->bindValue(':username',$username, PDO::PARAM_STR);
                    $query->bindValue(':password',password_hash($password, PASSWORD_ARGON2I), PDO::PARAM_STR);
                    $query->bindValue(':wallet_address',$address, PDO::PARAM_STR);
                    $query->execute();
                    header('Location: /blockchain/wallet/sign_in.php');
                    exit();
                }
                
            } else {
                echo "Missing parameter";
            }
        
        } else {
            echo "Missing parameter"; 
        }
    
    } else {
        
        if($_POST['action'] === "login") {
            
            if(isset($_POST['username']) && isset($_POST['password']) && $_POST['username'] != "" && $_POST['password'] != "") {
                
                $query=$db->prepare("SELECT * FROM users WHERE username=:username");
                $query->bindValue(':username', $_POST['username'], PDO::PARAM_STR);
                $query->execute();
                $data=$query->fetch();
                
                if ($data['username'] === $_POST['username'] && $data['password'] === password_hash($password, PASSWORD_ARGON2I)) {
                    
                    $sold = shell_exec('C:\Python36\python.exe ../count_the_money.py ' . $data['wallet_address']);
                    
                    if($sold === "") {
                        $_SESSION['sold'] = 0;
                        $_SESSION['username'] = $data['username'];
                        $_SESSION['address'] = $data['wallet_address'];
                        header('Location: /blockchain/wallet/transaction/');
                        exit();
                    } else {
                        $_SESSION['sold'] = $sold;
                        $_SESSION['username'] = $data['username'];
                        $_SESSION['address'] = $data['wallet_address'];
                        header('Location: /blockchain/wallet/transaction/');
                        exit();
                    }
                    
                } else {
                    echo "Bad credentials";
                }
            } else {
                echo "Missing parameter";
            }
            
            
        } else {
            echo "Missing parameter";
        }
        
    }
    
} else {
    
    echo "Missing parameter";
}

?>
