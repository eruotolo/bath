INSERT INTO `category` (`id_category`, `name_category`) VALUES (3, 'SuperAdministrador') ON DUPLICATE KEY UPDATE `name_category` = VALUES(`name_category`);
