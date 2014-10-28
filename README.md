mvcblog
=======

A Simple Model-View-Controller Blog Example, for educational purposes


# Requirements
1. PHP 5.4.0
2. MySQL (tested in 5.5.40)
3. HTTP Server (tested in Apache 2)

# Database creation script
Connect to MySQL console and paste this script.
```sql
create database mvcblog;
use mvcblog;
create table users ( 
    username varchar(255), 
    passwd varchar(255), 
    primary key (username) 
) ENGINE=INNODB DEFAULT CHARACTER SET = utf8;

create table posts ( 
  id int auto_increment, 
  title varchar(255),
  content varchar(255), 
  author varchar(255), 

  primary key (id), 
  foreign key (author) references users(username)
) ENGINE=INNODB DEFAULT CHARACTER SET = utf8;

create table comments (
  id int auto_increment,   
  content varchar(255), 
  author varchar(255), 
  post int, 
  
  primary key (id),  
  foreign key (author) references users(username), 
  foreign key (post) references posts(id) on delete cascade
) ENGINE=INNODB DEFAULT CHARACTER SET = utf8;
```
# Create username for the database
Create a username for the database. The connection settings in the PHP code are in /core/PDOConnection.php
```sql
grant all privileges on mvcblog.* to mvcuser@localhost identified by "mvcblogpass";
```