<?php session_start();

    $cid = $_SESSION["cid"];
    $card_ID = "";
    /*
    if ($_POST) {
        echo $_POST["name"];
        echo $_POST["cardNo"];
        echo $_POST["vaidity"];
        echo $_POST["cardType"];
    }*/




    // queries used in this page
    $query_checkCard = "select card_id, card_number from cards where card_number= :card_number";
    $query_InsertCard = "Insert into cards(card_number, expiry, credit_or_debit, name_on_card) values(:card_number, :expiry, :credit_or_debit, :name_on_card)";
    $query_cardID = "select card_id from cards where card_number=:card_number";
    $query_InsertCustomerCards = "insert into customer_cards(c_id, card_id) values (:cid, :cardID)";

    //other variables
    $error = "";
    $successMessage = "Redirecting you to Checkout page. Card Successfully Added";

    //code when user clicks sign up
    if ($_POST) {

        $orcl = OCILogon( $_SESSION["db_user"] , $_SESSION["db_password"], $_SESSION["db"]);

        if ($orcl){

            // First check if card already exists
            $query = oci_parse($orcl, $query_checkCard);

            oci_bind_by_name($query, ":card_number", $_POST["cardNo"]);

            oci_execute($query);

            //set this value to true if Card exists
            $exists = false;

            while(oci_fetch($query)){
                if (oci_result($query, 'CARD_NUMBER') ==$_POST["cardNo"]){

                    $card_ID = oci_result($query, 'CARD_ID');

                    //show Card exists
                    $error = '<div class="alert alert-danger" role="alert"><h4 class="alert-heading">'.'Card already exists'.'</h4></div>';
                    $exists = true;
                break;
                }
            }
            // if it doesnot exists then insert card into CARD table
            if(!$exists){

                $query = oci_parse($orcl, $query_InsertCard);

                oci_bind_by_name($query, ":card_number", $_POST["cardNo"]);
                oci_bind_by_name($query, ":expiry", $_POST["vaidity"]);
                oci_bind_by_name($query, ":credit_or_debit", $_POST["cardType"]);
                oci_bind_by_name($query, ":name_on_card", $_POST["name"]);

                if (oci_execute($query)){
                    $error = '<div class="alert alert-success" role="alert"><h4 class="alert-heading">'.$successMessage.'</h4></div>';
                    
                    // Get card_id against card number
                    $query = oci_parse($orcl, $query_cardID);

                    oci_bind_by_name($query, ":card_number", $_POST["cardNo"]);

                    oci_execute($query);
                    
                    while(oci_fetch($query)){

                        $card_ID = oci_result($query, 'CARD_ID');
                    }

                }
                else{
                    //show server down
                    $error = '<div class="alert alert-danger" role="alert"><h4 class="alert-heading">'.'Something Went Wrong. Try again later'.'</h4></div>';
                }
            }

            
            echo $card_ID;
            
            // insert data in customer_cards against card_id
            $query = oci_parse($orcl, $query_InsertCustomerCards);

            oci_bind_by_name($query, ":cid", $_SESSION["cid"]);
            oci_bind_by_name($query, ":cardID", $card_ID);

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

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href = "https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css" rel = "stylesheet">
  

    <title>Add a Card</title>

    <style type="text/css">

        .jumbotron{
            background-image:url("../checkout/address.jpg");
        }

        #site_heading{
            
            margin-left:130px;
        }
        
        body{
            background-color: #CECECE;

            background-image: url("../checkout/Bye_Mice.png");
        }

      
        #smallDiv {
            width: 150px;
            position: relative;
            float: left;
            margin-right: 10px;
        }


        
    </style>

  </head>

  <body>
    <div class="jumbotron jumbotron-fluid">
        <div id = "site_heading" class="container">
            <h1 class="display-5">Add a Credit/ Debit Card</h1>
            <p class="lead">Please check the information enterd carefully</p>
        </div>
    </div>
    
    <div id="form_container" class="container">
        <div id="user-error"><?php echo $error; ?></div>
        <div id="test"><?php echo $val1; ?></div>
        <form class="form-signup" method="post">
            <div class="form-group" id="address">
                <label for="name">Name</label>
                <input type="name" class="form-control" id="name" name = "name" placeholder="Arunabh Saikia">
            </div>
            <div class="form-group" id="address">
                <label for="cardNo">Card Number</label>
                <input type="cardNo" class="form-control" id="cardNo" name = "cardNo" placeholder="2123 4343 1221 1123">
            </div>
            <div class="form-group" id="smallDiv">
                <label for="cardType">Credit/ Debit</label>
                <input type="cardType" class="form-control" id = "cardType"  name = "cardType" placeholder="c/d">
            </div>
            <div class="form-group" id="smallDiv">
                <label for="vaidity">Valid Till</label>
                <input type="vaidity" class="form-control" id = "datepicker"  name = "vaidity" placeholder="05/20">
            </div>  

            <button id ="submit" type="submit" class="btn btn-lg btn-primary btn-block">Add Card</button>
            <br><br>
            
        </form>
    </div>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src = "https://code.jquery.com/jquery-1.10.2.js"></script>
    <script src = "https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
    
  
    <script type="text/javascript">

        $(function() {
            $( "#datepicker" ).datepicker({ dateFormat: 'dd-M-yy' }).val();
         });



        function check_valid(value, error, tag){

            if(value=="" || value==undefined || !value.replace(/\s/g, '').length){

                if(tag=="cardNum" && !Number.isInteger(value)){
                    error+="<p>" + tag + " is empty or not valid</p>";
                    return error;
                }

                error+="<p>" + tag + " is empty or not valid</p>";
            }

            return error;
        }





        $("form").submit(function(e){

            e.preventDefault();
            
            let error = "";

            
            error = check_valid($("#cardNo").val(), error, "Card Number");
            error = check_valid($("#name").val(), error, "Name on Card");
            error = check_valid($("#cardType").val(), error, "Card Type (C/ D)");
            error = check_valid($("#datepicker").val(), error, "Date Entered");

            if (error==""){

                $("form").unbind("submit").submit();
            }
            else{
                error = '<div class="alert alert-danger" role="alert"><h4 class="alert-heading">Hmmmm...! Are you sure these are the correct values </h4>' + error+ '</div>';
                $("#user-error").html(error);
            }
            
        });

        



        
    </script>
  
  </body>
</html>
