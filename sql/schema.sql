CREATE DATABASE IF NOT EXISTS fanhub_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fanhub_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS chatbot_queries;
DROP TABLE IF EXISTS chatbot_faqs;
DROP TABLE IF EXISTS media_ratings;
DROP TABLE IF EXISTS bookmarks;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS merchandise_items;
DROP TABLE IF EXISTS character_profiles;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS user_favorite_categories;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_pic VARCHAR(255) DEFAULT 'default-avatar.png',
    bio TEXT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    icon_code VARCHAR(50) NULL,
    default_thumbnail VARCHAR(255) DEFAULT 'default-category.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE user_favorite_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_category (user_id, category_id)
) ENGINE=InnoDB;

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    genre VARCHAR(50) NULL,
    type ENUM('article','video','audio','image') NOT NULL DEFAULT 'article',
    media_url VARCHAR(255) NULL,
    thumbnail VARCHAR(255) NOT NULL,
    release_year INT NULL,
    is_upcoming TINYINT(1) DEFAULT 0,
    release_date DATE NULL,
    views INT DEFAULT 0,
    popularity INT DEFAULT 0,
    status ENUM('pending','approved','rejected') DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_category (category_id),
    INDEX idx_popularity (popularity)
) ENGINE=InnoDB;

CREATE TABLE character_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    bio TEXT NULL,
    role_type VARCHAR(50) NULL,
    image VARCHAR(255) DEFAULT 'default-character.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE merchandise_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    tag ENUM('Limited Edition','Pre-Order','Collectible') NULL,
    image VARCHAR(255) DEFAULT 'default-merch.jpg',
    views INT DEFAULT 0,
    is_upcoming TINYINT(1) DEFAULT 0,
    release_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    city VARCHAR(100) NOT NULL,
    latitude DECIMAL(10,7) NOT NULL,
    longitude DECIMAL(10,7) NOT NULL,
    event_date DATE NOT NULL,
    ticket_link VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_post_bookmark (user_id, post_id)
) ENGINE=InnoDB;

CREATE TABLE media_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    rating_stars TINYINT NOT NULL,
    review_comment TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_post_rating (user_id, post_id),
    CONSTRAINT chk_rating_stars CHECK (rating_stars BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE chatbot_faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    keywords VARCHAR(255) NULL,
    answer TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE chatbot_queries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    message TEXT NOT NULL,
    response TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    sender_name VARCHAR(100) NOT NULL,
    sender_email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    admin_reply TEXT NULL,
    status ENUM('unread','read','replied','resolved') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token)
) ENGINE=InnoDB;

INSERT INTO categories (name, slug, description, icon_code, default_thumbnail) VALUES
('Anime', 'anime', 'Japanese animation series and films.', 'anime', 'anime-default.jpg'),
('Gaming', 'gaming', 'Video games, esports, and playthroughs.', 'gaming', 'gaming-default.jpg'),
('Movies', 'movies', 'Feature films across fandoms.', 'movies', 'movies-default.jpg'),
('TV Shows', 'tv-shows', 'Serialized television and streaming shows.', 'tv', 'tvshows-default.jpg'),
('K-Pop', 'k-pop', 'Korean pop music, idols, and comebacks.', 'music', 'kpop-default.jpg'),
('Comics', 'comics', 'Western comics and graphic novels.', 'comics', 'comics-default.jpg'),
('Manga', 'manga', 'Japanese comics and print series.', 'manga', 'manga-default.jpg'),
('Cosplay', 'cosplay', 'Costume design, conventions, and crafts.', 'cosplay', 'cosplay-default.jpg');

INSERT INTO users (name, username, email, password, bio, role) VALUES
('Fan Hub Admin', 'admin', 'admin@fanhub.com', '$2y$12$ILeIz974KvYNfvgL7lS2oujPBYFA.wFckb3w8XAWYhz0bF6FvM6j6', 'Platform administrator.', 'admin'),
('Test Fan', 'testfan', 'user@fanhub.com', '$2y$12$/R1lf93PHOsaYLBxmK9.OOjXdqxUZuDSDmKwSgd.mJDIKyPwXLhRq', 'Demo registered user for evaluation.', 'user');

INSERT INTO user_favorite_categories (user_id, category_id) VALUES (2, 1), (2, 2), (2, 5);

