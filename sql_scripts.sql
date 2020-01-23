--Relations for the project


CREATE TABLE address (
    address_id   NUMERIC PRIMARY KEY,
    street       VARCHAR(255) NOT NULL,
    apartment    VARCHAR(255) NOT NULL,
    zip          NUMERIC NOT NULL
);

CREATE TABLE store (
    store_id     NUMERIC PRIMARY KEY,
    store_name   VARCHAR(255),
    region       VARCHAR(255),
    zip          NUMERIC
);

CREATE TABLE customer (
    c_id            NUMERIC PRIMARY KEY,
    email           VARCHAR(255),
    first_name      VARCHAR(255),
    last_name       VARCHAR(255),
    phone           NUMERIC,
    password        VARCHAR(255),
    default_store   NUMERIC,
    FOREIGN KEY ( default_store )
        REFERENCES store ( store_id )
);

CREATE TABLE customer_address (
    c_id         NUMERIC,
    address_id   NUMERIC,
    PRIMARY KEY ( c_id,
                  address_id ),
    FOREIGN KEY ( c_id )
        REFERENCES customer,
    FOREIGN KEY ( address_id )
        REFERENCES address
);

CREATE TABLE login (
    c_id         NUMERIC,
    login_time   TIMESTAMP,
    PRIMARY KEY ( c_id,
                  login_time ),
    FOREIGN KEY ( c_id )
        REFERENCES customer ( c_id )
);

CREATE TABLE zip_code (
    zip     NUMERIC PRIMARY KEY,
    state   VARCHAR(255) NOT NULL,
    city    VARCHAR(255) NOT NULL
);


CREATE TABLE category (
    category_id     NUMERIC PRIMARY KEY,
    category_name   VARCHAR(255) UNIQUE NOT NULL,
    parent_id       NUMERIC,
    FOREIGN KEY ( parent_id )
        REFERENCES category ( category_id )
);

