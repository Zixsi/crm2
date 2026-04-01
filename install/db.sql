CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT , 
    `login` VARCHAR(100) NOT NULL , 
    `password` VARCHAR(255) NOT NULL , 
    PRIMARY KEY (`id`), 
    UNIQUE (`login`)
) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;

INSERT INTO `users` (`id`, `login`, `password`) VALUES
(1, 'manager', '$2y$12$V8INuqwguxmes7wXo0jtguIRUBFg4PLtdgIZT7CTgY.qQo4Gg2Vvi');

CREATE TABLE clients (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `contact_person` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50) NOT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'new',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (`status`)
);

CREATE TABLE client_notes (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `message` TEXT NOT NULL,
    `client_id` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (`client_id`),
    FOREIGN KEY (`client_id`) REFERENCES clients(`id`)
);

CREATE TABLE projects (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `client_id` INT UNSIGNED NOT NULL,
    `description` TEXT NOT NULL,
    `deleted` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    INDEX (`client_id`),
    INDEX (`deleted`, `end_date`),
    FOREIGN KEY (`client_id`) REFERENCES clients(`id`)
);

CREATE TABLE tasks (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `end_date` DATE NOT NULL,
    `project_id` INT UNSIGNED NOT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'new',
    `deleted` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    INDEX (`project_id`),
    INDEX (`deleted`),
    INDEX (`status`),
    FOREIGN KEY (`project_id`) REFERENCES projects(`id`)
);