INSERT INTO posts (user_id, category_id, title, description, genre, type, media_url, thumbnail, release_year, is_upcoming, release_date, views, popularity, status) VALUES
(NULL, 1, 'Neon Blade Chronicles', 'A featured article on rising dark-fantasy anime.', 'Action', 'article', NULL, 'anime-default.jpg', 2024, 0, NULL, 42, 80, 'approved'),
(NULL, 2, 'Raid Night: Co-op Tactics', 'Strategy notes for weekend raid groups.', 'RPG', 'article', NULL, 'gaming-default.jpg', 2025, 0, NULL, 31, 60, 'approved'),
(NULL, 3, 'Midnight Premiere Recap', 'Opening-weekend recap for a fandom favorite.', 'Sci-Fi', 'article', NULL, 'movies-default.jpg', 2023, 0, NULL, 18, 40, 'approved'),
(NULL, 4, 'Season Finale Decoder', 'TV-show finale theories without spoilers in the title.', 'Drama', 'article', NULL, 'tvshows-default.jpg', 2024, 0, NULL, 22, 45, 'approved'),
(NULL, 5, 'Comeback Stage Notes', 'What to watch for on the next K-Pop comeback stage.', 'Pop', 'article', NULL, 'kpop-default.jpg', 2025, 1, '2026-10-12', 9, 20, 'approved'),
(NULL, 6, 'Variant Cover Hunt', 'Collectible comic covers worth tracking this quarter.', 'Superhero', 'article', NULL, 'comics-default.jpg', 2022, 0, NULL, 14, 25, 'approved'),
(NULL, 7, 'Chapter Drop Club', 'Weekly manga chapter discussion starter.', 'Shonen', 'article', NULL, 'manga-default.jpg', 2025, 0, NULL, 27, 50, 'approved'),
(NULL, 8, 'Convention Armor Build', 'Cosplay armor workflow from foam to finish.', 'Craft', 'article', NULL, 'cosplay-default.jpg', 2024, 0, NULL, 11, 18, 'approved'),
(2, 1, 'Pending Fan Essay', 'User-submitted post waiting for moderation.', 'Slice of Life', 'article', NULL, 'anime-default.jpg', 2025, 0, NULL, 0, 0, 'pending'),
(2, 2, 'Rejected Clip Notes', 'Example rejected submission for dashboard testing.', 'Shooter', 'article', NULL, 'gaming-default.jpg', 2025, 0, NULL, 0, 0, 'rejected');

INSERT INTO character_profiles (category_id, name, bio, role_type, image) VALUES
(1, 'Aiko Renshaw', 'A ronin courier who smuggles memories between cities.', 'Protagonist', 'default-character.jpg'),
(5, 'Luna Haneul', 'Lead vocalist of a fictional fourth-gen group.', 'Idol', 'default-character.jpg');

INSERT INTO merchandise_items (category_id, title, description, tag, image, views, is_upcoming, release_date) VALUES
(1, 'Blade Crest Pin', 'Enamel pin from the Neon Blade drop.', 'Limited Edition', 'default-merch.jpg', 4, 0, NULL),
(5, 'Comeback Lightstick', 'Pre-order lightstick for the autumn tour.', 'Pre-Order', 'default-merch.jpg', 7, 1, '2026-10-01');

INSERT INTO events (category_id, title, city, latitude, longitude, event_date, ticket_link) VALUES
(8, 'Karachi Cosplay Meet', 'Karachi', 24.8607000, 67.0011000, '2026-11-14', 'https://example.com/tickets/khi'),
(2, 'Lahore Game Con', 'Lahore', 31.5204000, 74.3587000, '2026-12-05', 'https://example.com/tickets/lhr');

INSERT INTO chatbot_faqs (question, keywords, answer) VALUES
('How do I create an account?', 'register,signup,account', 'Open Register, choose a unique username, and submit. New posts start as pending until an admin approves them.'),
('How do I reset my password?', 'password,forgot,reset', 'Use Forgot Password, then open the Local Test Debug Banner link on the same page (SMTP is not required on XAMPP).'),
('Can guests save posts?', 'bookmark,save,guest,login', 'Guests can browse, but Save and Submit open a login/register modal. No page reload and no database write happens until you log in.');
