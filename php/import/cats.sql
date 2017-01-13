INSERT INTO `categories` (`id`, `pid`, `name`, `type`, `image_name`) VALUES
(1, 1, 'ROOT', 1, ''),
(2, 1, 'Buildings', 1, 'layers/redspot.png'),
(3, 1, 'Lecture Theatres', 10, '/layers/teaching_rooms.png'),
(5, 1, 'Facilities and amenities', 1, ''),
(6, 1, 'Student Residences', 0, '/layers/residences.png'),
(9, 5, 'Food and Drink', 10, '/layers/food.png'),
(13, 5, 'PC Labs/Workstations', 10, '/layers/computer_labs.png'),
(20, 1, 'Transport', 1, ''),
(24, 20, 'Bus stops', 10, '/layers/bus.png'),
(39, 1, 'Recycling', 1, '/layers/recycling.png'),
(53, 20, 'Car Parks', 0, '/layers/parking.png'),
(56, 5, 'Shops', 10, '/layers/shops.png'),
(58, 5, 'Toilets', 1, ''),
(59, 58, 'Toilets (Male)', 20, '/layers/toilet_male.png'),
(60, 58, 'Toilets (Female)', 20, '/layers/toilet_female.png'),
(61, 58, 'Toilets (Accessible)', 20, '/layers/toilet_disabled.png'),
(62, 58, 'Toilets (Unisex)', 20, '/layers/toilet_unisex.png'),
(63, 1, 'Lobby, Atrium or Foyer', 49, ''),
(64, 1, 'Corridors', 49, ''),
(65, 1, 'Outline', 49, ''),
(67, 1, 'Not searchable', 49, ''),
(68, 5, 'Cash Machines (ATM)', 1, '/layers/atm.png'),
(100, 1, 'Stairs', 0, '/layers/stairs.png'),
(101, 1, 'Lifts', 0, '/layers/elev.png');

--
-- Default no-style (for FK const.)
-- TODO: Add in cat.sql! Skeleton
--
INSERT INTO `styles` (`id`,`name`) VALUES (1, 'NoStyle');
