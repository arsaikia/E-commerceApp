<?php session_start(); 
	
	include "queries.php";

	$orderType = "delivery";
	$total = $_SESSION["Total"];
	$addressId = $_SESSION['address_id'];
 	$cardId = $_SESSION['card_id'];
 	$courierId = $_SESSION['courier_id'];
	$isContract = $_SESSION['hasContract'];
	$cid = $_SESSION["cid"];
	$cart = $_SESSION["cart"];
	$store = $_SESSION["defaultStore"];
	$orderId = -1;
	$defaultCid = 0;
	
	$messageHeader = "";

	$message = "";

 	if ($_SESSION["info"]=="BuyNow"){

		// Connect to oracle DB
		$orcl = OCILogon( $_SESSION["db_user"] , $_SESSION["db_password"], $_SESSION["db"]);

		$query = oci_parse($orcl, $query5);

		oci_bind_by_name($query, ":cardId",  $cardId);

		oci_execute($query);
		
		$creditOrDebit = "";
		$creditBalance = 0;
		$expiry = "";

		while(oci_fetch($query)){

			$creditOrDebit = oci_result($query, "CREDIT_OR_DEBIT");
			$creditBalance = oci_result($query, "CREDIT_BALANCE");
			$expiry = oci_result($query, "EXPIRY");
		}

		$query = oci_parse($orcl, $query6);

		oci_bind_by_name($query, ":courierId",  $courierId);

		oci_execute($query);

		$courierFee = 0;

		while(oci_fetch($query)){

			if(!oci_field_is_null($query, "DELIVERY_FEE")){
				$courierFee = $courierFee + oci_result($query, "DELIVERY_FEE");
			}
		}
		
		if ($creditOrDebit == "c" && $isContract != "Y"){
			if ((int)$creditBalance < ($total + $courierFee)){
				$messageHeader = "Order Place Failed..!!";
				$message = "Your credit card doesnot have sufficient credit. Please use another card";
			}
			
		}

		if($messageHeader ==""){

			if (strtotime($expiry) < strtotime(date("d-M-y")) && $isContract != "Y"){
				$messageHeader = "Order Place Failed..!!";
				$message = "You used an expired card. Please use a new card";
			}
			else{
				$query = oci_parse($orcl, $query1);
				oci_execute($query);

				while(oci_fetch($query)){
					$orderId = oci_result($query, "ORDER_ID");
				}

				if($orderId != -1){

					$query = oci_parse($orcl, $query7);

					$timestamp = date("d-M-Y h:i:s A", time());
					oci_bind_by_name($query, ":orderTime", $timestamp);
					oci_bind_by_name($query, ":orderId", $orderId);
					oci_bind_by_name($query, ":storeId", $store);
					oci_bind_by_name($query, ":orderType", $orderType);
					if ($cid=="NA"){

						oci_bind_by_name($query, ":cid", $defaultCid);
					}
					else{
						
						oci_bind_by_name($query, ":cid", $cid);
					}
					if ($isContract != "Y"){
						oci_bind_by_name($query, ":cardId", $cardId);
					}
					oci_bind_by_name($query, ":addressId", $addressId);
					oci_bind_by_name($query, ":courierId", $courierId);
					oci_bind_by_name($query, ":isContract", $isContract);

					oci_execute($query);
					
					foreach($cart as $k=>$v){
						$query = oci_parse($orcl, $query3);
						oci_bind_by_name($query, ":orderId", $orderId);
						oci_bind_by_name($query, ":storeId", $store);
						oci_bind_by_name($query, ":pId", $k);
						oci_bind_by_name($query, ":quantity", $v);
						oci_execute($query);
					}

					if($cid !="NA"){
						$query = oci_parse($orcl, $query4);
						oci_bind_by_name($query, ":cid", $cid);

						oci_execute($query);
					}

					$_SESSION["cart"] = array();

					$messageHeader = "Order Place Successfully..!!";
					$message = "Please check your mail for order details";

				}
			}
		}

		OCILogoff($orcl);
	 }

 	
 ?>
 <!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Buy</title>
  </head>
  <body>
    <h1><?php echo $messageHeader; ?></h1>

    <br>
    <br>
    <h4><?php echo $message; ?></h4>
    <br>
    <p id="dots"></p>



    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script type='text/javascript'>

        function dots(){
            $("#dots").append("..");
        }

        setInterval(dots, 500);

        window.setTimeout(function(){
            if ($("h1").text().includes("Failed")){
				window.location.href = "../checkout/address&cards.php";
			}
			else{
            	window.location.href = "../homepage/main.php";
			}
        }, 5000);

    </script>
  </body>
</html>