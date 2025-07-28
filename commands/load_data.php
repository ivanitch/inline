<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Inline\Connection;
use Inline\Query;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dbConfig = require_once dirname(__DIR__) . '/config/db.php';

echo "Начинается загрузка данных из API в базу данных...\n";

$postsCount    = 0;
$commentsCount = 0;

try {
    $pdo = Connection::getInstance($dbConfig);
    echo "Успешное подключение к базе данных.\n";

    $queryService = new Query($pdo);
    $httpClient   = new Client();

    // Если нужно очистить таблицы перед вставкой
    $queryService->truncate();

    echo "Загрузка записей...\n";
    $postsResponse = $httpClient->get('https://jsonplaceholder.typicode.com/posts');
    $posts         = json_decode($postsResponse->getBody()->getContents(), true);

    echo "Загрузка комментариев...\n";
    $commentsResponse = $httpClient->get('https://jsonplaceholder.typicode.com/comments');
    $comments         = json_decode($commentsResponse->getBody()->getContents(), true);


    $pdo->beginTransaction();

    echo "Сохранение записей в БД...\n";
    foreach ($posts as $post) {
        if ($queryService->insertPost($post)) {
            $postsCount++;
        } else {
            error_log("Не удалось вставить запись ID: " . ($post['id'] ?? 'N/A'));
        }
    }

    echo "Сохранение комментариев в БД...\n";
    foreach ($comments as $comment) {
        if ($queryService->insertComment($comment)) {
            $commentsCount++;
        } else {
            error_log("Не удалось вставить комментарий ID: " . ($comment['id'] ?? 'N/A'));
        }
    }

    $pdo->commit();

    echo "Загружено $postsCount записей и $commentsCount комментариев.\n";
    echo "Загрузка данных завершена успешно!\n";

    unset(
        $posts,
        $comment,
        $postsCount,
        $commentsCount
    );

} catch (GuzzleException $e) {
    echo "Ошибка при загрузке данных с API: " . $e->getMessage() . "\n";
    exit(1);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
        echo "Транзакция отменена.\n";
    }
    echo "Ошибка при работе с базой данных: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
        echo "Транзакция отменена из-за непредвиденной ошибки.\n";
    }
    echo "Произошла непредвиденная ошибка: " . $e->getMessage() . "\n";
    exit(1);
}
