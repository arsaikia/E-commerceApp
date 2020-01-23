<?php session_start();

    $query1 = "select order_id_seq.nextval as order_id from dual";

    $query2 = "insert into order_details(order_id, store_id, order_type, order_time, c_id, delivery_status) values (:orderId, :storeId, (select order_type_id from order_types where order_type=:orderType),:orderTime, :cid, (select delivery_status_id from delivery_status where delivery_status_name='ready_for_pickup'))";

    $query3 = "BEGIN insert into orders(order_id, p_id, quantity, unit_price) values (:orderId, :pId, :quantity, (select product_price from inventory where store_id=:storeId and p_id=:pId)) ; update inventory set quantity = quantity-:quantity where store_id=:storeId and p_id=:pId; End;";

    $query4 = "delete from cart where c_id=:cid";

    $query5 = "select credit_or_debit, credit_balance, expiry from cards where card_id = :cardId";

    $query6 = "select delivery_fee from courier where courier_id=:courierId";

    $query7 = "insert into order_details(order_id, store_id, order_type, order_time, c_id, card, address_id, courier_id, delivery_status, is_contract) values (:orderId, :storeId, (select order_type_id from order_types where order_type=:orderType),:orderTime, :cid, :cardId, :addressId, :courierId, (select delivery_status_id from delivery_status where delivery_status_name='assigned_to_shipping'), :isContract)";

?>