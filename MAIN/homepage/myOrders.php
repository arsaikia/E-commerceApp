<?php session_start();

include "queries.php";

$cid = $_SESSION["cid"];
$orderCards = "";

if($cid=="NA"){
    header("Location: main.php");
}
else{

    $orcl = OCILogon( $_SESSION["db_user"] , $_SESSION["db_password"], $_SESSION["db"]);

    $query = oci_parse($orcl, $query12);

    oci_bind_by_name($query, ":cid", $cid);

    oci_execute($query);

    while(oci_fetch($query)){
        $orderId = oci_result($query, "ORDER_ID");
        $orderTime = oci_result($query, "ORDER_TIME");
        $orderType = oci_result($query, "ORDER_TYPE");
        $trackingId = oci_result($query, "TRACKING_ID");
        $deliveryStatusName = oci_result($query, "DELIVERY_STATUS_NAME");
        $storeName = oci_result($query, "STORE_NAME");
        $storeRegion = oci_result($query, "REGION");
        $isContract = oci_result($query, "IS_CONTRACT");
        $deliveryStatus = "";
        $deliveryStatusName = explode("_", $deliveryStatusName);
        foreach( $deliveryStatusName as $k=>$v){
            $deliveryStatus = $deliveryStatus." ".ucfirst($v);
        }
        $orderCards = $orderCards."<div class='card list-group-item row' id='".$orderId."'><div class='card-body'><h5 class='card-title'>Order Id: #".$orderId."</h5><h6 class=\"card-subtitle mb-2 text-muted\">Delivery status: ".$deliveryStatus."</h6><p class='card-text'>Store: ".$storeName.", ".$storeRegion."<span id='orderType'>"."OrderType: ".strtoupper($orderType)."</span></p><p class = 'card-text orderTime'>Ordered on: ".$orderTime."</p><p class='card-text tracking'>Tracking Id: #".$trackingId."<span id='ticket'><a id='".$orderId."' class='errorLink' href = ''>Raise a Ticket</a></span></p></div></div>";
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <title>My Orders</title>
    <style type='text/css'>
        .container-fluid{
            margin-top:57px;
        }
        .card{
            margin-left:20px;
            background-color: #f3e9fc;
            padding-bottom:1px;
        }
        .card-text{
            margin:2px;
        }
        .tracking{
            color: grey;
            margin-top:10px !important;
        }
        #ticket{
            color:#270943;
            float:right;
        }
        #orderType{
            float:right;
        }
        .orderTime{
            margin-top:10px !important;
        }

        .toast{
            position:absolute;
            top:10%;
            left:90%;
        }

    </style>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="main.php">B-Buy</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <a class="nav-link" href="#">Products <span class="sr-only">(current)</span></a>
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
            <form class="form-inline my-2 my-lg-0" method='post'>
            <input name = 'search_query' class="form-control mr-sm-2" type="search" placeholder="Search B-Buy" aria-label="Search">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
            </form>
        </div>
        </nav>
        <div class="container-fluid">
        <span id='errorAlert'></span>
            <div class="col list-group">
                <?php echo $orderCards; ?>
            </div>
        </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script type='text/javascript'>

        $('.alert').alert();

        $(".errorLink").click(function(){
            var orderId = $(this).attr('id');
            $.ajax({
                url: 'raiseTicket.php',
                type: 'POST',
                data: {
                    orderid: orderId,
                },
                success: function(msg) {
                        //$("#errorAlert").html("<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\"><p id=\"success-message\">You already have a ticket for this order</p><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button></div>");
                    $("#errorAlert").html("<div class=\"toast\" role=\"alert\" aria-live=\"assertive\" aria-atomic=\"true\"><div class=\"toast-header\"><strong class=\"mr-auto\">Success</strong><button type=\"button\" class=\"ml-2 mb-1 close\" data-dismiss=\"toast\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button></div><div class=\"toast-body\">Ticket Raised Successfully</div></div>");
                },
                datatype:"text"               
            });
        });
    </script>
  </body>
</html>