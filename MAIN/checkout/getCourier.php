<?php session_start();


$cid = $_SESSION["cid"];

$addID = $_POST["addID"];

$array = array();





$query_GetCourierDetails = "select distinct c.estimated_delivery, C.courier_id, C.COURIER_NAME, C.delivery_fee from address A, courier C where A.zip = C.zip and A.address_id = :address_id";


if ($_POST) {

	$orcl = OCILogon( $_SESSION["db_user"] , $_SESSION["db_password"], $_SESSION["db"]);

        if ($orcl){

        	$query = oci_parse($orcl, $query_GetCourierDetails);

            oci_bind_by_name($query, ":address_id", $addID);

            oci_execute($query);

            $counter = 0;
		    while(oci_fetch($query)){

		    	$array[$counter] = array();
		        $array[$counter]["courier_id"] = oci_result($query, 'COURIER_ID');
		        $array[$counter]["courier_name"] = oci_result($query, 'COURIER_NAME');
		        $array[$counter]["delivery_fee"] = oci_result($query, 'DELIVERY_FEE');
		        $array[$counter]["estimated_delivery"] = oci_result($query, 'ESTIMATED_DELIVERY');

		        $counter++;
		    }
        }

}

echo json_encode($array);


?>