-- MySQL Workbench Forward Engineering
-- Run this script to create the database and user to be used with this application.

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema DebEdu
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `DebEdu` DEFAULT CHARACTER SET utf8;
USE `DebEdu` ;

-- -----------------------------------------------------
-- Table `DebEdu`.`STUDENT_USER`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DebEdu`.`STUDENT_USER` (
  `USER_NUMBER` INT NOT NULL AUTO_INCREMENT,
  `USER_NAME` NVARCHAR(50) NOT NULL,
  `USER_PASSWORD` NVARCHAR(300) NOT NULL,
  `EMAIL` NVARCHAR(50) NOT NULL,
  `REAL_NAME` NVARCHAR(300) NOT NULL,
  PRIMARY KEY (`USER_NUMBER`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `DebEdu`.`TEACHER_USER`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DebEdu`.`TEACHER_USER` (
  `USER_NUMBER` INT NOT NULL AUTO_INCREMENT,
  `USER_NAME` NVARCHAR(50) NOT NULL,
  `USER_PASSWORD` NVARCHAR(300) NOT NULL,
  `EMAIL` NVARCHAR(50) NOT NULL,
  `REAL_NAME` NVARCHAR(300),
  PRIMARY KEY (`USER_NUMBER`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `DebEdu`.`ASSIGNMENT`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DebEdu`.`ASSIGNMENT` (
  `ASSIGNMENT_NUMBER` INT NOT NULL,
  `CLASS_NUMBER` INT NULL,
  `DATE_POSTED` DATETIME NULL,
  `DOWNLOADABLE` LONGBLOB NULL,
  PRIMARY KEY (`ASSIGNMENT_NUMBER`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `DebEdu`.`EDU_CLASS`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DebEdu`.`EDU_CLASS` (
  `CLASS_NUMBER` INT NOT NULL,
  `DESCRIPTION` NVARCHAR(512) NULL,
  `TITLE` NVARCHAR(100) NULL,
  `SEMESTER` ENUM('Fall 2023', 'Spring 2023', 'Summer 2023', 'Fall 2022', 'Spring 2022') DEFAULT NULL,
  `TEACHER_NUMBER` INT NOT NULL,
  PRIMARY KEY (`CLASS_NUMBER`),
  INDEX `fk_EDU_CLASS_TEACHER_USER1_idx` (`TEACHER_NUMBER` ASC) VISIBLE,
  CONSTRAINT `fk_EDU_CLASS_TEACHER_USER1`
    FOREIGN KEY (`TEACHER_NUMBER`)
    REFERENCES `DebEdu`.`TEACHER_USER` (`USER_NUMBER`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `DebEdu`.`ASSIGNMENT_FOR_CLASS`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DebEdu`.`ASSIGNMENT_FOR_CLASS` (
  `STUDENT_NUMBER` INT NOT NULL,
  `ASSIGNMENT_NUMBER` INT NOT NULL,
  `GRADE` INT NULL DEFAULT NULL,
  `SUBMISSION` LONGBLOB NULL,
  PRIMARY KEY (`STUDENT_NUMBER`, `ASSIGNMENT_NUMBER`),
  INDEX `fk_ASSIGNMENT_FOR_CLASS_STUDENT_USER1_idx` (`STUDENT_NUMBER` ASC) VISIBLE,
  INDEX `fk_ASSIGNMENT_FOR_CLASS_ASSIGNMENT1_idx` (`ASSIGNMENT_NUMBER` ASC) VISIBLE,
  CONSTRAINT `fk_ASSIGNMENT_FOR_CLASS_STUDENT_USER1`
    FOREIGN KEY (`STUDENT_NUMBER`)
    REFERENCES `DebEdu`.`STUDENT_USER` (`USER_NUMBER`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ASSIGNMENT_FOR_CLASS_ASSIGNMENT1`
    FOREIGN KEY (`ASSIGNMENT_NUMBER`)
    REFERENCES `DebEdu`.`ASSIGNMENT` (`ASSIGNMENT_NUMBER`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `DebEdu`.`STUDENT_IN_CLASS`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DebEdu`.`STUDENT_IN_CLASS` (
  `STUDENT_NUMBER` INT NOT NULL,
  `CLASS_NUMBER` INT NOT NULL,
  `GRADE` INT NULL,
  INDEX `fk_STUDENT_IN_CLASS_STUDENT_USER1_idx` (`STUDENT_NUMBER` ASC) VISIBLE,
  PRIMARY KEY (`STUDENT_NUMBER`, `CLASS_NUMBER`),
  INDEX `fk_STUDENT_IN_CLASS_EDU_CLASS1_idx` (`CLASS_NUMBER` ASC) VISIBLE,
  CONSTRAINT `fk_STUDENT_IN_CLASS_STUDENT_USER1`
    FOREIGN KEY (`STUDENT_NUMBER`)
    REFERENCES `DebEdu`.`STUDENT_USER` (`USER_NUMBER`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_STUDENT_IN_CLASS_EDU_CLASS1`
    FOREIGN KEY (`CLASS_NUMBER`)
    REFERENCES `DebEdu`.`EDU_CLASS` (`CLASS_NUMBER`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `DebEdu`.`MESSAGE`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DebEdu`.`MESSAGE` (
  `MESSAGE_NUMBER` INT NOT NULL AUTO_INCREMENT,
  `MESSAGE_TEXT` VARCHAR(2000) NOT NULL,
  `TIMESTAMP` VARCHAR(50) NOT NULL,
  `CLASS_NUMBER` INT NOT NULL,
  `STUDENT_USER_NUMBER` INT NULL,
  `TEACHER_USER_NUMBER` INT NULL,
  PRIMARY KEY (`MESSAGE_NUMBER`),
  INDEX `fk_MESSAGE_EDU_CLASS1_idx` (`CLASS_NUMBER` ASC) VISIBLE,
  INDEX `fk_MESSAGE_STUDENT_USER1_idx` (`STUDENT_USER_NUMBER` ASC) VISIBLE,
  INDEX `fk_MESSAGE_TEACHER_USER1_idx` (`TEACHER_USER_NUMBER` ASC) VISIBLE,
  CONSTRAINT `fk_MESSAGE_EDU_CLASS1`
    FOREIGN KEY (`CLASS_NUMBER`)
    REFERENCES `DebEdu`.`EDU_CLASS` (`CLASS_NUMBER`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_MESSAGE_STUDENT_USER1`
    FOREIGN KEY (`STUDENT_USER_NUMBER`)
    REFERENCES `DebEdu`.`STUDENT_USER` (`USER_NUMBER`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_MESSAGE_TEACHER_USER1`
    FOREIGN KEY (`TEACHER_USER_NUMBER`)
    REFERENCES `DebEdu`.`TEACHER_USER` (`USER_NUMBER`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB CHARSET utf8mb4;

CREATE USER IF NOT EXISTS 'DebEdu' IDENTIFIED BY 'DebEduService1';
GRANT SELECT, INSERT, UPDATE, TRIGGER, DELETE ON TABLE `DebEdu`.* TO 'DebEdu';

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

