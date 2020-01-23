<?php session_start();

    $cid = $_SESSION["cid"];
    $array = array();
    $array_card = array();
    $array_courier = array();
    $addDiv = "";
    $cardDiv = "";
    $add_id="";
    $card_id = "";
    $account_num = "";
    $contractButtn = "";

    

    $query_GetAddress = "select A.address_id, A.apartment, A.street, Z.city, Z.state, Z.zip, CA.c_id from address A, customer_address CA, zip_code Z where A.zip = Z.zip and A.address_id = CA.address_id and CA.c_id=:cid";

    $query_GetCardDetails = "select C.name_on_card, C.card_id, C.card_number, C.expiry, C.credit_or_debit from cards C, customer_cards CC where c.card_id = cc.card_id and c_id = :cid";

    $query_GetCourier = "select courier_name, courier_id, estimated_delivery, delivery_fee from courier where ZIP=:zip";

    $query_GetCustomerName = "select first_name, last_name from customer where c_id =:cid";

    $query_contract = "select account_name, account_number, c_id from contract where c_id = :cid";

    $query_courierDetails = "select distinct courier_id, COURIER_NAME, ZIP, delivery_fee from courier";



    // Connect to oracle DB
    $orcl = OCILogon( $_SESSION["db_user"] , $_SESSION["db_password"], $_SESSION["db"]);

    

    $query = oci_parse($orcl, $query_contract);

    oci_bind_by_name($query, ":cid",  $cid);

    oci_execute($query);
    $account_name = "";
    
    while(oci_fetch($query)){

    	$account_num = oci_result($query, 'ACCOUNT_NUMBER');
    	$account_name = oci_result($query, 'ACCOUNT_NAME');
    	
    }

    if ($account_num != "") {
    	
    	//$contractButtn = $contractButtn."<button id=\"contract\" type=\"submit\" class=\"btn btn-outline-success \" >Buy with Contract : ".$account_name."  A/C : ".$account_num."</button>";
    	$contractButtn = $contractButtn."<button id=\"contract\" type=\"submit\" class=\"btn btn-outline-success\" data-toggle=\"button\" aria-pressed=\"false\" autocomplete=\"off\" checked>Buy with Contract : ".$account_name."  A/C : ".$account_num."</button>";
    }

    else{
    	$contractButtn = $contractButtn."<button id=\"contract\" type=\"submit\" class=\"btn btn-outline-success \" >Contract not Available</button>";
    }

    
    


    $query = oci_parse($orcl, $query_GetAddress);

    oci_bind_by_name($query, ":cid",  $cid);

    oci_execute($query);
    $counter = 0;
    while(oci_fetch($query)){

    	$array[$counter] = array();
        $array[$counter]["apartment"] = oci_result($query, 'APARTMENT');
        $array[$counter]["street"] = oci_result($query, 'STREET');
        $array[$counter]["city"] = oci_result($query, 'CITY');
        $array[$counter]["zip"] = oci_result($query, 'ZIP');
        $array[$counter]["addr_id"] = oci_result($query, 'ADDRESS_ID');

        $counter++;
    }

    $query1 = oci_parse($orcl, $query_GetCustomerName);

    oci_bind_by_name($query1, ":cid",  $cid);

    oci_execute($query1);
    $first_name = "";
    $last_name = "";
    while(oci_fetch($query1)){

    	$first_name = oci_result($query1, 'FIRST_NAME');
        $last_name = oci_result($query1, 'LAST_NAME');
    }

   	
    foreach($array as $key=> $value){
    	$addr = "";
    	$addrId = "";
    	$apt="";
    	foreach($value as $k=>$v){
    		if($k=='apartment'){
    			$apt = "APT:  ".$v;
    		}
    		else if($k=="addr_id"){
    			$addrId = $v;
    		}
    		else{
    			$addr = $addr." ".$v;
    		}
    	}

    	$addDiv = $addDiv."<div id='".$addrId."'class=\"card list-group-item addr\" style=\"width: 36rem;\"><div class=\"card-body\"><h5 class=\"card-title\">".$first_name." ".$last_name."</h5><h6 class=\"card-subtitle mb-2 text-muted\">".$apt."<br></h6><p class=\"card-text\">".$addr."</p><a href=\"#\" class=\"card-link\"></a></div></div>";	
    }

    OCILogoff($orcl);


    $orcl = OCILogon( $_SESSION["db_user"] , $_SESSION["db_password"], $_SESSION["db"]);
    $query2 = oci_parse($orcl, $query_GetCardDetails);

    oci_bind_by_name($query2, ":cid",  $cid);

    oci_execute($query2);
    $counter1 = 0;

    while(oci_fetch($query2)){
    	$array_card[$counter1] = array();
        $array_card[$counter1]["card_id"] = oci_result($query2, 'CARD_ID');
        $array_card[$counter1]["card_number"] = oci_result($query2, 'CARD_NUMBER');
        $array_card[$counter1]["expiry"] = oci_result($query2, 'EXPIRY');
        $array_card[$counter1]["credit_or_debit"] = oci_result($query2, 'CREDIT_OR_DEBIT');
        $array_card[$counter1]["name_on_card"] = oci_result($query2, 'NAME_ON_CARD');
        $counter1++;
    }
    foreach($array_card as $key=> $value){
    	
    	$card="";
    	$card_details="";
    	$cardId = "";
    	$card_number = "";
    	$expiry = "";
    	$name_on_card = "";

    	foreach($value as $k1=>$v1){
    		if($k1=='credit_or_debit'){
    			if ($v1 == 'c') {
    				$card = "Credit";
    			}
    			elseif ($v1 == 'd') {
    				$card = "Debit";
    			}
    			;
    		}
    		else if($k1=="card_id"){
    			$cardId = $v1;
    		}
    		else if($k1=="card_number"){
    			$card_number = $v1."    ";
    		}
    		else if($k1=="expiry"){
    			$expiry = $v1;
    		}
    		else if($k1=="name_on_card"){
    			$name_on_card = $v1;
    		}
    	}

    	$cardDiv = $cardDiv."<div id='".$cardId."'class=\"card list-group-item cards\" style=\"width: 36rem;\"><div class=\"card-body\"><h5 class=\"card-title\">".$name_on_card."</h5><h6 class=\"card-subtitle mb-2 text-muted\">".$card_number."<br></h6><p class=\"card-text\">Card Type: ".$card." Valid Till: ".$expiry."</p><a href=\"#\" class=\"card-link\"></a></div></div>";

    	OCILogoff($orcl);
    }

    if ($_POST) {
    $_SESSION['address_id'] = $_POST['address_id'];
 	$_SESSION['card_id'] = $_POST['card_id'];
 	$_SESSION['courier_id'] = $_POST['courier_id'];
 	$_SESSION['hasContract'] = $_POST['hasContract'];
 	$_SESSION["info"] = "BuyNow";
    header("Location: ../checkout/buyNow.php");
	}

