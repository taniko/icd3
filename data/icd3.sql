CREATE TABLE db(
id int auto_increment PRIMARY KEY,
name varchar(16)
);

CREATE TABLE asset(
id int auto_increment PRIMARY KEY,
db int,
name varchar(32) UNIQUE
);

CREATE TABLE arc_user(
id int auto_increment PRIMARY KEY,
ip varchar(16),
user_agent varchar(255),
date date
);


CREATE TABLE arc_log(
id int auto_increment PRIMARY KEY,
user_id int,
asset_id int,
date date,
time time
);

CREATE TABLE recommend(
id int auto_increment PRIMARY KEY,
parent int,
child int,
points float,
UNIQUE(parent, child)
);

CREATE TABLE user(
id int auto_increment PRIMARY KEY,
token varchar(256)
);

CREATE TABLE log(
id int auto_increment PRIMARY KEY,
user_id int,
asset_id int,
datetime datetime
);

CREATE TABLE info(
    asset_id int PRIMARY KEY,
    artist varchar(127),
    title varchar(255),
    cover varchar(255),
    FOREIGN KEY (asset_id) REFERENCES asset(id)
);

CREATE TABLE ignore_assets(
    user_id int UNSIGNED,
    asset_id int UNSIGNED,
    date date
);
