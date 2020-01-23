<?php session_start();

    include "queries.php";

    $returnArray = array();

    $orderId = $_POST["orderid"];

    $orcl = OCILogon( $_SESSION["db_user"] , $_SESSION["db_password"], $_SESSION["db"]);

    $query = oci_parse($orcl, $query13);

    oci_bind_by_name($query, ":orderId", $orderId);

    oci_execute($query);

    $ticketCount = 0;

    while(oci_fetch($query)){
        $ticketCount = oci_result($query, 'TICKETCOUNT');
    }

    if($ticketCount>0){
        OCILogoff($orcl);
        $returnArray["val"] = "present";
        return json_encode($returnArray);
    }
    else{

        $query = oci_parse($orcl, $query14);

        oci_bind_by_name($query, ":orderId", $orderId);

        oci_execute($query);

        OCILogoff($orcl);

        $returnArray["val"] = "present";
        return json_encode($returnArray);
    }
?>