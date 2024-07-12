create table RESTAURANTS
(
    RestaurantID int unsigned auto_increment
        primary key,
    Name         varchar(255)         not null,
    Email        varchar(255)         not null,
    Phone        varchar(255)         not null,
    Address      varchar(255)         not null,
    Password     varchar(255)         not null,
    Status       varchar(16)          not null,
    isAdmin      tinyint(1) default 0 not null,
    Summary      text                 not null,
    constraint Email
        unique (Email),
    constraint Name
        unique (Name),
    constraint Phone
        unique (Phone)
)
    collate = utf8mb4_general_ci;


create table DISH_CATEGORIES
(
    CategoryID   int unsigned auto_increment
        primary key,
    RestaurantID int unsigned                         not null,
    CategoryName varchar(255) default 'Uncategorised' not null,
    constraint RestaurantIDCategoryName
        unique (RestaurantID, CategoryName),
    constraint DISH_CATEGORIES_RestaurantID_foreign
        foreign key (RestaurantID) references RESTAURANTS (RestaurantID)
            on update cascade on delete cascade
)
    collate = utf8mb4_general_ci;


create table DISHES
(
    DishID      int unsigned auto_increment
        primary key,
    CategoryID  int unsigned   not null,
    DishName    varchar(255)   not null,
    Description text           not null,
    BasePrice   decimal(10, 2) not null,
    constraint RestaurantIDDishName
        unique (DishName),
    constraint DISHES_CategoryID_foreign
        foreign key (CategoryID) references DISH_CATEGORIES (CategoryID)
            on update cascade on delete set default
)
    collate = utf8mb4_general_ci;



create table CUSTOMISATION_OPTIONS
(
    OptionID     int unsigned auto_increment
        primary key,
    OptionName   varchar(255) not null,
    RestaurantID int unsigned not null,
    constraint RestaurantIDOptionName
        unique (RestaurantID, OptionName),
    constraint CUSTOMISATION_OPTIONS_RestaurantID_foreign
        foreign key (RestaurantID) references RESTAURANTS (RestaurantID)
            on update cascade on delete cascade
)
    collate = utf8mb4_general_ci;


create table OPTION_VALUES
(
    ValueID    int unsigned auto_increment
        primary key,
    OptionID   int unsigned   not null,
    ValueName  varchar(255)   not null,
    ExtraPrice decimal(10, 2) not null,
    constraint OptionIDValueName
        unique (OptionID, ValueName),
    constraint OPTION_VALUES_OptionID_foreign
        foreign key (OptionID) references CUSTOMISATION_OPTIONS (OptionID)
            on update cascade on delete cascade
)
    collate = utf8mb4_general_ci;



create table DISH_AVAILABLE_OPTIONS
(
    DishID   int unsigned not null,
    OptionID int unsigned not null,
    primary key (DishID, OptionID),
    constraint DISH_AVAILABLE_OPTIONS_DishID_foreign
        foreign key (DishID) references DISHES (DishID)
            on update cascade on delete cascade,
    constraint DISH_AVAILABLE_OPTIONS_OptionID_foreign
        foreign key (OptionID) references CUSTOMISATION_OPTIONS (OptionID)
            on update cascade on delete cascade
)
    collate = utf8mb4_general_ci;



create table TABLES
(
    TableID      int unsigned auto_increment
        primary key,
    RestaurantID int unsigned not null,
    TableNumber  varchar(16)  not null,
    Capacity     int unsigned not null,
    constraint RestaurantIDTableNumber
        unique (RestaurantID, TableNumber),
    constraint TABLES_RestaurantID_foreign
        foreign key (RestaurantID) references RESTAURANTS (RestaurantID)
            on update cascade on delete cascade
)
    collate = utf8mb4_general_ci;



create table ORDERS
(
    OrderID      int unsigned auto_increment
        primary key,
    RestaurantID int unsigned                                                                   not null,
    OrderNumber  int unsigned                                                                   not null,
    CustomerName varchar(255)                                                                   not null,
    TotalPrice   decimal(10, 2)                                                                 not null,
    OrderTime    timestamp                                            default CURRENT_TIMESTAMP not null,
    TableID      int unsigned                                                                   not null,
    Comment      text                                                                           null,
    Status       enum ('Pending', 'Making', 'Completed', 'Cancelled') default 'Pending'         not null,
    constraint ORDERS_RestaurantID_foreign
        foreign key (RestaurantID) references RESTAURANTS (RestaurantID)
            on update cascade on delete cascade,
    constraint ORDERS_TableID_foreign
        foreign key (TableID) references TABLES (TableID)
            on update cascade,
	constraint RestaurantIDOrderNumber
		unique (RestaurantID, OrderNumber)
)
    collate = utf8mb4_general_ci;



create table ORDER_DETAILS
(
    OrderDetailID int unsigned auto_increment
        primary key,
    OrderID       int unsigned not null,
    DishID        int unsigned not null,
    Quantity      int unsigned not null,
    constraint OrderIDOrderDetailIDDishID
        unique (OrderID, OrderDetailID, DishID),
    constraint ORDER_DETAILS_DishID_foreign
        foreign key (DishID) references DISHES (DishID)
            on update cascade,
    constraint ORDER_DETAILS_OrderID_foreign
        foreign key (OrderID) references ORDERS (OrderID)
            on update cascade on delete cascade
)
    collate = utf8mb4_general_ci;

