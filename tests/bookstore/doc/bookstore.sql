set foreign_key_checks=0;

DROP DATABASE IF EXISTS `bookstore`;
CREATE DATABASE IF NOT EXISTS `bookstore` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_polish_ci */;
USE `bookstore`;

DROP TABLE IF EXISTS `authors`;
CREATE TABLE IF NOT EXISTS `authors` (
  `author_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_polish_ci,
  `birth_date` date DEFAULT NULL,
  PRIMARY KEY (`author_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

DELETE FROM `authors`;
INSERT INTO `authors` (`author_id`, `first_name`, `last_name`, `birth_date`) VALUES
	(1, 'Matthew ', 'Normani', '1982-06-06'),
	(2, 'Roji', 'Normani', '1977-03-15'),
	(3, 'Anna', 'Kowalska', '1999-05-15');

DROP TABLE IF EXISTS `authors_addresses`;
CREATE TABLE IF NOT EXISTS `authors_addresses` (
  `author_id` int(10) unsigned NOT NULL,
  `type` int(10) unsigned NOT NULL,
  `street_prefix` int(10) unsigned DEFAULT NULL,
  `street` varchar(50) COLLATE utf8_polish_ci DEFAULT NULL,
  `country` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`author_id`,`type`),
  KEY `authors_addresses_street_prefix` (`street_prefix`),
  KEY `authors_addresses_type` (`type`),
  KEY `authors_addresses_country` (`country`),
  CONSTRAINT `authors_addresses_author` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`),
  CONSTRAINT `authors_addresses_country` FOREIGN KEY (`country`) REFERENCES `dictionary_values` (`id`),
  CONSTRAINT `authors_addresses_street_prefix` FOREIGN KEY (`street_prefix`) REFERENCES `dictionary_values` (`id`),
  CONSTRAINT `authors_addresses_type` FOREIGN KEY (`type`) REFERENCES `dictionary_values` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

DELETE FROM `authors_addresses`;
INSERT INTO `authors_addresses` (`author_id`, `type`, `street_prefix`, `street`, `country`) VALUES
	(1, 2, 3, '1 Chapel Hill', 4),
	(2, 2, 3, '56/45 Apel Mill', 4),
	(3, 1, 3, '45/23 Apply', 5);

DROP TABLE IF EXISTS `books`;
CREATE TABLE IF NOT EXISTS `books` (
  `book_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) COLLATE utf8_polish_ci NOT NULL,
  `release_date` date DEFAULT NULL,
  `format_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`book_id`),
  KEY `books_format` (`format_id`),
  CONSTRAINT `books_format` FOREIGN KEY (`format_id`) REFERENCES `dictionary_values` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

DELETE FROM `books`;
INSERT INTO `books` (`book_id`, `name`, `release_date`, `format_id`) VALUES
	(1, 'Learning PHP, MySQL & JavaScript: With jQuery, CSS & HTML5', NULL, NULL),
	(2, 'We\'re All Damaged', NULL, NULL),
	(3, 'JavaScript and JQuery: Interactive Front-End Web Development', '2016-06-06', NULL);


-- Zrzut struktury tabela bookstore.books_authors
DROP TABLE IF EXISTS `books_authors`;
CREATE TABLE IF NOT EXISTS `books_authors` (
  `book_id` int(10) unsigned NOT NULL,
  `author_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`book_id`,`author_id`),
  KEY `books_authors_author` (`author_id`),
  CONSTRAINT `books_authors_author` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`),
  CONSTRAINT `books_authors_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

DELETE FROM `books_authors`;
INSERT INTO `books_authors` (`book_id`, `author_id`) VALUES
	(1, 1),
	(2, 1),
	(3, 1),
	(2, 2),
	(3, 3);

