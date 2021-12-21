create database if not exists `myzoo`;

use `myzoo`;

create table if not exists `Person` (
    `PersonID` int primary key auto_increment,
    `Password` varchar(100),
    `Salt` varchar(100),
    `Username` varchar(100),
    `Token` varchar(100),
    `Zoobars` int default 10,
    `Profile` varchar(5000)
)engine = INNODB default charset = utf8;