CREATE TABLE manufacturer (
    manufacturer_id     NUMERIC PRIMARY KEY,
    manufacturer_name   VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE product (
    p_id              NUMERIC PRIMARY KEY,
    list_name         VARCHAR(255) NOT NULL,
    manufacturer_id   NUMERIC NOT NULL,
    category_id       NUMERIC NOT NULL,
    image_name        VARCHAR(255),
    FOREIGN KEY ( manufacturer_id )
        REFERENCES manufacturer ( manufacturer_id ),
    FOREIGN KEY ( category_id )
        REFERENCES category ( category_id )
);

CREATE TABLE inventory (
    p_id            NUMERIC,
    store_id        NUMERIC,
    quantity        INT,
    product_price   NUMERIC,
    PRIMARY KEY ( p_id,
                  store_id ),
    FOREIGN KEY ( p_id )
        REFERENCES product ( p_id ),
    FOREIGN KEY ( store_id )
        REFERENCES store ( store_id )
);

CREATE TABLE cards (
    card_id           NUMERIC PRIMARY KEY,
    card_number       NUMERIC UNIQUE,
    expiry            DATE,
    credit_or_debit   VARCHAR(1),
    credit_balance    NUMERIC,
    name_on_card      VARCHAR(255)
);

CREATE TABLE customer_cards (
    c_id      NUMERIC,
    card_id   NUMERIC,
    PRIMARY KEY ( c_id,
                  card_id ),
    FOREIGN KEY ( c_id )
        REFERENCES customer,
    FOREIGN KEY ( card_id )
        REFERENCES cards
);

CREATE TABLE courier (
    courier_id           NUMERIC PRIMARY KEY,
    courier_name         VARCHAR(255),
    zip                  NUMERIC,
    estimated_delivery   NUMERIC,
    delivery_fee         NUMERIC
);

CREATE TABLE contract (
    account_name     VARCHAR(255),
    account_number   NUMERIC,
    c_id             NUMERIC PRIMARY KEY,
    FOREIGN KEY ( c_id )
        REFERENCES customer
);

CREATE TABLE order_types (
    order_type_id   NUMERIC PRIMARY KEY,
    order_type      VARCHAR(255)
);

CREATE TABLE delivery_status (
    delivery_status_id     NUMERIC PRIMARY KEY,
    delivery_status_name   VARCHAR(255)
);


CREATE TABLE order_details (
    order_id          NUMERIC PRIMARY KEY,
    store_id          NUMERIC,
    order_type        INT,
    order_time        TIMESTAMP,
    courier_id        NUMERIC,
    c_id              NUMERIC,
    tracking_id       NUMERIC,
    delivery_status   NUMERIC,
    address_id        NUMERIC,
    card              NUMERIC,
    is_contract       VARCHAR(1),
    FOREIGN KEY ( store_id )
        REFERENCES store,
    FOREIGN KEY ( order_type )
        REFERENCES order_types ( order_type_id ),
    FOREIGN KEY ( courier_id )
        REFERENCES courier,
    FOREIGN KEY ( c_id )
        REFERENCES customer,
    FOREIGN KEY ( delivery_status )
        REFERENCES delivery_status ( delivery_status_id ),
    FOREIGN KEY ( address_id )
        REFERENCES address,
    FOREIGN KEY ( card )
        REFERENCES cards( card_id )
);

CREATE TABLE orders (
    order_id     NUMERIC,
    p_id         NUMERIC,
    quantity     INT,
    unit_price   NUMERIC,
    PRIMARY KEY ( order_id,
                  p_id ),
    FOREIGN KEY ( p_id )
        REFERENCES product,
    FOREIGN KEY ( order_id )
        REFERENCES order_details
);

CREATE TABLE cart (
    p_id       NUMERIC,
    c_id       NUMERIC,
    quantity   INT,
    PRIMARY KEY ( p_id,
                  c_id ),
    FOREIGN KEY ( p_id )
        REFERENCES product,
    FOREIGN KEY ( c_id )
        REFERENCES customer
);

CREATE TABLE reorders (
    store_id     NUMERIC,
    p_id         NUMERIC,
    order_time   TIMESTAMP PRIMARY KEY,
    quantity     INT NOT NULL,
    status       NUMERIC,
    FOREIGN KEY ( store_id,
                  p_id )
        REFERENCES inventory,
    FOREIGN KEY ( status )
        REFERENCES delivery_status ( delivery_status_id )
);

CREATE TABLE ticket (
    order_id   NUMERIC,
    status     VARCHAR(255),
    PRIMARY KEY ( order_id,
                  status ),
    FOREIGN KEY ( order_id )
        REFERENCES order_details
);

--Sequences were created for auto increment of indices in tables

CREATE SEQUENCE order_id_seq;

CREATE SEQUENCE courier_id_seq;

CREATE SEQUENCE card_id_seq;

CREATE SEQUENCE cust_id_seq;

CREATE SEQUENCE store_id_seq;

CREATE SEQUENCE category_id_seq;

CREATE SEQUENCE manufacturer_id_seq;

CREATE SEQUENCE product_id_seq;

CREATE SEQUENCE address_id_seq;

--This is a special insert as sequence would normally start from 1 and would automatically get inserted through triggers
--We are inserting a row with c_id = 0 to represent guest customer who order without logging in

insert into customer(c_id) values (0);

--Triggers were created for auto increment of the primary keys in some tables. Oracle 12g supports Identity columns and we donot need triggers for the auto-increment but Oracle 11g doesnot support it and hence we created these triggers

CREATE TRIGGER category_before_insert BEFORE
    INSERT ON category
    FOR EACH ROW
BEGIN
    SELECT
        category_id_seq.NEXTVAL
    INTO :new.category_id
    FROM
        dual;

END;
/

CREATE TRIGGER manufacturer_before_insert BEFORE
    INSERT ON manufacturer
    FOR EACH ROW
BEGIN
    SELECT
        manufacturer_id_seq.NEXTVAL
    INTO :new.manufacturer_id
    FROM
        dual;

END;
/

CREATE TRIGGER product_before_insert BEFORE
    INSERT ON product
    FOR EACH ROW
BEGIN
    SELECT
        product_id_seq.NEXTVAL
    INTO :new.p_id
    FROM
        dual;

END;
/

CREATE TRIGGER address_before_insert BEFORE
    INSERT ON address
    FOR EACH ROW
BEGIN
    SELECT
        address_id_seq.NEXTVAL
    INTO :new.address_id
    FROM
        dual;

END;
/

CREATE TRIGGER customer_before_insert BEFORE
    INSERT ON customer
    FOR EACH ROW
BEGIN
    SELECT
        cust_id_seq.NEXTVAL
    INTO :new.c_id
    FROM
        dual;

END;
/

CREATE TRIGGER store_before_insert BEFORE
    INSERT ON store
    FOR EACH ROW
BEGIN
    SELECT
        store_id_seq.NEXTVAL
    INTO :new.store_id
    FROM
        dual;

END;
/

CREATE TRIGGER cards_before_insert BEFORE
    INSERT ON cards
    FOR EACH ROW
BEGIN
    SELECT
        card_id_seq.NEXTVAL
    INTO :new.card_id
    FROM
        dual;

END;
/

CREATE TRIGGER courier_before_insert BEFORE
    INSERT ON courier
    FOR EACH ROW
BEGIN
    SELECT
        courier_id_seq.NEXTVAL
    INTO :new.courier_id
    FROM
        dual;

END;
/

--The below trigger is a special trigger implemented for REORDERS
--Whenever quantity falls below 10, we automatically initiate a reorder of quantity 100 for that item

CREATE TRIGGER reorder_trigger AFTER
    UPDATE OF quantity ON inventory
    FOR EACH ROW
DECLARE
    deliverystatus NUMERIC;
BEGIN
    SELECT
        delivery_status_id
    INTO deliverystatus
    FROM
        delivery_status
    WHERE
        delivery_status_name = 'assigned_to_shipping';

    IF :old.quantity >= 10 AND :new.quantity < 10 THEN
        INSERT INTO reorders VALUES (
            :old.store_id,
            :old.p_id,
            current_timestamp,
            100,
            deliverystatus
        );

    END IF;

END;
/

--The below is a special trigger for updating the inventory quantity once the reorder gets delivered

CREATE TRIGGER store_quantity_trigger AFTER
    UPDATE OF status ON reorders
    FOR EACH ROW
DECLARE
    deliveredstatus NUMERIC;
BEGIN
    SELECT
        delivery_status_id
    INTO deliveredstatus
    FROM
        delivery_status
    WHERE
        delivery_status_name = 'delivered';

    IF :new.status IN (
        deliveredstatus
    ) AND :old.status != deliveredstatus THEN
        UPDATE inventory
        SET
            quantity = :new.quantity + :old.quantity
        WHERE
            p_id = :old.p_id
            AND store_id = :old.store_id;

    END IF;

END;
/

--Some insert scripts that we executed for testing

INSERT INTO store (
    store_name,
    region,
    zip
) VALUES (
    'Roosevelt Ave',
    'IL',
    60616
);

INSERT INTO store (
    store_name,
    region,
    zip
) VALUES (
    '1000 W North Ave',
    'IL',
    60616
);

INSERT INTO category ( category_name ) VALUES ( 'camera' );

INSERT INTO category (
    category_name,
    parent_id
) VALUES (
    'dslr',
    (
        SELECT
            category_id
        FROM
            category
        WHERE
            category_name = 'camera'
    )
);

INSERT INTO category (
    category_name,
    parent_id
) VALUES (
    'point and shoot',
    (
        SELECT
            category_id
        FROM
            category
        WHERE
            category_name = 'camera'
    )
);

INSERT INTO manufacturer ( manufacturer_name ) VALUES ( 'Canon' );

INSERT INTO manufacturer ( manufacturer_name ) VALUES ( 'Fujifilm' );

INSERT INTO product (
    list_name,
    category_id,
    manufacturer_id,
    image_name
) VALUES (
    'Canon - EOS 5D Mark IV DSLR Camera (Body Only) - Black',
    (
        SELECT
            category_id
        FROM
            category
        WHERE
            category_name = 'dslr'
    ),
    (
        SELECT
            manufacturer_id
        FROM
            manufacturer
        WHERE
            manufacturer_name = 'Canon'
    ),
    '1.jpg'
);


INSERT INTO product (
    list_name,
    category_id,
    manufacturer_id,
    image_name
) VALUES (
    'Fujifilm - instax mini 9 Instant Film Camera Bundle - Smokey Purple',
    (
        SELECT
            category_id
        FROM
            category
        WHERE
            category_name = 'point and shoot'
    ),
    (
        SELECT
            manufacturer_id
        FROM
            manufacturer
        WHERE
            manufacturer_name = 'Fujifilm'
    ),
    '2.jpg'
);

INSERT INTO category (
    category_name,
    parent_id
) VALUES (
    'computer',
    NULL
);

INSERT INTO category (
    category_name,
    parent_id
) VALUES (
    'desktop',
    4
);

INSERT INTO category (
    category_name,
    parent_id
) VALUES (
    'laptop',
    4
);

INSERT INTO category (
    category_name,
    parent_id
) VALUES (
    'computer accessories',
    4
);

INSERT INTO category (
    category_name,
    parent_id
) VALUES (
    'phones',
    NULL
);

INSERT INTO category (
    category_name,
    parent_id
) VALUES (
    'android',
    9
);

INSERT INTO category (
    category_name,
    parent_id
) VALUES (
    'ios',
    9
);

INSERT INTO category (
    category_name,
    parent_id
) VALUES (
    'printer',
    NULL
);

INSERT INTO category (
    category_name,
    parent_id
) VALUES (
    'all-in-one',
    12
);

INSERT INTO category (
    category_name,
    parent_id
) VALUES (
    'game consoles',
    NULL
);

INSERT INTO category (
    category_name,
    parent_id
) VALUES (
    'xbox',
    14
);

INSERT INTO category (
    category_name,
    parent_id
) VALUES (
    'ps4',
    14
);

INSERT INTO manufacturer ( manufacturer_name ) VALUES ( 'Apple' );

INSERT INTO manufacturer ( manufacturer_name ) VALUES ( 'Microsoft' );

INSERT INTO manufacturer ( manufacturer_name ) VALUES ( 'Dell' );

INSERT INTO manufacturer ( manufacturer_name ) VALUES ( 'HP' );

INSERT INTO manufacturer ( manufacturer_name ) VALUES ( 'WD' );

INSERT INTO manufacturer ( manufacturer_name ) VALUES ( 'Samsung' );

INSERT INTO manufacturer ( manufacturer_name ) VALUES ( 'Epson' );

INSERT INTO manufacturer ( manufacturer_name ) VALUES ( 'Activision' );

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'Nikon - Z7 Mirrorless Camera with NIKKOR Z 24-70mm Lens - Black',
    3,
    2,
    '3.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'Sony - Cyber-shot RX100 IV 20.1-Megapixel Digital Camera - Black',
    4,
    3,
    '4.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'Apple - MacBook Pro - 16" Display with Touch Bar - Intel Core i7 - 16GB Memory - AMD Radeon Pro 5300M - 512GB SSD (Latest Model) - Space Gray'
    ,
    5,
    6,
    '5.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'Microsoft - Surface Book 2 - 13.5" Touch-Screen PixelSense™ - 2-in-1 Laptop - Intel Core i5 - 8GB Memory - 256GB SSD - Platinum'
    ,
    6,
    6,
    '6.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'Dell - Inspiron 23.8" Touch-Screen All-In-One - AMD A9-Series - 8GB Memory - 256GB Solid State Drive - Black',
    7,
    5,
    '7.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'HP - Pavilion 23.8" Touch-Screen All-In-One - AMD Ryzen 5-Series - 8GB Memory - 256GB Solid State Drive - Snowflake White',
    8,
    5,
    '8.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'WD - Easystore 5TB External USB 3.0 Portable Hard Drive - Black',
    9,
    8,
    '9.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'Apple - Magic Mouse 2 - Silver',
    5,
    8,
    '10.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'Samsung - CHG9 Series C49HG90DMN 49" HDR LED Curved FHD FreeSync Monitor - Matte dark blue black',
    10,
    8,
    '11.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'Samsung - Galaxy S10+ with 128GB Memory Cell Phone (Unlocked) Prism - White',
    10,
    10,
    '12.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'Apple - iPhone 11 with 64GB Memory Cell Phone (Unlocked) - Black',
    5,
    11,
    '13.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'HP - ENVY 5055 All-in-One Instant Ink Ready Printer - Black',
    8,
    13,
    '14.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'Epson - WorkForce Pro WF-3720 Wireless All-In-One Printer - Black',
    11,
    13,
    '15.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'Microsoft - Xbox One S 1TB Star Wars Jedi: Fallen Order™ Deluxe Edition Console Bundle',
    6,
    15,
    '16.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'Call of Duty: Modern Warfare Standard Edition - Xbox One',
    12,
    15,
    '17.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'Sony - PlayStation 4 1TB Console - Black',
    4,
    16,
    '18.jpg'
);