create table ORDER_DETAIL_CUSTOMISATION_OPTIONS
(
    ValueID       int unsigned not null,
    OrderDetailID int unsigned not null,
    primary key (ValueID, OrderDetailID),
    constraint ORDER_DETAIL_CUSTOMISATION_OPTIONS_OrderDetailID_foreign
        foreign key (OrderDetailID) references ORDER_DETAILS (OrderDetailID)
            on update cascade on delete cascade,
    constraint ORDER_DETAIL_CUSTOMISATION_OPTIONS_ValueID_foreign
        foreign key (ValueID) references OPTION_VALUES (ValueID)
            on update cascade on delete cascade
)
    collate = utf8mb4_general_ci;


DELIMITER //

CREATE TRIGGER before_order_insert
        BEFORE INSERT ON ORDERS
        FOR EACH ROW
        BEGIN
            DECLARE nextOrderNumber INT DEFAULT 1;
            SELECT COALESCE(MAX(OrderNumber), 0) + 1 INTO nextOrderNumber FROM ORDERS WHERE RestaurantID = NEW.RestaurantID;
            SET NEW.OrderNumber = nextOrderNumber;
        END;
    
//

DELIMITER ;


-- User: root
-- Password: 8964
INSERT INTO RESTAURANTS (RestaurantID, Name, Email, Phone, Address, Password, isAdmin) VALUES (1, 'root', 'root', '30624770', 'root', '$2y$10$UI9sFGavUq5dmcJnGnz9xO8vcgqJRgg6Wztpw2Zn.y7lkjC3ZxiOK', 1);

INSERT INTO DISH_CATEGORIES (CategoryID, RestaurantID, CategoryName) VALUES (1, 1, 'Coffee');
INSERT INTO DISH_CATEGORIES (CategoryID, RestaurantID, CategoryName) VALUES (5, 1, 'Dessert');
INSERT INTO DISH_CATEGORIES (CategoryID, RestaurantID, CategoryName) VALUES (3, 1, 'Dim Sum');
INSERT INTO DISH_CATEGORIES (CategoryID, RestaurantID, CategoryName) VALUES (2, 1, 'Milk Tea');
INSERT INTO DISH_CATEGORIES (CategoryID, RestaurantID, CategoryName) VALUES (4, 1, 'Steamed Rice Noodle Roll');

INSERT INTO DISHES (DishID, CategoryID, DishName, Description, BasePrice) VALUES (1, 1, 'Latte', 'Latte', 6.00);
INSERT INTO DISHES (DishID, CategoryID, DishName, Description, BasePrice) VALUES (2, 2, 'Bubble Tea', 'Milk tea ', 8.00);
INSERT INTO DISHES (DishID, CategoryID, DishName, Description, BasePrice) VALUES (3, 4, 'Pork Cheung Fun', 'Cheung fun', 12.00);
INSERT INTO DISHES (DishID, CategoryID, DishName, Description, BasePrice) VALUES (4, 4, 'Beef Cheung Fun', 'Beef', 13.00);
INSERT INTO DISHES (DishID, CategoryID, DishName, Description, BasePrice) VALUES (6, 5, 'Lava Cake', 'It is very very very very very very very very very very very very very very delicious.', 6.00);

INSERT INTO CUSTOMISATION_OPTIONS (OptionID, OptionName, RestaurantID) VALUES (2, 'Milk', 1);
INSERT INTO CUSTOMISATION_OPTIONS (OptionID, OptionName, RestaurantID) VALUES (3, 'Soy Sauce', 1);
INSERT INTO CUSTOMISATION_OPTIONS (OptionID, OptionName, RestaurantID) VALUES (4, 'Sugar', 1);
INSERT INTO CUSTOMISATION_OPTIONS (OptionID, OptionName, RestaurantID) VALUES (1, 'Temperature', 1);

INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (11, 3, 'Regular', 0.00);
INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (22, 2, 'Almond Milk', 1.00);
INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (23, 2, 'Lactose-free Milk', 0.00);
INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (24, 2, 'Lite Milk', 0.00);
INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (25, 2, 'Oat Milk', 1.00);
INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (26, 2, 'Regular', 0.00);
INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (27, 4, 'Extra Sugar', 1.00);
INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (28, 4, 'Half Sugar', 0.00);
INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (29, 4, 'Less Sugar', 0.00);
INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (30, 4, 'No Sugar', 0.00);
INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (31, 4, 'Regular', 0.00);
INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (32, 1, 'Extra Hot', 0.00);
INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (33, 1, 'Hot', 0.00);
INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (34, 1, 'Ice', 1.00);
INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (35, 1, 'No Ice', 1.00);
INSERT INTO OPTION_VALUES (ValueID, OptionID, ValueName, ExtraPrice) VALUES (36, 1, 'Regular', 0.00);

INSERT INTO DISH_AVAILABLE_OPTIONS (DishID, OptionID) VALUES (1, 1);
INSERT INTO DISH_AVAILABLE_OPTIONS (DishID, OptionID) VALUES (2, 1);
INSERT INTO DISH_AVAILABLE_OPTIONS (DishID, OptionID) VALUES (1, 2);
INSERT INTO DISH_AVAILABLE_OPTIONS (DishID, OptionID) VALUES (2, 2);
INSERT INTO DISH_AVAILABLE_OPTIONS (DishID, OptionID) VALUES (3, 3);
INSERT INTO DISH_AVAILABLE_OPTIONS (DishID, OptionID) VALUES (4, 3);
INSERT INTO DISH_AVAILABLE_OPTIONS (DishID, OptionID) VALUES (6, 4);

