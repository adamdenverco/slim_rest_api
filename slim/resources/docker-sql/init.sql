CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `firstname` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `lastname` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`username`),
  UNIQUE KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `users` (`id`, `username`, `password`, `firstname`, `lastname`, `email`, `created`, `updated`) VALUES
(1, 'gwashington', 'temppass', 'George', 'Washington', 'gwashington@gmail.com', '2018-02-01 06:00:01', '2018-02-02 07:00:00'),
(2, 'jadams', 'temppass', 'John', 'Adams', 'jadams@gmail.com', '2018-02-01 06:00:01', '2018-02-02 07:00:00'),
(3, 'tjefferson', 'temppass', 'Thomas', 'jefferson', 'tjefferson@gmail.com', '2018-02-01 06:00:01', '2018-02-02 07:00:00'),
(4, 'jmadison', 'temppass', 'James', 'Madison', 'jmadison@gmail.com', '2018-02-01 06:00:01', '2018-02-02 07:00:00'),
(5, 'jmonroe', 'temppass', 'James', 'Monroe', 'jmonroe@gmail.com', '2018-02-01 06:00:01', '2018-02-02 07:00:00');

