<?php session_start();

include "queries.php";

$cartItemDiv = "";
$maxQuantity = 10;
$cid = $_SESSION["cid"];
$cart = $_SESSION["cart"];
$store = $_SESSION["defaultStore"];
$storeName = $_SESSION["storeName"];
$totalAmount = 0;

if($cid != "NA"){

    $orcl = OCILogon($_SESSION["db_user"], $_SESSION["db_password"] , $_SESSION["db"]);

    if ($orcl){

        $query = oci_parse($orcl, $query1);

        oci_bind_by_name($query, ":cid", $cid);
        
        oci_execute($query);
        $cart = array();
        while(oci_fetch($query)){
            $pid = oci_result($query, "P_ID");
            $quantity = oci_result($query, "QUANTITY");
            $cart[$pid] = $quantity;
            $_SESSION["cart"] = $cart;
        }
        OCILogoff($orcl);
    }
}

$orcl = OCILogon($_SESSION["db_user"], $_SESSION["db_password"] , $_SESSION["db"]);

if ($orcl){
    $inputArr = array();
    foreach($cart as $pid=>$quantity){
        $inputArr[] = $pid;
    }
    
    $query = $query2;
    $items = 0;

    foreach($inputArr as $var){
        if ($items!=0){
            $query = $query.",";
        }
        $varName = ":input_".$items;
        $query = $query.$varName;
        $items = $items+1;
    }
    $query = $query.$query2_1;
    $query = oci_parse($orcl, $query);
    oci_bind_by_name($query, ":sid", $store);
    for($i=0;$i<$items;$i++){
        oci_bind_by_name($query, ":input_".$i, $inputArr[$i]);
    }
    
    oci_execute($query);
    while(oci_fetch($query)){
        $pid = oci_result($query, "P_ID");
        $manufacturer = oci_result($query, "MANUFACTURER_NAME");
        $listName = oci_result($query, "LIST_NAME");
        $unitPrice = oci_result($query, "PRODUCT_PRICE");
        $overallQuantity = oci_result($query, "QUANTITY");
        $imageName = oci_result($query, "IMAGE_NAME");
        $selectQuantity = "<select id=\"quantity_select\"class=\"browser-default custom-select\" name='".$pid."'>";
        for($i=1;$i<=$maxQuantity;$i++){
            $selected = "";
            if ($i==$cart[$pid]){
                $selected = "selected";
            }
            else{
                $selected = "";
            }
            $selectQuantity = $selectQuantity."<option ".$selected." value='".$i."'>".$i."</option>";
        }
        $selectQuantity = $selectQuantity."</select>";
        $cartItemDiv = $cartItemDiv."<div id = 'item_container' class='container list-group-item'><div id='img_container' class='item_separator'><img src='../pictures/".$imageName."'></div><div id='list_name_div' class=\"item_separator \"><p class='text-capitalize'><h5 class=\"mb-1\">".$listName."</h5>Unit Price: &#36;".$unitPrice."</p></div><div class=\"item_separator \"><Button class='delete_button'type='button' value='".$pid."'>Delete Item</Button><br class='text-capitalize'>Inventory: ".$overallQuantity."</div><div class=\"item_separator \"><p class='text-capitalize' id=\"quantity_p\">Quantity: ".$selectQuantity."</p><p class='text-capitalize' id='total_price_p'>Total Price: </p></div><div class='item_separator'><p id='cart_".$pid."'class=\"cart_price\">&#36;".$unitPrice*$cart[$pid]."</p></div></div>";

        $totalAmount = $totalAmount + ($unitPrice*$cart[$pid]);

        $_SESSION["Total"] = $totalAmount;
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

    <title>Cart</title>
    <style type='text/css'>
        
        #list_name_div{
            height:200px;
            width:300px;
        }
        .item_separator{
            float:left;
        }
        img{
            height:100px;
            width:150px;
            margin:0px 0px;
            margin-right:20px;
        }
        .delete_button{
            margin-top:20px;
            margin-bottom:40px;
        }
        #quantity_p{
            margin-top:20px;
            margin-bottom:40px;
            margin-left:50px;
        }
        #total_price_p{
            margin-left:100px;
        }
        .list-group{
            margin-top:100px;
        }
        #quantity_select{
            width:60px;
        }
        .cart_price{
            padding-left:50px;
            margin-top:99px;
        }
        #total_container{
            margin-top:0px;
            height:50px;
        }
        #total_amount{
            margin-right:200px;
            margin-left:5px;
            padding-top:17px;
        }
        #proceed_checkout{
            margin-left:80%;
            margin-top:13px;
        }
        #instore_checkout{
            margin-left:10px;
            margin-top:13px;
        }
    </style>
  </head>
  <body>
  <p id="cid" hidden><?php echo $cid; ?></p>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="../homepage/main.php">B-Buy</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <a class="nav-link" href="../homepage/main.php">Products</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Brands</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Account
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                <a class="dropdown-item" href="#">My Orders</a>
                <a class="dropdown-item" href="#">Account Details</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Addresses</a>
                <a class="dropdown-item" href="#">Cards</a>
                </div>
            </li>
            </ul>
            <p id="store_info">Store: <a href = "#storeModal" data-toggle="modal" data-target="#storeModal"><?php echo $storeName; ?></a></p>
        </div>
        </nav>
    <div class='list-group'><?php echo $cartItemDiv; ?></div>
    <div id='total_container' class="container"><h2 id="total_amount" class="font-weight-bold"style="float:right;">&#36;<?php echo $totalAmount; ?></h2><h1 class="font-weight-bold"style="float:right;margin-top:10px">Total: </h1></div>
    <div class='container' id="checkout_buttons"><a id="proceed_checkout" href="../checkout/address&cards.php" class="btn btn-primary">Proceed to Checkout</a></div>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script type="text/javascript">

        if($('#cid').text()=="NA"){
            $('#proceed_checkout').attr("href", "../checkout/inStore.php");
        }
        else{
            $('#proceed_checkout').css("margin-left", "70%");
            $('#checkout_buttons').append("<a id=\"instore_checkout\" href=\"../checkout/inStore.php\" class=\"btn btn-primary\">Buy In store</a>");
        }

        $("select").on('change', function(){
            if ($(this).attr("id")=="quantity_select"){

                var pId = $(this).attr("name");
                var Quantity = this.value;
                var Action = "change";
                var message = 0;
                $.ajax({
                    url: 'changeCart.php',
                    type: 'POST',
                    data: {
                        action:Action,
                        pid: pId,
                        quantity:Quantity
                    },
                    success: function(msg) {
                        message = 1;
                        window.location.href='cart.php';
                    },
                    datatype:"text"               
                });


            }
        });
        $('button').click(function(){
            if ($(this).hasClass('delete_button')){
                
                var pId = this.value;
                var Action = "delete";
                var message = 0;
                $.ajax({
                    url: 'changeCart.php',
                    type: 'POST',
                    data: {
                        action:Action,
                        pid: pId
                    },
                    success: function(msg) {
                        message = 1;
                        window.location.href='cart.php';
                    },
                    datatype:"text"               
                });
            }
        });

    </script>
  </body>
</html>