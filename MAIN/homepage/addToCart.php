<?php session_start();

    $pid = $_POST["pid"];
    $array = array();
    if($_SESSION["cid"]=="NA"){
        if($_SESSION["cart"][$pid]){
            $val = $_SESSION["cart"][$pid];
            $_SESSION["cart"][$pid] = $val+1;
            $array['val'] = 0;
            
        }
        else{
            $_SESSION["cart"][$pid] = 1;
            $array['val'] = 1;
            
        }
        echo json_encode($array);
    }
    else{

        include 'queries.php';
        
        $cid = $_SESSION["cid"];

        $orcl = OCILogon($_SESSION["db_user"], $_SESSION["db_password"] , $_SESSION["db"]);
        if($orcl){
            $query = oci_parse($orcl, $query8);
            oci_bind_by_name($query, ":cid", $cid);
            oci_bind_by_name($query, ":pid", $pid);
            oci_execute($query);
            
            $inserted = false;
            while(oci_fetch($query)){
                $inserted = true;
                
                $query = oci_parse($orcl, $query9);
                oci_bind_by_name($query, ":cid", $cid);
                oci_bind_by_name($query, ":pid", $pid);
                oci_execute($query);
                $array['val'] = 0;
                OCILogoff($orcl);
                echo json_encode($array);
                
            }

            if(!$inserted){
                
                $query = oci_parse($orcl, $query10);
                oci_bind_by_name($query, ":cid", $cid);
                oci_bind_by_name($query, ":pid", $pid);
                oci_execute($query);
                $array['val'] = 1;
                OCILogoff($orcl);
                echo json_encode($array);
            }

            
        }
        


    }

?>