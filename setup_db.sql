CREATE TABLE `user` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `pass` CHAR(40),
    `role` TINYINT
) ENGINE=MyISAM CHARSET=utf8;
