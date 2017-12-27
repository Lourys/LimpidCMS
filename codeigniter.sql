-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 09, 2017 at 09:59 PM
-- Server version: 5.7.14
-- PHP Version: 5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `codeigniter`
--

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `color` varchar(7) NOT NULL,
  `permissions` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `color`, `permissions`) VALUES
(3, 'dédé', '#000000', 'MENU__MANAGE, PAGES__ADD, PAGES__MANAGE, PAGES__EDIT, PAGES__DELETE, MENU__MANAGE, USERS__DELETE, USERS__TAKE_CONTROL, GROUPS__MANAGE'),
(2, 'Administrateurs', '#ff0000', '');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `title` varchar(60) NOT NULL,
  `url` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_dropdown` tinyint(1) NOT NULL,
  `is_divider` tinyint(1) NOT NULL,
  `position` int(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `title`, `url`, `parent_id`, `is_dropdown`, `is_divider`, `position`) VALUES
(1, 'Accueil', '{{ site_url() }}', NULL, 0, 0, 0),
(2, 'Autres', '', NULL, 1, 0, 2),
(3, 'Test', 'http://google.fr', 2, 0, 0, 1),
(5, 'Test', 'http://lourys.fr', NULL, 0, 0, 1),
(6, 'Dropdown', '#', NULL, 1, 0, 3),
(7, 'Test', 'https://www.amazon.com/AmazonBasics-Training-Puppy-Pads-Regular/dp/B00MW8G62E/ref=sr_1_1?s=pet-supplies&srs=10112675011&ie=UTF8&qid=1496493255&sr=1-1', 6, 0, 0, 2),
(8, '', '', 6, 0, 1, 1),
(9, 'Je suis un sous-lien !', 'https://www.meteocontact.fr/', 6, 0, 0, 0),
(13, 'Actualités', '{{ site_url(\'actualites\') }}', NULL, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(165) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `content`, `author_id`, `created_at`, `edited_at`, `active`) VALUES
(3, 'Je suis un article', 'ok', '<p>YOLO</p>', 1, '2017-06-28 14:48:24', '2017-07-05 12:43:03', 1),
(4, 'Coucou la planète', 'coucou-la-planete', '<p>Coucou les gens de la plan&egrave;te !!!!</p>', 4, '2017-07-05 09:13:09', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `title` varchar(165) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `created_at`, `edited_at`, `active`) VALUES
(1, 'Je suis une page de test', 'ok', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque interdum cursus congue. Vestibulum turpis magna, blandit sit amet tincidunt eget, cursus non massa. Cras eget egestas enim. Nullam in elementum quam, sed sagittis eros. Mauris a quam erat. Vestibulum facilisis gravida sem, eget malesuada odio ultricies eu. Vivamus molestie orci vel magna interdum, sed vulputate mauris volutpat. Fusce tristique, tortor non sollicitudin fermentum, nunc metus porttitor massa, eu tincidunt leo purus a turpis. Vivamus non massa vitae elit ullamcorper porttitor. Proin a lorem id odio auctor blandit vel vel ipsum.<br /> <br /> Phasellus fringilla purus condimentum, imperdiet dolor ut, consequat justo. Fusce pulvinar metus vel nunc aliquam, eleifend faucibus lectus efficitur. Mauris arcu risus, malesuada a ex sit amet, malesuada gravida diam. Phasellus laoreet velit at dolor mattis, in venenatis urna euismod. Sed quis suscipit ante, eu tristique nibh. Nullam eget consectetur lorem. Morbi eu est vehicula, ultricies nulla ut, gravida nibh. Mauris ornare sed arcu ut ullamcorper. Curabitur eu nisi sit amet tellus suscipit consectetur. Aliquam molestie tortor sit amet diam ultrices mattis. Quisque eu facilisis ipsum, sed commodo est. Maecenas a ex at sapien blandit rhoncus in quis elit. In hac habitasse platea dictumst. Vivamus diam ante, semper eu venenatis in, laoreet id sapien. Sed velit eros, suscipit eu velit in, mattis sodales odio.<br /> <br /> Vivamus pharetra, odio pellentesque malesuada faucibus, neque dolor imperdiet orci, et egestas risus risus vitae mauris. Etiam imperdiet volutpat nulla ac bibendum. Nam pulvinar nisl quis purus ultrices venenatis. Maecenas eget placerat lectus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vivamus et suscipit dolor. Pellentesque cursus erat nisi, id feugiat risus viverra molestie. Donec et tellus sit amet libero porttitor auctor. Aenean fringilla consectetur enim non auctor. Aenean eu tincidunt eros, non venenatis velit. Duis tincidunt sem in turpis convallis semper. Nulla eget commodo magna, sit amet rutrum ex. In at eros quis risus fermentum vehicula in pretium quam. Phasellus vitae imperdiet tellus. Proin quis felis arcu.<br /> <br /> Etiam nec tortor vitae dolor dignissim iaculis at eget erat. Quisque non feugiat purus. Praesent scelerisque urna nec enim ornare, sit amet dignissim neque ultrices. Phasellus ac efficitur purus, in lobortis libero. Cras nec elit nec lorem rutrum commodo id ultricies tortor. Vestibulum interdum nec neque sed efficitur. Etiam molestie tincidunt enim vel efficitur. Sed fringilla quam sed dapibus ultrices. Pellentesque nulla massa, sodales sit amet dictum ut, commodo et ex. Nunc sagittis leo id egestas placerat. Integer efficitur varius molestie. Praesent posuere et ipsum ac pharetra. Morbi et vestibulum est. Curabitur eu magna pretium, pharetra turpis eu, pharetra ante. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.<br /> <br /> Quisque aliquet sem at lacus placerat, non tincidunt ligula laoreet. Phasellus sagittis, libero vitae facilisis placerat, quam odio vulputate arcu, id fermentum felis arcu at quam. Integer scelerisque semper egestas. Phasellus urna mauris, vehicula ut diam a, pellentesque sollicitudin nibh. Nullam sit amet elit pellentesque augue porttitor pharetra a sed ligula. Morbi non mattis ante, ac ullamcorper enim. Phasellus pulvinar elit non sagittis venenatis. Nunc mollis sed libero eget lacinia. Pellentesque risus leo, eleifend at nisl eget, malesuada imperdiet mi.</p>', '2017-06-24 13:33:25', '2017-06-24 15:33:25', 1),
(3, 'J\'te lôvv', 'love', '<h1 id="mcetoc_1bk7qf5rj0" style="text-align: center;"><strong>COUCOU !!! ', '2017-07-04 22:11:12', NULL, 1),
(4, 'test', 'je-suis-un-test', '<p><img src="http://localhost/codeigniter/public/assets/default/js/admin/tinymce/plugins/emoticons/img/smiley-innocent.gif" alt="innocent" width="18" height="18" /></p>', '2017-07-04 22:33:12', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`) VALUES
(1, 'ADMIN__ACCESS', '{"french":"Accès au panel d\'administration","english":""}'),
(2, 'MENU__MANAGE', '{"french":"Gestion du menu","english":""}'),
(3, 'PAGES__ADD', '{"french":"Créer une page","english":""}'),
(4, 'PAGES__MANAGE', '{"french":"Gérer les pages","english":""}'),
(5, 'PAGES__EDIT', '{"french":"Éditer les pages","english":""}'),
(6, 'PAGES__DELETE', '{"french":"Supprimer les pages","english":""}'),
(7, 'MENU__MANAGE', '{"french":"Gérer le menu","english":""}'),
(8, 'NEWS__VIEW_DEACTIVATED', '{"french":"Accès aux actualités désactivées","english":""}'),
(9, 'NEWS__ADD', '{"french":"Créer une actualité","english":""}'),
(10, 'NEWS__MANAGE', '{"french":"Gérer les actualités","english":""}'),
(11, 'NEWS__EDIT', '{"french":"Éditer les actualités","english":""}'),
(12, 'NEWS__DELETE', '{"french":"Supprimer les actualités","english":""}'),
(13, 'USERS__ADD', '{"french":"Créer un utilisateur","english":""}'),
(14, 'USERS__MANAGE', '{"french":"Gérer les utilisateurs","english":""}'),
(15, 'USERS__EDIT', '{"french":"Éditer les utilisateurs","english":""}'),
(16, 'USERS__DELETE', '{"french":"Supprimer les utilisateurs","english":""}'),
(17, 'USERS__TAKE_CONTROL', '{"french":"Prendre le contrôle du compte d\'un utilisateur","english":""}'),
(18, 'GROUPS__ADD', '{"french":"Créer un groupe","english":""}'),
(19, 'GROUPS__MANAGE', '{"french":"Gérer les groupes","english":""}'),
(20, 'GROUPS__EDIT', '{"french":"Éditer les groupes","english":""}'),
(21, 'GROUPS__DELETE', '{"french":"Supprimer les groupes","english":""}');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(40) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `data` blob NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `ip_address`, `timestamp`, `data`) VALUES
('61i4fhus53ctpc1a50su1f19fi3jehg4', '::1', 1502313338, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530323331333333373b69647c733a313a2234223b),
('9q5uraq6lej25373g3qpmh2mjc3dpb9s', '::1', 1502314966, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530323331343639343b69647c733a313a2234223b),
('ku0kcgs8319reosmk3b92mc47ls6m3mf', '::1', 1502315260, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530323331353030353b69647c733a313a2234223b),
('kjk8uocrgrgqohknpdl9pcd1fav9tkpd', '::1', 1502315610, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530323331353331343b69647c733a313a2234223b),
('ldkqu609sv1ebmqj5dk82rljt55as1hj', '::1', 1502315902, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530323331353732323b69647c733a313a2234223b),
('r1fojrupq1k9uhjk14unuiff1djkurov', '::1', 1502313223, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530323331323935363b69647c733a313a2234223b),
('2mar901soe8bn2ojlul7tv51i3qk4lei', '::1', 1502308246, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530323330383133333b69647c733a313a2234223b),
('secun5inn7ksl3vn05cv1gk1h5t0h5m0', '::1', 1502307593, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530323330373531303b69647c733a313a2234223b),
('m9d35m122a9d0ri40urk4qvqusmh7ill', '::1', 1502307452, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530323330373136383b69647c733a313a2234223b737563636573737c733a34303a224c652067726f757065206120c3a974c3a9207375707072696dc3a920617665632073756363c3a873223b5f5f63695f766172737c613a313a7b733a373a2273756363657373223b733a333a226f6c64223b7d),
('4sjgs5f7lgc7q1ukt1os462de4mfq6e1', '::1', 1502307035, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530323330363831353b69647c733a313a2234223b),
('opl7mjg8e5pcuhpp4l6fe3tpa7gld7ft', '::1', 1502306776, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530323330363438353b69647c733a313a2234223b),
('a5rse40k6650ge4ueuipvuao7a98jhr4', '::1', 1502306220, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530323330363135313b69647c733a313a2234223b),
('s0qdjl0gfh3jr1egjiao0egf8kq8ilhd', '::1', 1502305649, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530323330353537353b69647c733a313a2234223b),
('jtaf7uap9p033gm2eksoa5qng6o4g10j', '::1', 1501684433, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313638343433313b),
('30n5g0a8j8jt7mb3ujsnvmm0oj97sp77', '::1', 1501691137, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313639313039373b69647c733a313a2234223b),
('1tf7i28pl6e9hla439nk7vj9ae9fjr5k', '::1', 1501692995, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313639323730353b69647c733a313a2234223b),
('slhrkuaifmp8p04i7a9ke2cn1o6r8223', '::1', 1501693340, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313639333034333b69647c733a313a2234223b),
('4o0olanniht1lt43v9ad3vkr7d567niu', '::1', 1501693531, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313639333334353b69647c733a313a2234223b),
('v39fq3r7drrqp3t893v14rjgeevgl9br', '::1', 1501693899, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313639333635303b69647c733a313a2234223b),
('2affdgvf5iekht6l92v1jvin9csr6e8l', '::1', 1501694673, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313639343436393b69647c733a313a2234223b),
('5u7l95lhshd4b1r7slppjcn3deivm9ds', '::1', 1501695094, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313639343830333b69647c733a313a2234223b),
('vqib04k546j0lme1t068d78e3njqa3jt', '::1', 1501695321, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313639353233323b69647c733a313a2234223b),
('i04ruljaho5v9kocfu37qpjor8bkma7h', '::1', 1501705543, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313730353534323b69647c733a313a2234223b),
('07309jbebpg7biqohc0ifp6s1kbp96fg', '::1', 1501772411, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313737323430393b69647c733a313a2234223b),
('6lg0qnu938rpocbf209ncjk18pcn7vs0', '::1', 1501608220, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313630383231393b),
('1e8bccitkduiuhr8aqtol5im1iss35tk', '::1', 1501608603, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313630383539393b),
('hpu749r70823soviscmsms904vusa9aq', '::1', 1501621832, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313632313833313b),
('cbct5hvdc86cf7s4dj43ngh72t3930rq', '::1', 1501511094, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313531313039333b69647c733a313a2234223b),
('hl22mpljnlvcejbpq1sok1300a089foq', '::1', 1501511751, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313531313630353b69647c733a313a2234223b),
('9kp6sdj2kchsh0m1ie0768b9nj7l2ub6', '::1', 1501495599, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313439353539383b69647c733a313a2234223b),
('lr59a9l8jji787544ndp23n25f0v74r1', '::1', 1501493837, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313439333833323b69647c733a313a2234223b),
('d3qdcr0o70628mga5vb1a836pc1ih6b9', '::1', 1501440888, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313434303837323b69647c733a313a2234223b),
('6dq3t36isbfl50msrohf9caleouqaotc', '::1', 1501443259, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313434333234343b69647c733a313a2234223b),
('dj5re75cb9a3g6dpqukcn3rf3ctu64bo', '::1', 1501443329, 0x5f5f63695f6c6173745f726567656e65726174657c693a313530313434333332363b69647c733a313a2234223b);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(25) NOT NULL,
  `email` varchar(105) NOT NULL,
  `password` varchar(255) NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `registered_at`) VALUES
(1, 'CocoLaNoix', 'CocoLaNoixDeCoco', 'cocoLeBG', '2017-06-24 15:25:58'),
(4, 'Lourys', 'me@lourys.fr', '$2y$12$IN2CiYqGHH/QiRiq4eIaiO8gN7DCNrQ.XWnZHhgQq7gBj0CFWVCYS', '2017-06-28 21:42:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ci_sessions_timestamp` (`timestamp`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