?>


<!DOCTYPE html>
<html>
<head>
	<!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">


	<title>Existing Cards and Addresses	</title>
	  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

	<style type="text/css">

	body{

	    background-color: ;

	}

	.container-fluid, .jumbotron {
		background-color:   ;

	}

	.cards, .addr {
		background-color:     ;

	}
	
	

	#cards {
    
    width:700px;
    height: 600px;
    border: 3px solid grey;
    margin-right: 5px;
    float: right;
    border-radius: 5px;
    
	}
	.list-group-item{
		padding:10px;
		margin:3px;
		border:1px solid black;
	}

	
	#addressSelect {

		margin: 30px 0px 5px 15px
	}

	#add_address_button, #add_card_button {

		width: 580px;
		margin-top: 20px;
		position: :relative;
		float: left;
		margin-left: 10px;
	}

	.col {

		margin-right: 25px;
		margin-left: 25px;
	}

	.addresses {

		height: 500px;
		padding:10px;
	}


	#contract {
		width: 20%;
		margin-top: 70px;
		position: :relative;
		float: left;
		margin-left: 2%;

	}

	.courier {
		width: 15%;
		margin-top: 70px;
		position: :relative;
		float: left;
		margin-left:20%;
	}

	#continue {
		width: 10%;
		margin-top: 70px;
		position: :relative;
		float: left;
		margin-left: 25%;

	}

	#mainLink{
		position:relative;
		top:20px;
		left:45%;
	}


	</style>

</head>


