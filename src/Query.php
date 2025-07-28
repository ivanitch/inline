<?php

declare(strict_types=1);

namespace Inline;

use PDO;
use PDOException;

readonly class Query
{
    /**
     * @param PDO $pdo
     */
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @param array $post
     *
     * @return bool
     */
    public function insertPost(array $post): bool
    {
        try {
            $sql  = "INSERT IGNORE INTO posts (`id`, `user_id`, `title`, `body`) VALUES (:id, :userId, :title, :body)";
            $stmt = $this->getConnection()->prepare($sql);

            return $stmt->execute([
                ':id'     => $post['id'],
                ':userId' => $post['userId'],
                ':title'  => $post['title'],
                ':body'   => $post['body']
            ]);
        } catch (PDOException $e) {
            error_log("Ошибка при вставке записи: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array $comment
     *
     * @return bool
     */
    public function insertComment(array $comment): bool
    {
        try {
            $sql  = "INSERT IGNORE INTO comments (`id`, `post_id`, `name`, `email`, `body`) VALUES (:id, :postId, :name, :email, :body)";
            $stmt = $this->getConnection()->prepare($sql);

            return $stmt->execute([
                ':id'     => $comment['id'],
                ':postId' => $comment['postId'],
                ':name'   => $comment['name'],
                ':email'  => $comment['email'],
                ':body'   => $comment['body']
            ]);
        } catch (PDOException $e) {
            error_log("Ошибка при вставке комментария: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Очистка таблиц
     *
     * @return void
     */
    public function truncate(): void
    {
        try {
            echo "Очистка существующих данных в таблицах...\n";
            $this->getConnection()->exec("SET FOREIGN_KEY_CHECKS = 0;"); // Отключаем проверку внешних ключей временно
            $this->getConnection()->exec("TRUNCATE TABLE comments;");
            $this->getConnection()->exec("TRUNCATE TABLE posts;");
            $this->getConnection()->exec("SET FOREIGN_KEY_CHECKS = 1;"); // Включаем проверку внешних ключей обратно
            echo "Данные очищены.\n";
        } catch (PDOException $e) {
            throw new PDOException("Ошибка при очистке таблиц: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @param string $search
     *
     * @return array
     */
    public function searchPostsByCommentText(string $search): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                p.id AS post_id,
                p.title AS post_title,
                c.id AS comment_id,
                c.body AS comment_body
            FROM
                posts p
            JOIN
                comments c ON p.id = c.post_id
            WHERE
                c.body LIKE :search
            ORDER BY
                p.id, c.id
        ");

        $stmt->execute([':search' => '%' . $search . '%']);

        return $stmt->fetchAll();
    }

    private function getConnection(): PDO
    {
        return $this->pdo;
    }
}
