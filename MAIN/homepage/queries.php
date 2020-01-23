<?php

    $query1 = "update customer set default_store=:defaultStore where c_id=:cid";

    $query2 = "select inventory.p_id, product_price, quantity, list_name, manufacturer_id, category_id, image_name  from inventory left outer join product on inventory.p_id=product.p_id where store_id=:storeId and quantity>0";

    $query3 = "select manufacturer_name from manufacturer where manufacturer_id=:manufacturerId";

    $query4 = "with catString(parent_id, category_name) as ((select parent_id, category_name from category where category_id=:categoryId) union all (select category.parent_id, category.category_name from category, catString where category.category_id=catString.parent_id)) select category_name from catString";

    $query5 = "select store_id, store_name, region, zip from store";

    $query6 = "update customer set default_store=:newstore where c_id=:cid";

    $query7 = "select count(*) as num_items from cart where c_id=:cid";

    $query8 = "select quantity from cart where p_id=:pid and c_id=:cid";

    $query9 = "update cart set quantity = quantity+1 where p_id=:pid and c_id=:cid";

    $query10 = "insert into cart(p_id, c_id, quantity) values (:pid, :cid, 1)";

    $query11 = "DELETE from cart where c_id=:cid";

    $query12 = "SELECT od.order_id,od.order_time,ot.order_type,od.tracking_id,ds.delivery_status_name,od.address_id,od.c_id,s.store_name,s.region,od.is_contract FROM order_details od, store s, order_types ot, delivery_status ds WHERE od.delivery_status = ds.delivery_status_id and od.store_id = s.store_id and od.order_type = ot.order_type_id AND c_id = :cid ORDER BY od.order_time DESC";

    $query13 = "select NVL(count(*), 0) as ticketCount from ticket where order_id = :orderId and status !='close'";

    $query14 = "insert into ticket(order_id, status) values (:orderId, 'open')";
?>