INSERT INTO product (
    list_name,
    manufacturer_id,
    category_id,
    image_name
) VALUES (
    'God of War - PlayStation Hits Standard Edition - PlayStation 4',
    4,
    16,
    '19.jpg'
);

INSERT INTO inventory VALUES (
    4,
    1,
    15,
    2499
);

INSERT INTO inventory VALUES (
    5,
    1,
    129,
    1999
);

INSERT INTO inventory VALUES (
    6,
    1,
    19,
    3199
);

INSERT INTO inventory VALUES (
    7,
    1,
    27,
    1349
);

INSERT INTO inventory VALUES (
    9,
    1,
    32,
    849
);

INSERT INTO inventory VALUES (
    10,
    1,
    11,
    349
);

INSERT INTO inventory VALUES (
    11,
    1,
    25,
    69
);

INSERT INTO inventory VALUES (
    12,
    1,
    29,
    129
);

INSERT INTO inventory VALUES (
    14,
    1,
    785,
    1099
);

INSERT INTO inventory VALUES (
    15,
    1,
    100,
    39
);

INSERT INTO inventory VALUES (
    16,
    1,
    100,
    45
);

INSERT INTO inventory VALUES (
    17,
    1,
    50,
    249
);

INSERT INTO inventory VALUES (
    19,
    1,
    15,
    279
);

