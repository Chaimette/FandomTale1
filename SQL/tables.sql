CREATE DATABASE IF NOT EXISTS fandom_tales CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fandom_tales;

CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    bio TEXT,
    avatar_url VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    INDEX idx_username (username),
    INDEX idx_email (email)
);

CREATE TABLE fandom (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    image_url VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    story_count INT DEFAULT 0,
    INDEX idx_name (name),
    INDEX idx_slug (slug)
);

CREATE TABLE story (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    summary TEXT,
    content TEXT,
    status ENUM('draft', 'published', 'completed', 'abandoned', 'on_hold') DEFAULT 'draft',
    is_completed BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at DATETIME,
    is_published BOOLEAN DEFAULT FALSE,
    language VARCHAR(10) DEFAULT 'fr',
    rating ENUM('G', 'PG', 'PG-13', 'R', 'NC-17') DEFAULT 'G',
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_published (is_published),
    INDEX idx_created_at (created_at),
    INDEX idx_view_count (view_count)
);

CREATE TABLE chapter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    story_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    chapter_number INT NOT NULL,
    word_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at DATETIME,
    is_published BOOLEAN DEFAULT FALSE,
    author_note TEXT,
    FOREIGN KEY (story_id) REFERENCES story(id) ON DELETE CASCADE,
    UNIQUE KEY unique_story_chapter (story_id, chapter_number),
    INDEX idx_story_id (story_id),
    INDEX idx_chapter_number (chapter_number),
    INDEX idx_published (is_published)
);

CREATE TABLE tag (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    type ENUM('genre', 'character', 'relationship', 'warning', 'general') DEFAULT 'general',
    is_predefined BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    usage_count INT DEFAULT 0,
    INDEX idx_name (name),
    INDEX idx_slug (slug),
    INDEX idx_type (type),
    INDEX idx_usage_count (usage_count)
);

CREATE TABLE comment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    chapter_id INT NOT NULL,
    parent_comment_id INT,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_edited BOOLEAN DEFAULT FALSE,
    is_deleted BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (chapter_id) REFERENCES chapter(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_comment_id) REFERENCES comment(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_chapter_id (chapter_id),
    INDEX idx_parent_comment_id (parent_comment_id),
    INDEX idx_created_at (created_at)
);

CREATE TABLE reading_list (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_public (is_public)
);

CREATE TABLE notification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('new_chapter', 'new_comment', 'comment_reply', 'new_follower', 'story_favorited') NOT NULL,
    message TEXT NOT NULL,
    data JSON,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);


