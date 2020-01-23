<?php session_start();

    $action = $_POST["action"];
    $cid = $_SESSION["cid"];
    $cart = $_SESSION["cart"];

    if ($action=="change"){

        $pid = $_POST["pid"];
        $quantity = $_POST["quantity"];

        $cart[$pid] = $quantity;
        $_SESSION["cart"] = $cart;

        if($cid != "NA"){

            include 'queries.php';

            $orcl = OCILogon($_SESSION["db_user"], $_SESSION["db_password"] , $_SESSION["db"]);

            $query = oci_parse($orcl, $query3);
            oci_bind_by_name($query, ":quantity", $quantity);
            oci_bind_by_name($query, ":pid", $pid);
            oci_bind_by_name($query, ":cid", $cid);

            oci_execute($query);

            OCILogoff($orcl);
        }

        echo "{'val':'0'}";
       
    }
    else if ($action=="delete"){

        $pid = $_POST["pid"];
        unset($cart[$pid]);
        $_SESSION["cart"] = $cart;

        if($cid != "NA"){

            include 'queries.php';

            $orcl = OCILogon($_SESSION["db_user"], $_SESSION["db_password"] , $_SESSION["db"]);

            $query = oci_parse($orcl, $query4);
            oci_bind_by_name($query, ":pid", $pid);
            oci_bind_by_name($query, ":cid", $cid);

            oci_execute($query);

            OCILogoff($orcl);
        }

        echo "{'val':'0'}";
    }
?>