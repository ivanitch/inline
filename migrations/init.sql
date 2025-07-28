-- Posts
CREATE TABLE IF NOT EXISTS `posts`
(
    `id`      INT          NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
    `user_id` INT          NOT NULL COMMENT 'ID пользователя',
    `title`   VARCHAR(255) NOT NULL COMMENT 'Заголовок записи',
    `body`    TEXT         NOT NULL COMMENT 'Текст записи',
    PRIMARY KEY (`id`)
    ) ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Таблица записей';


-- Comments
CREATE TABLE IF NOT EXISTS `comments`
(
    `id`      INT          NOT NULL AUTO_INCREMENT COMMENT 'ID комментария',
    `post_id` INT          NOT NULL COMMENT 'ID (ссылка) записи, к которой относится комментарий (внешний ключ к таблице posts)',
    `name`    VARCHAR(255) NOT NULL COMMENT 'Имя автора комментария',
    `email`   VARCHAR(255) NOT NULL COMMENT 'Email автора комментария',
    `body`    TEXT         NOT NULL COMMENT 'Текст комментария',
    PRIMARY KEY (`id`),
    KEY `post_id_idx` (`post_id`),
    CONSTRAINT `fk_comments_post_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Таблица комментариев';

CREATE FULLTEXT INDEX `idx_comment_body` ON `comments` (`body`);