CREATE USER 'optatio_user'@'localhost' IDENTIFIED BY 'password';
CREATE USER 'optatio_user'@'%' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON * . * TO 'optatio_user'@'%';
GRANT ALL PRIVILEGES ON * . * TO 'optatio_user'@'localhost';
FLUSH PRIVILEGES;
CREATE DATABASE optatio;