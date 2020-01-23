<?php session_start();

    $cid = $_SESSION["cid"];
    $address_ID = "";
    
    if ($_POST) {
        echo $_POST["apartment"];
        echo $_POST["street"];
        echo $_POST["city"];
        echo $_POST["state"];
        echo $_POST["zip"];
    }


    // queries used in this page
    $query_checkZip = "select zip from Zip_code where zip = :zip";
    $query_insertZip = "insert into Zip_code(zip, state, city) values(:zip, :state, :city)";
    $query_insertAddress = "insert into address(street, apartment, zip) values(:street, :apartment, :zip)";
    $query_getAddressID = "select address_id from address where street=:street and apartment=:apartment and zip=:zip";
    $query_insertCustomerAddress = "insert into customer_address(c_id, address_id) values(:cid, :address_id)";


    //other variables
    $error = "";
    $successMessage = "Redirecting you to login page. Please login with your new email/password";

    //code when user clicks sign up
    if ($_POST) {

        $orcl = OCILogon( $_SESSION["db_user"] , $_SESSION["db_password"], $_SESSION["db"]);

        if ($orcl){

            // First check if ZIP exists
            $query = oci_parse($orcl, $query_checkZip);

            oci_bind_by_name($query, ":zip", $_POST["zip"]);

            oci_execute($query);

            //set this value to true if ZIP exists
            $exists = false;

            while(oci_fetch($query)){
                if (oci_result($query, 'ZIP') == $_POST["zip"]){
                    $exists = true;
                break;
                }
            }
            // if it doesnot exists then insert City, State into ZIP_CODE table
            if(!$exists){

                $query = oci_parse($orcl, $query_insertZip);

                oci_bind_by_name($query, ":zip", $_POST["zip"]);
                oci_bind_by_name($query, ":state", $_POST["state"]);
                oci_bind_by_name($query, ":city", $_POST["city"]);

                oci_execute($query);

            }

            // Insert Val into Address table
            $query = oci_parse($orcl, $query_insertAddress);
            oci_bind_by_name($query, ":zip", $_POST["zip"]);
            oci_bind_by_name($query, ":street", $_POST["street"]);
            oci_bind_by_name($query, ":apartment", $_POST["apartment"]);
            oci_execute($query);

            // Get address_ID for inserted address
            $query = oci_parse($orcl, $query_getAddressID);
            oci_bind_by_name($query, ":zip", $_POST["zip"]);
            oci_bind_by_name($query, ":street", $_POST["street"]);
            oci_bind_by_name($query, ":apartment", $_POST["apartment"]);
            oci_execute($query);

            while(oci_fetch($query)){

                $address_ID = oci_result($query, 'ADDRESS_ID');
            }

            // Insert Val into customer_address table
            $query = oci_parse($orcl, $query_insertCustomerAddress);
            oci_bind_by_name($query, ":cid", $_SESSION["cid"]);
            oci_bind_by_name($query, ":address_id", $address_ID);
            oci_execute($query);

            
            //logout oracle db
            OCILogoff($orcl);

            $_SESSION["info"] = "cardAdded";
            header("Location: ../checkout/address&cards.php");

            
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

    <title>Add an Address</title>

    <style type="text/css">

        .jumbotron{
            background-image:url("../checkout/address.jpg");
        }
        #site_heading{
            
            margin-left:130px;
        }
        
        body{

            background-color: #74ab9a;

        }

      
        #smallDiv {
            width: 350px;
            position: relative;
            float: left;
            margin-right: 10px;
        }

        #state {
            width: 350px;
            position: relative;
            float: left;
            margin-left: 10px; 
        }

        #zip {
            width: 350px;
            position: relative;
            float: right; 
        }


    </style>

  </head>

  <body>
    <div class="jumbotron jumbotron-fluid">
        <div id = "site_heading" class="container">
            <h1 class="display-5">Add a new address</h1>
            <p class="lead">Please check the information enterd carefully</p>
        </div>
    </div>
    
    <div id="form_container" class="container">
        <div id="user-error"><?php echo $error; ?></div>
        <form class="form-signup" method="post">
            <div class="form-group" id="address">
                <label for="apartment">Apartment</label>
                <input type="apartment" class="form-control" id="apartment" name = "apartment" placeholder="1809">
            </div>
            <div class="form-group" id="address">
                <label for="address">Street Address</label>
                <input type="address" class="form-control" id="street" name = "street" placeholder="2901 S king Dr">
            </div>
            <div class="form-group" id="smallDiv">
                <label for="city">City</label>
                <input type="text" class="form-control" id="city" name = "city" placeholder="Chicago">
            </div>  

            <div class="form-group" id="smallDiv">
                <div class="form-group" >
                    <label for="state" id="state">State</label>
                    <input type="text" class="form-control" id="state" name = "state" placeholder="Illinois">
                </div>   
            </div>        
           
            <div class="form-group" id="zip">
                <label for="phone">Zipcode</label>
                <input type="text" class="form-control" id="zip" name = "zip" placeholder="60616">
            </div>

            <button type="submit" class="btn btn-lg btn-primary btn-block">Add this Address</button>
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

                error+="<p>" + tag + " is empty or not valid</p>";
            }

            return error;
        }


        $("form").submit(function(e){

            e.preventDefault();
            
            let error = "";

            error = check_valid($("#apartment").val(), error, "Apartment ");
            error = check_valid($("#street").val(), error, "Street ");
            error = check_valid($("#city").val(), error, "City ");
            //error = check_valid($("#state").val(), error, "State");

            if (error==""){

                $("form").unbind("submit").submit();
            }
            else{
                error = '<div class="alert alert-danger" role="alert"><h4 class="alert-heading">Looks like you are missing some fields: </h4>' + error+ '</div>';
                $("#user-error").html(error);
            }
            
        });

        
    </script>
  
  </body>
</html>
