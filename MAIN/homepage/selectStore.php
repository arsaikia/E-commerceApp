<?php session_start();


$newStore = $_GET["store"];


if ($_SESSION["cid"] != "NA" && is_numeric($newStore)){

    $orcl = OCILogon($_SESSION["db_user"], $_SESSION["db_password"] , $_SESSION["db"]);

    if ($orcl){

        include 'queries.php';

        $query = oci_parse($orcl, $query6);

        oci_bind_by_name($query, ":cid", $_SESSION["cid"]);
        oci_bind_by_name($query, ":newstore", $newStore);

        oci_execute($query);

        $query = oci_parse($orcl, $query11);
        oci_bind_by_name($query, ":cid", $_SESSION["cid"]);

        oci_execute($query);

        OCILogoff($orcl);
    }

}

if (is_numeric($newStore)){
    $_SESSION["defaultStore"] = $newStore;
    $_SESSION["cart"] = array();
}

header("Location: ../main.php");


?>