CREATE TABLE story_tag (
    id INT AUTO_INCREMENT PRIMARY KEY,
    story_id INT NOT NULL,
    tag_id INT NOT NULL,
    FOREIGN KEY (story_id) REFERENCES story(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tag(id) ON DELETE CASCADE,
    UNIQUE KEY unique_story_tag (story_id, tag_id),
    INDEX idx_story_id (story_id),
    INDEX idx_tag_id (tag_id)
);

CREATE TABLE story_fandom (
    id INT AUTO_INCREMENT PRIMARY KEY,
    story_id INT NOT NULL,
    fandom_id INT NOT NULL,
    FOREIGN KEY (story_id) REFERENCES story(id) ON DELETE CASCADE,
    FOREIGN KEY (fandom_id) REFERENCES fandom(id) ON DELETE CASCADE,
    UNIQUE KEY unique_story_fandom (story_id, fandom_id),
    INDEX idx_story_id (story_id),
    INDEX idx_fandom_id (fandom_id)
);

CREATE TABLE favorite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    story_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (story_id) REFERENCES story(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_story (user_id, story_id),
    INDEX idx_user_id (user_id),
    INDEX idx_story_id (story_id),
    INDEX idx_created_at (created_at)
);

CREATE TABLE reading_list_story (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reading_list_id INT NOT NULL,
    story_id INT NOT NULL,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    position INT DEFAULT 0,
    FOREIGN KEY (reading_list_id) REFERENCES reading_list(id) ON DELETE CASCADE,
    FOREIGN KEY (story_id) REFERENCES story(id) ON DELETE CASCADE,
    UNIQUE KEY unique_list_story (reading_list_id, story_id),
    INDEX idx_reading_list_id (reading_list_id),
    INDEX idx_story_id (story_id),
    INDEX idx_position (position)
);

CREATE TABLE follow (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,
    followed_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (follower_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (followed_id) REFERENCES user(id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (follower_id, followed_id),
    CHECK (follower_id != followed_id),
    INDEX idx_follower_id (follower_id),
    INDEX idx_followed_id (followed_id)
);

DELIMITER //
CREATE TRIGGER increment_fandom_story_count
    AFTER INSERT ON story_fandom
    FOR EACH ROW
BEGIN
    UPDATE fandom SET story_count = story_count + 1 WHERE id = NEW.fandom_id;
END//

CREATE TRIGGER decrement_fandom_story_count
    AFTER DELETE ON story_fandom
    FOR EACH ROW
BEGIN
    UPDATE fandom SET story_count = story_count - 1 WHERE id = OLD.fandom_id;
END//

CREATE TRIGGER increment_tag_usage_count
    AFTER INSERT ON story_tag
    FOR EACH ROW
BEGIN
    UPDATE tag SET usage_count = usage_count + 1 WHERE id = NEW.tag_id;
END//

CREATE TRIGGER decrement_tag_usage_count
    AFTER DELETE ON story_tag
    FOR EACH ROW
BEGIN
    UPDATE tag SET usage_count = usage_count - 1 WHERE id = OLD.tag_id;
END//

CREATE TRIGGER calculate_chapter_word_count
    BEFORE INSERT ON chapter
    FOR EACH ROW
BEGIN
    SET NEW.word_count = (
        CHAR_LENGTH(NEW.content) - CHAR_LENGTH(REPLACE(NEW.content, ' ', '')) + 1
    );
END//

CREATE TRIGGER update_chapter_word_count
    BEFORE UPDATE ON chapter
    FOR EACH ROW
BEGIN
    IF NEW.content != OLD.content THEN
        SET NEW.word_count = (
            CHAR_LENGTH(NEW.content) - CHAR_LENGTH(REPLACE(NEW.content, ' ', '')) + 1
        );
    END IF;
END//

DELIMITER ;

INSERT INTO tag (name, slug, type, is_predefined) VALUES
('Romance', 'romance', 'genre', TRUE),
('Angst', 'angst', 'genre', TRUE),
('Fluff', 'fluff', 'genre', TRUE),
('Hurt/Comfort', 'hurt-comfort', 'genre', TRUE),
('Action/Aventure', 'action-aventure', 'genre', TRUE),
('Mystère', 'mystere', 'genre', TRUE),
('Humour', 'humour', 'genre', TRUE),
('Drame', 'drame', 'genre', TRUE),
('Violence', 'violence', 'warning', TRUE),
('Langage explicite', 'langage-explicite', 'warning', TRUE),
('Contenu mature', 'contenu-mature', 'warning', TRUE),
('Mort de personnage', 'mort-personnage', 'warning', TRUE);

INSERT INTO fandom (name, slug, description) VALUES
('Harry Potter', 'harry-potter', 'L''univers magique de J.K. Rowling'),
('Naruto', 'naruto', 'L''univers ninja de Masashi Kishimoto'),
('My Hero Academia', 'my-hero-academia', 'L''univers des super-héros de Kohei Horikoshi'),
('Attack on Titan', 'attack-on-titan', 'L''univers post-apocalyptique de Hajime Isayama'),
('Marvel Cinematic Universe', 'marvel-cinematic-universe', 'L''univers cinématographique Marvel'),
('Sherlock Holmes', 'sherlock-holmes', 'L''univers du détective d''Arthur Conan Doyle');

COMMIT;