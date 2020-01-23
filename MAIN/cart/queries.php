<?php

    $query1 = "select p_id, quantity from cart where c_id=:cid";

    $query2 = "select s.p_id, m.manufacturer_name, p.list_name, s.product_price, s.quantity, p.image_name from inventory s, product p, manufacturer m where s.store_id=:sid and s.p_id=p.p_id and s.p_id in (";
    $query2_1 = ") and p.manufacturer_id=m.manufacturer_id";

    $query3 = "update cart set quantity = :quantity where p_id=:pid and c_id=:cid";

    $query4 = "delete cart where p_id=:pid and c_id=:cid";


?>