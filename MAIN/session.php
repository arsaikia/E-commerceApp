<?php session_destroy();
    
    session_start();
    
    $_SESSION["db"] = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521)))(CONNECT_DATA=(SID=orcl)))";
    $_SESSION["db_user"] = "*****";
    $_SESSION["db_password"] = "*****";

    $_SESSION["cid"] = "NA";

?>