create table RESTAURANTS
(
    RestaurantID int unsigned auto_increment
        primary key,
    Name         varchar(255)         not null,
    Email        varchar(255)         not null,
    Phone        varchar(255)         not null,
    Address      varchar(255)         not null,
    Password     varchar(255)         not null,
    isAdmin      tinyint(1) default 0 not null,
    Summary      text                 ,
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
INSERT INTO RESTAURANTS (RestaurantID, Name, Email, Phone, Address, Password, isAdmin) VALUES (1, 'root@root.root', 'root', '30624770', 'root', '$2y$10$UI9sFGavUq5dmcJnGnz9xO8vcgqJRgg6Wztpw2Zn.y7lkjC3ZxiOK', 1);