DROP TABLE IF EXISTS `books_categories`;
CREATE TABLE IF NOT EXISTS `books_categories` (
  `book_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`book_id`,`category_id`),
  KEY `books_categories_category` (`category_id`),
  CONSTRAINT `books_categories_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`),
  CONSTRAINT `books_categories_category` FOREIGN KEY (`category_id`) REFERENCES `dictionary_books_categories` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

DELETE FROM `books_categories`;
INSERT INTO `books_categories` (`book_id`, `category_id`) VALUES
	(1, 1),
	(3, 1),
	(2, 2),
	(3, 3);

DROP TABLE IF EXISTS `books_opinions`;
CREATE TABLE IF NOT EXISTS `books_opinions` (
  `opinion_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `book_id` int(10) unsigned NOT NULL,
  `opinion` varchar(1024) COLLATE utf8_polish_ci NOT NULL,
  `author` varchar(50) COLLATE utf8_polish_ci DEFAULT NULL,
  PRIMARY KEY (`opinion_id`),
  KEY `books_opinions_book` (`book_id`),
  CONSTRAINT `books_opinions_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

DELETE FROM `books_opinions`;
INSERT INTO `books_opinions` (`opinion_id`, `book_id`, `opinion`, `author`) VALUES
	(1, 1, 'I think this book would serve a beginning coder well.', 'Mill'),
	(2, 1, 'One of the best-written books I\'ve come across in many years. Its amazing how 3 major dev environments could be covered so thoroughly in 1 book', 'Mike'),
	(3, 2, 'Everyone has their history and quirks.', NULL),
	(4, 2, ' Andy is divorced and living a seemingly meaningless life in the big city', 'Jula'),
	(5, 2, ' A trip back home to the Midwest is not in his plans', 'Milka');

DROP TABLE IF EXISTS `dictionaries`;
CREATE TABLE IF NOT EXISTS `dictionaries` (
  `dictionary_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  `system_name` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  PRIMARY KEY (`dictionary_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

DELETE FROM `dictionaries`;
INSERT INTO `dictionaries` (`dictionary_id`, `name`, `system_name`) VALUES
	(1, 'Address types', 'address_types'),
	(2, 'Street prefix', 'street_prefix'),
	(3, 'Countries', 'countries');

DROP TABLE IF EXISTS `dictionary_books_categories`;
CREATE TABLE IF NOT EXISTS `dictionary_books_categories` (
  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

DELETE FROM `dictionary_books_categories`;
INSERT INTO `dictionary_books_categories` (`category_id`, `name`) VALUES
	(1, 'IT'),
	(2, 'Life'),
	(3, 'Front-end');

DROP TABLE IF EXISTS `dictionary_values`;
CREATE TABLE IF NOT EXISTS `dictionary_values` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dictionary_id` int(10) unsigned NOT NULL,
  `value` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dictionary_values_dictionary` (`dictionary_id`),
  CONSTRAINT `dictionary_values_dictionary` FOREIGN KEY (`dictionary_id`) REFERENCES `dictionaries` (`dictionary_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

DELETE FROM `dictionary_values`;
INSERT INTO `dictionary_values` (`id`, `dictionary_id`, `value`) VALUES
	(1, 1, 'business'),
	(2, 1, 'correspondence'),
	(3, 2, 'Road'),
	(4, 3, 'Poland'),
	(5, 3, 'USA');

DROP TABLE IF EXISTS `shops`;
CREATE TABLE IF NOT EXISTS `shops` (
  `shop_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  PRIMARY KEY (`shop_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

DELETE FROM `shops`;
INSERT INTO `shops` (`shop_id`, `name`) VALUES
	(1, 'NY ST'),
	(2, 'CHILL Mount');

DROP TABLE IF EXISTS `shops_books`;
CREATE TABLE IF NOT EXISTS `shops_books` (
  `shop_id` int(10) unsigned NOT NULL,
  `book_id` int(10) unsigned NOT NULL,
  `price` float NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  PRIMARY KEY (`shop_id`,`book_id`),
  KEY `shops_books_book` (`book_id`),
  CONSTRAINT `shops_books_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`),
  CONSTRAINT `shops_books_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

DELETE FROM `shops_books`;
INSERT INTO `shops_books` (`shop_id`, `book_id`, `price`, `amount`) VALUES
	(1, 1, 37.82, 2),
	(1, 2, 50.55, 10),
	(1, 3, 35, 10),
	(2, 2, 50, 5),
	(2, 3, 30.78, 2);


DROP TABLE IF EXISTS `warehouse`;
CREATE TABLE IF NOT EXISTS `warehouse` (
  `book_id` int(10) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  PRIMARY KEY (`book_id`),
  CONSTRAINT `warehouse_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

DELETE FROM `warehouse`;
INSERT INTO `warehouse` (`book_id`, `amount`) VALUES
	(1, 2),
	(2, 10),
	(3, 11);

DROP TABLE IF EXISTS `authors_addresses_changes`;
CREATE TABLE IF NOT EXISTS `authors_addresses_changes` (
  `author_id` int(10) unsigned NOT NULL,
  `type` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`author_id`,`type`,`date`),
  KEY `authors_addresses_changes_type` (`type`),
  CONSTRAINT `authors_addresses_changes_author` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`),
  CONSTRAINT `authors_addresses_changes_type` FOREIGN KEY (`type`) REFERENCES `dictionary_values` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

DELETE FROM `authors_addresses_changes`;
INSERT INTO `authors_addresses_changes` (`author_id`, `type`, `date`) VALUES
	(1, 2, '2016-06-21 14:43:41'),
	(1, 2, '2016-06-21 14:43:56'),
	(1, 2, '2016-06-21 14:44:04');

