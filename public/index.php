<?php

declare(strict_types=1);

use Inline\Connection;
use Inline\Query;


// Develop
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require dirname(__DIR__) . '/vendor/autoload.php';

$searchResults = [];
$searchTerm    = '';
$errorMessage  = '';

try {
    $pdo = Connection::getInstance(
        require_once dirname(__DIR__) . '/config/db.php'
    );

    $queryService = new Query($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
        $searchTerm = trim($_GET['search']);

        if (mb_strlen($searchTerm) >= 3) {
            $searchResults = $queryService->searchPostsByCommentText($searchTerm);
        } else {
            $errorMessage = 'Поисковый запрос должен содержать минимум 3 символа.';
        }
    }
} catch (PDOException $e) {
    $errorMessage = 'Ошибка базы данных: ' . $e->getMessage();
    error_log($errorMessage);
} catch (Exception $e) {
    $errorMessage = 'Произошла ошибка: ' . $e->getMessage();
    error_log($errorMessage);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поиск записей блога по комментариям</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
<div class="container">
    <h1>Поиск записей по тексту комментария</h1>

    <?php if ($errorMessage): ?>
        <div class="error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <form action="" method="get">
        <label>
            <input type="text" name="search" placeholder="Введите текст комментария (минимум 3 символа)"
                   value="<?= htmlspecialchars($searchTerm) ?>">
        </label>
        <button type="submit">Найти</button>
    </form>

    <div class="results">
        <?php if (!empty($searchResults)): ?>
            <h2>Результаты поиска:</h2>
            <?php
            $groupedResults = [];
            foreach ($searchResults as $row) {
                $groupedResults[$row['post_id']]['title']      = $row['post_title'];
                $groupedResults[$row['post_id']]['comments'][] = $row['comment_body'];
            }

            foreach ($groupedResults as $postId => $postData):
                ?>
                <div class="post-group">
                    <div class="post-title">Запись ID: <?= htmlspecialchars((string)$postId) ?>
                        - <?= htmlspecialchars($postData['title']) ?></div>
                    <?php foreach ($postData['comments'] as $commentBody): ?>
                        <div class="comment-item">
                            <?= str_replace(htmlspecialchars($searchTerm), '<span class="highlight">' . htmlspecialchars($searchTerm) . '</span>', htmlspecialchars($commentBody)) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php elseif (isset($_GET['search']) && empty($searchTerm) === false && empty($errorMessage)): ?>
            <p class="no-results">По вашему запросу "<?= htmlspecialchars($searchTerm) ?>" ничего не найдено.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
