-- Run this script once to create the database and user to be
-- used with this application. Be careful -- running it again
-- will delete everything in the database!

USE sys;
DROP DATABASE debedu;
CREATE DATABASE debedu;
USE debedu;

DROP USER debedu;
CREATE USER debedu IDENTIFIED BY 'DebEduServer1';
GRANT SELECT, INSERT, UPDATE on debedu.* TO debedu;