INSERT INTO inventory VALUES (
    20,
    1,
    50,
    8.99
);

INSERT INTO inventory VALUES (
    4,
    2,
    25,
    2449
);

INSERT INTO inventory VALUES (
    5,
    2,
    19,
    1899
);

INSERT INTO inventory VALUES (
    6,
    2,
    80,
    3299
);

INSERT INTO inventory VALUES (
    7,
    2,
    37,
    1349
);

INSERT INTO inventory VALUES (
    8,
    2,
    15,
    949
);

INSERT INTO inventory VALUES (
    9,
    2,
    20,
    849
);

INSERT INTO inventory VALUES (
    10,
    2,
    12,
    349
);

INSERT INTO inventory VALUES (
    11,
    2,
    115,
    69
);

INSERT INTO inventory VALUES (
    12,
    2,
    29,
    129
);

INSERT INTO inventory VALUES (
    13,
    2,
    25,
    999
);

INSERT INTO inventory VALUES (
    14,
    2,
    25,
    1099
);

INSERT INTO inventory VALUES (
    15,
    2,
    102,
    39
);

INSERT INTO inventory VALUES (
    17,
    2,
    500,
    199
);

INSERT INTO inventory VALUES (
    18,
    2,
    210,
    8.99
);

INSERT INTO inventory VALUES (
    19,
    2,
    125,
    279
);