<body>

	

	<div class="">

		<div class="jumbotron vertical-center"> 
			<div class="container-fluid">
				<div class="row justify-content-md-center">
					<div class='col col-xl-auto '>
						<div id="addressSelect">
							<h3>Select an Address</h3>
						</div>
						<div class='list-group addresses overflow-auto'>	
							<?php echo $addDiv; ?>
						</div>
						<button id="add_address_button" type="button" class="btn btn-primary btn-lg btn-block">Add a new address</button>		
					</div>

					<div class='col col-xl-auto '>
						<div id="addressSelect">
							<h3>Select a Card</h3>
						</div>
						<div class='list-group addresses overflow-auto'>	
							<?php echo $cardDiv; ?>
						</div>
						<button id="add_card_button" type="button" class="btn btn-primary btn-lg btn-block" >Add a Card</button>		
					</div>

				</div>


			</div>
			<div>
				<?php echo $contractButtn; ?>

				<select id = "courierDetails" class="form-control courier">
				  	<option disabled:true selected hidden>Select Address for Avaiable Courier</option>
				</select>

				<form class="form-buy" method="post">
					<input id="selected_address" class="form-control" name = "address_id" hidden></input>
					<input id="selected_card" class="form-control" name = "card_id" hidden></input>
					<input id="courier_id" class="form-control" name = "courier_id" hidden></input>
					<input id="hasContract" class="form-control" name = "hasContract" hidden></input>
					<button id="continue" type="submit"  class="btn btn-lg btn-primary" disabled >Buy Now</button>

				</form>
			</div>
			<div class="container-fluid">
			<br>
				<button id="mainLink" type="button"  class="btn btn-lg btn-secondary">Back To Main</button>
			</div>
		</div>

	</div>


	<!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  
    <script type="text/javascript">

    	// Button Navigation:
    	$('#add_address_button').click(function() {
		   window.location = "addAddress.php" ;
		});
		
		$('#mainLink').click(function() {
		   window.location.href = "../homepage/main.php" ;
		});

		$('#add_card_button').click(function() {
		   window.location = "addCard.php" ;
		});

		// Disabled Courier
		$('#courierDetails').attr('disabled','disabled');

		// Check if conact is available
		$('#hasContract').attr('value', 'N')
		$($('#continue').click(function(){
			if ($('#contract').hasClass('active')) {
				$('#hasContract').attr('value', 'Y');
			}
			else{
				$('#hasContract').attr('value', 'N');
			}
			
		}));
		
		

    	$('.list-group-item').click(function(){

    		if($(this).hasClass("addr")){
    			var addr_id = $(this).attr("id");
    			var parent = $(this).parent()[0];
    			$(parent).children().each(function(){
    				//console.log("clicked");
    				if($(this).attr("id")==addr_id){
    					$(this).css('background-color', "#ffb3b3");
    				}
    				else{
    					$(this).css('background-color', "white");
    				}
    			});

    			$('#selected_address').attr('value', addr_id);

    			$addID = $('#selected_address').val();

    			$.ajax({
	              url: 'getCourier.php',
	              type: 'POST',
	              data: {
	                  addID: $addID,
	              },
	              success: function(msg) {
	                  var message = JSON.parse(msg);

	                  //console.log(message.length);
	                  
	                  if(message.length == 0){

	                  	$('#courierDetails').find('option').remove().end().append('<option disabled selected hidden >No Available Courier for Address</option>');
	                  	$('#courierDetails').attr('disabled','disabled');
	                  	$("#continue").attr("disabled", true);
	                  	//$("#courierDetails").append(new Option("No Courier Available", "value"));

	                  }

	                  else{
	                  	//$("#continue").attr("disabled", false);
	                  	$('#courierDetails').find('option').remove().end();
	                  	$('#courierDetails').attr('disabled','false').removeAttr('disabled');;

	                  	$.each(message, function(i, obj) {

	                  	if (obj["delivery_fee"] == null) {obj["delivery_fee"] = 'FREE !';}

	                  	  var courier_id = obj["courier_id"] ;
						  var var1 = obj["courier_name"] ;
						  var1 = var1 + " :  $ " + obj["delivery_fee"] + " - " + obj["estimated_delivery"] +" Days";

						  $("#courierDetails").append(new Option(var1, courier_id));

						});
	                  }

	                  
	              },
	              datatype:"text"               
	          });

    			if( ($('#selected_card').val() != "") ){

    				$("#continue").attr("disabled", false);
				}
				


    			if (($('#courierDetails').prop('disabled') == true)) {
    				$("#continue").attr("disabled", true);
    			}
    			

    		}
    	});

    	$('.list-group-item').click(function(){

    		if($(this).hasClass("cards")){
    			var card_id = $(this).attr("id");
    			var parent = $(this).parent()[0];
    			$(parent).children().each(function(){
    				console.log("clicked");
    				if($(this).attr("id")==card_id){
    					$(this).css('background-color', "#a29dfa");
    				}
    				else{
    					$(this).css('background-color', "white");
    				}
    			});

    			$('#selected_card').attr('value', card_id);

    			if ( ($('#selected_address').val() != "") ) {

    				$("#continue").attr("disabled", false);
    			}
    			

    			if (($('#courierDetails').prop('disabled') == true)) {
    				$("#continue").attr("disabled", true);
    			}
    			
    		}
    	});

    	
    	$('#courier_id').attr('value', 1);
    	$("#courierDetails").on('change', function(){

    		var var2 = this.value;
    		$('#courier_id').attr('value', var2);
    		
    	});


    	//var courierDetails = $('#courierDetails').find(":selected").text();
    	//console.log(courierDetails);

    	
    	


    </script>

</body>



</html>