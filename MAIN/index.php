<?php 

    include "session.php";

    //needed for hashing password
    $simm = "youCannotCrackThisHash";

    $connectMessage = "";
    $error = "";


    $query1 = "select c_id, password, default_store from customer where email=:email";
    $query2 = "insert into login values (:cid, :login_time)";
    if ($_POST) {
        
        $db = $_SESSION["db"];
        $user = $_SESSION["db_user"];
        $password = $_SESSION["db_password"];
        
        $orcl = OCILogon($user, $password , $db);
        
        if ($orcl){
          
          $stid = oci_parse($orcl, $query1);
          oci_bind_by_name($stid, ":email", $_POST["email"]);
          oci_execute($stid);
          while (oci_fetch($stid)){
            $cid = oci_result($stid, 'C_ID');
            if (oci_field_is_null($stid, 'DEFAULT_STORE')){
              $defaultStore = "";
            }
            else{
              $defaultStore = oci_result($stid, 'DEFAULT_STORE');
            }
            $password = oci_result($stid, 'PASSWORD');
          break;
          }

          if (password_verify ( $simm.$_POST["password"] , $password )){
            $_SESSION["cid"] = $cid;
            $_SESSION["defaultStore"] = $defaultStore;
            //date_default_timezone_set("UTC");
            $timestamp = date("d-M-Y h:i:s A", time());
            // parse the insert query
            $stid = oci_parse($orcl, $query2);
            //bind the required variables
            oci_bind_by_name($stid, ":cid", $cid);
            oci_bind_by_name($stid, ":login_time", $timestamp);
            // execute query
            oci_execute($stid);
            // redirect to homepage
            header("Location: homepage/main.php");
          }
          else{
            $error = '<br><br><div class="alert alert-warning alert-dismissible fade show" role="alert"><strong> Incorrect Password / User unavailable </strong><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
          }

          OCILogoff($orcl);
        }
        else{
            $connectMessage = "Connection to DB Failed..!!";
            $error = '<br><br><div class="alert alert-warning alert-dismissible fade show" role="alert"><strong>' . $connectMessage . '</strong><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
        }
        
        
    }
    else if($_SESSION["info"]=="justCreated"){
      $error = '<br><br><div class="alert alert-success alert-dismissible fade show" role="alert"><p id="success-message">'."Account created. You can login now ".'</p><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
      $_SESSION["info"]=="";
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        
        <style type="text/css">
            body {
              height: 100%;
            }

            body {
              display: -ms-flexbox;
              display: flex;
              -ms-flex-align: center;
              align-items: center;
              padding-top: 40px;
              padding-bottom: 40px;
              background-color: #f5f5f5;
            }

            .form-signin {
              width: 100%;
              max-width: 330px;
              padding: 15px;
              margin: auto;
            }
            .form-signin .checkbox {
              font-weight: 400;
            }
            .form-signin .form-control {
              position: relative;
              box-sizing: border-box;
              height: auto;
              padding: 10px;
              font-size: 16px;
            }
            .form-signin .form-control:focus {
              z-index: 2;
            }
            .form-signin input[type="email"] {
              margin-bottom: -1px;
              border-bottom-right-radius: 0;
              border-bottom-left-radius: 0;
            }
            .form-signin input[type="password"] {
              margin-bottom: 10px;
              border-top-left-radius: 0;
              border-top-right-radius: 0;
            }
            
            #success-message{
              margin-bottom:1px !important;
            }
        </style>
    <title>Login to B Buy</title>
    <style>
        body {
            margin:0;
            padding:0;
        }
    </style>
    </head>
    <body class="text-center">

        <form class="form-signin" method="post">
        
            <h1 class="h1 mb-5">Welcome to B Buy</h1>
            
            <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
            
            <label for="inputEmail" class="sr-only">Email address</label>
            
            <input type="email" class="form-control" id="inputEmail" name="email" aria-describedby="emailHelp" placeholder="Enter email" required autofocus>

            <label for="inputPassword" class="sr-only">Password</label>
            
            <input type="password" class="form-control" id="inputPassword" placeholder="Password" name="password" required>
            
            <div class="checkbox mb-3">
                
                <label>
                <input type="checkbox" value="remember-me">
                Remember Me
                </label>
            </div>
            <button type="submit" class="btn btn-lg btn-primary btn-block">Sign In</button>
            
            <small class="font-weight-normal">Not on B-Buy? <a href="signup/signup.php">Sign up</a>
            </small>
            <br>
            <br>
            <small class="font-weight-normal"><a href="homepage/main.php">Click Here </a> if you want to buy in-store without login
            </small>
            <div id="error"><?php echo $error; ?></div>
        </form>
        
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
        </script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
        </script>
        
        <script type="text/javascript">
            $("form").submit(function(e){
                e.preventDefault();
                
                $("form").unbind("submit").submit();
            });

        </script>
    </body>
</html>