INSERT INTO inventory VALUES (
    20,
    2,
    150,
    8.99
);

INSERT INTO address (
    street,
    apartment,
    zip
) VALUES (
    '2851 S King Dr',
    '1810',
    60616
);

INSERT INTO customer_address (
    c_id,
    address_id
) VALUES (
    43,
    6
);

INSERT INTO customer_address (
    c_id,
    address_id
) VALUES (
    43,
    7
);

INSERT INTO zip_code (
    zip,
    state,
    city
) VALUES (
    60616,
    'chicago',
    'illinois'
);

INSERT INTO customer_cards (
    c_id,
    card_id
) VALUES (
    41,
    4
);

INSERT INTO customer_cards (
    c_id,
    card_id
) VALUES (
    43,
    6
);

INSERT INTO cards (
    card_number,
    expiry,
    credit_or_debit,
    name_on_card
) VALUES (
    :card_number,
    :expiry,
    :credit_or_debit,
    :name_on_card
);

INSERT INTO zip_code (
    zip,
    state,
    city
) VALUES (
    :zip,
    :state,
    :city
);

INSERT INTO address (
    street,
    apartment,
    zip
) VALUES (
    :street,
    :apartment,
    :zip
);

SELECT
    address_id
FROM
    address
WHERE
    street = :street
    AND apartment = :apartment
    AND zip = :zip;

