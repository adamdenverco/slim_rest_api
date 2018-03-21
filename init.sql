CREATE TABLE IF NOT EXISTS `slimproject`.`users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_520_ci NULL,
  `token_expire` datetime DEFAULT NULL,
  `firstname` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `lastname` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `status` int(11) DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`username`),
  UNIQUE KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `slimproject`.`users` (`id`, `username`, `password`, `firstname`, `lastname`, `email`, `created`, `updated`) VALUES
(1, 'gwashington', 'e5f0f20b92f7022779015774e90ce917', 'George', 'Washington', 'gwashington@gmail.com', '2018-02-01 06:00:01', '2018-02-02 07:00:00'),
(2, 'jadams', 'e5f0f20b92f7022779015774e90ce917', 'John', 'Adams', 'jadams@gmail.com', '2018-02-01 06:00:01', '2018-02-02 07:00:00'),
(3, 'tjefferson', 'e5f0f20b92f7022779015774e90ce917', 'Thomas', 'jefferson', 'tjefferson@gmail.com', '2018-02-01 06:00:01', '2018-02-02 07:00:00'),
(4, 'jmadison', 'e5f0f20b92f7022779015774e90ce917', 'James', 'Madison', 'jmadison@gmail.com', '2018-02-01 06:00:01', '2018-02-02 07:00:00'),
(5, 'jmonroe', 'e5f0f20b92f7022779015774e90ce917', 'James', 'Monroe', 'jmonroe@gmail.com', '2018-02-01 06:00:01', '2018-02-02 07:00:00'),
(6, 'ajackson', 'e5f0f20b92f7022779015774e90ce917', 'Andrew', 'Jackson', 'ajackson@gmail.com', '2018-02-01 06:00:01', '2018-02-02 07:00:00');
