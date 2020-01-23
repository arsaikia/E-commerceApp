<?php session_start();

    //needed for hashing password
    $simm = "youCannotCrackThisHash";

    // queries used in this page
    $query1 = "insert into customer(first_name, last_name, password, phone, email) values (:first_name, :last_name, :password, :phone, :email)";
    $query2 = "select email from customer where email=:email";

    //other variables
    $error = "";
    $successMessage = "Redirecting you to login page. Please login with your new email/password";

    //code when user clicks sign up
    if ($_POST) {

        $orcl = OCILogon( $_SESSION["db_user"] , $_SESSION["db_password"], $_SESSION["db"]);

        if ($orcl){

            // First check if email already exists
            $query = oci_parse($orcl, $query2);

            oci_bind_by_name($query, ":email", $_POST["email"]);

            oci_execute($query);

            //set this value to true if email exists
            $exists = false;

            while(oci_fetch($query)){
                if (oci_result($query, 'EMAIL') ==$_POST["email"]){

                    //show email exists
                    $error = '<div class="alert alert-danger" role="alert"><h4 class="alert-heading">'.'Email already exists'.'</h4></div>';
                    $exists = true;
                break;
                }
            }
            // if it doesnot exists then insert data in db
            if(!$exists){

                $query = oci_parse($orcl, $query1);

                oci_bind_by_name($query, ":email", $_POST["email"]);
                oci_bind_by_name($query, ":first_name", $_POST["first-name"]);
                oci_bind_by_name($query, ":last_name", $_POST["last-name"]);
                oci_bind_by_name($query, ":phone", $_POST["phone"]);

                $password = $simm.$_POST["password"];
                $password = password_hash($password, PASSWORD_DEFAULT );
                oci_bind_by_name($query, ":password", $password);


                if (oci_execute($query)){
                    $error = '<div class="alert alert-success" role="alert"><h4 class="alert-heading">'.$successMessage.'</h4></div>';
                    $_SESSION["info"] = "justCreated";
                    header("Location: ../index.php");
                }
                else{
                    //show server down
                    $error = '<div class="alert alert-danger" role="alert"><h4 class="alert-heading">'.'Our servers seem to be down at the moment. Try again later'.'</h4></div>';
                }
            }

            //logout of oracle db
            OCILogoff($orcl);
            
        }

    }


?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Fill details</title>

    <style type="text/css">

        .jumbotron{
            background-image:url("signup_1.jpg");
        }
        #site_heading{
            
            margin-left:130px;
        }
        
        body{
            background-color:#F0FFFF;
        }
    </style>

  </head>
  <body>
    <div class="jumbotron jumbotron-fluid">
        <div id = "site_heading" class="container">
            <h1 class="display-5">Welcome to B-Buy</h1>
            <p class="lead">Take some time to fill your details correctly so that we can log you in next time</p>
        </div>
    </div>
    
    <div id="form_container" class="container">
        <div id="user-error"><?php echo $error; ?></div>
        <form class="form-signup" method="post">
            <div class="form-group">
                <label for="first-name">First name</label>
                <input type="name" class="form-control" id="first-name" name = "first-name" placeholder="john">
            </div>
            <div class="form-group">
                <label for="middle-name">Middle name</label>
                <input type="name" class="form-control" id="middle-name" name = "middle-name" placeholder="'o">
            </div>
            <div class="form-group">
                <label for="last-name">Last name</label>
                <input type="name" class="form-control" id="last-name" name = "last-name" placeholder="connor">
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" class="form-control" id="phone" name = "phone" placeholder="0123456789">
            </div>
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" class="form-control" id="email" name = "email" placeholder="name@example.com">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name = "password" placeholder="password">
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm password</label>
                <input type="password" class="form-control" id="confirm-password" placeholder="password">
            </div>
            <button type="submit" class="btn btn-lg btn-primary btn-block">Sign Up</button>
            <br><br>
        </form>
    </div>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  
    <script type="text/javascript">

        function check_valid(value, error, tag){

            if(value=="" || value==undefined || !value.replace(/\s/g, '').length){

                if(tag=="phone" && !Number.isInteger(value)){
                    error+="<p>" + tag + " is empty or not valid</p>";
                    return error;
                }

                error+="<p>" + tag + " is empty or not valid</p>";
            }

            return error;
        }

        function check_password(value1, value2, error){
            if(value1 != value2 | !value1.replace(/\s/g, '').length){
                error+="<p>Passwords don't match</p>";
            }

            return error;
        }

        $("form").submit(function(e){

            e.preventDefault();
            
            let error = "";

            error = check_valid($("#first-name").val(), error, "First name");
            error = check_valid($("#last-name").val(), error, "Last name");
            error = check_valid($("#phone").val(), error, "phone");
            error = check_password($("#password").val(), $("#confirm-password").val(), error);

            if (error==""){

                $("form").unbind("submit").submit();
            }
            else{
                error = '<div class="alert alert-danger" role="alert"><h4 class="alert-heading">Oops..!! You have some errors</h4>' + error+ '</div>';
                $("#user-error").html(error);
            }
            
        });
    </script>
  
  </body>
</html>