INSERT INTO customer_address (
    c_id,
    address_id
) VALUES (
    :cid,
    :address_id
);

INSERT INTO courier (
    courier_name,
    zip,
    delivery_fee
) VALUES (
    'USPS',
    60617,
    25
);

INSERT INTO inventory (
    p_id,
    store_id,
    quantity,
    product_price
) VALUES (
    1,
    1,
    100,
    2500
);

INSERT INTO inventory (
    p_id,
    store_id,
    quantity,
    product_price
) VALUES (
    2,
    1,
    200,
    150.50
);
INSERT INTO inventory (
    p_id,
    store_id,
    quantity,
    product_price
) VALUES (
    2,
    2,
    200,
    200
);



UPDATE cards
SET
    name_on_card = 'john'
WHERE
    card_id = 3;

SELECT
    *
FROM
    v$nls_parameters;

INSERT INTO cards (
    card_number,
    expiry,
    credit_or_debit,
    credit_balance
) VALUES (
    5459648902709063,
    '30-Jun-20',
    'c',
    70000
);

INSERT INTO cards (
    card_number,
    expiry,
    credit_or_debit,
    credit_balance
) VALUES (
    5459640902409063,
    '31-Jul-22',
    'c',
    10000
);

INSERT INTO cards (
    card_number,
    expiry,
    credit_or_debit,
    credit_balance
) VALUES (
    2659648902709063,
    '30-Jun-20',
    'd',
    NULL
);

INSERT INTO customer_cards (
    c_id,
    card_id
) VALUES (
    43,
    1
);

INSERT INTO customer_cards (
    c_id,
    card_id
) VALUES (
    43,
    2
);

INSERT INTO customer_cards (
    c_id,
    card_id
) VALUES (
    43,
    3
);

INSERT INTO courier (
    courier_name,
    zip,
    estimated_delivery,
    delivery_fee
) VALUES (
    'USPS',
    60616,
    10,
    NULL
);

INSERT INTO courier (
    courier_name,
    zip,
    estimated_delivery,
    delivery_fee
) VALUES (
    'FedEx',
    60616,
    2,
    125.55
);

INSERT INTO courier (
    courier_name,
    zip,
    estimated_delivery,
    delivery_fee
) VALUES (
    'DHL',
    60616,
    5,
    69.99
);

INSERT INTO order_types VALUES (
    1,
    'in_store'
);

INSERT INTO order_types VALUES (
    2,
    'delivery'
);

INSERT INTO delivery_status VALUES (
    1,
    'assigned_to_shipping'
);

INSERT INTO delivery_status VALUES (
    2,
    'shipped'
);

INSERT INTO delivery_status VALUES (
    3,
    'out_for_delivery'
);

INSERT INTO delivery_status VALUES (
    4,
    'delivered'
); 

