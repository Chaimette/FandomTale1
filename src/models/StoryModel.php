<?php

namespace App\models;

use App\Models\AbstractModel;
use PDO;
use PDOException;

class StoryModel extends AbstractModel
{
    public const TABLE = 'story';
    public const ID = 'id';

    /**
     * Récupère une histoire par son ID
     *
     * @param int $id
     * @return array|null
     */
    public function getStoryById(int $id): ?array
    {
        $query = "SELECT * FROM " . self::TABLE . " WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $story = $stmt->fetch(PDO::FETCH_ASSOC);
        return $story ?: null;
    }

    public function createStory(
        string $title,
        string $content,
        int $authorId,
        ?string $summary = null,
        bool $isPublished = false,
        ?string $createdAt = null,
        int $chapterCount = 0,

    ): bool {
        $query = "INSERT INTO " . self::TABLE . "
                (title, content, author_id, summary, is_published, created_at, chapter_count)
                VALUES (:title, :content, :author_id, :summary, :is_published, :created_at, :chapter_count)";
        $stmt = $this->pdo->prepare($query);
        $params = [
            ':title' => $title,
            ':content' => $content,
            ':author_id' => $authorId,
            ':summary' => $summary,
            ':is_published' => $isPublished ? 1 : 0,
            ':created_at' => $createdAt ?? date('Y-m-d H:i:s'),
            ':chapter_count' => $chapterCount
        ];
        return $stmt->execute($params);
    }

    public function updateStory(
        int $id,
        string $title,
        string $content,
        ?string $summary = null,
        bool $isPublished = false,
        ?string $updatedAt = null,
        int $chapterCount = 0
    ): bool {
        $query = "UPDATE " . self::TABLE . " SET
                title = :title,
                content = :content,
                summary = :summary,
                is_published = :is_published,
                created_at = :created_at,
                chapter_count = :chapter_count
                WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $params = [
            ':id' => $id,
            ':title' => $title,
            ':content' => $content,
            ':summary' => $summary,
            ':is_published' => $isPublished ? 1 : 0,
            ':created_at' => $updatedAt ?? date('Y-m-d H:i:s'),
            ':chapter_count' => $chapterCount
        ];
        return $stmt->execute($params);
    }

    public function deleteStory(int $id): bool
    {
        $query = "DELETE FROM " . self::TABLE . " WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    //TODO maybe add an id parameter to get stories for each user
    public function getAllStories(): array
    {
        $query = "SELECT * FROM " . self::TABLE;
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStoriesByAuthorId(int $authorId): array
    {
        $query = "SELECT * FROM " . self::TABLE . " WHERE author_id = :author_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':author_id', $authorId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getPublishedStories(): array
    {
        $query = "SELECT * FROM " . self::TABLE . " WHERE is_published = 1";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnpublishedStories(): array
    {
        $query = "SELECT * FROM " . self::TABLE . " WHERE is_published = 0";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function countStories(): int
    {
        $query = "SELECT COUNT(*) FROM " . self::TABLE;
        $stmt = $this->pdo->query($query);
        return (int) $stmt->fetchColumn();
    }
    public function countPublishedStories(): int
    {
        $query = "SELECT COUNT(*) FROM " . self::TABLE . " WHERE is_published = 1";
        $stmt = $this->pdo->query($query);
        return (int) $stmt->fetchColumn();
    }
    public function countUnpublishedStories(): int
    {
        $query = "SELECT COUNT(*) FROM " . self::TABLE . " WHERE is_published = 0";
        $stmt = $this->pdo->query($query);
        return (int) $stmt->fetchColumn();
    }
    public function searchStories(string $keyword): array
    {
        $query = "SELECT * FROM " . self::TABLE . " WHERE title LIKE :keyword OR content LIKE :keyword";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getLatestStories(int $limit = 5): array
    {
        $query = "SELECT * FROM " . self::TABLE . " ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getStoriesByChapterCount(int $minChapters): array
    {
        $query = "SELECT * FROM " . self::TABLE . " WHERE chapter_count >= :min_chapters";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':min_chapters', $minChapters, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getStoriesByDateRange(string $startDate, string $endDate): array
    {
        $query = "SELECT * FROM " . self::TABLE . " WHERE created_at BETWEEN :start_date AND :end_date";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getStoriesByTitle(string $title): array
    {
        $query = "SELECT * FROM " . self::TABLE . " WHERE title LIKE :title";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':title', '%' . $title . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getStoriesByContent(string $content): array
    {
        $query = "SELECT * FROM " . self::TABLE . " WHERE content LIKE :content";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':content', '%' . $content . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getStoriesByAuthorName(string $authorName): array
    {
        $query = "SELECT s.* FROM " . self::TABLE . " s
                JOIN user u ON s.author_id = u.id
                WHERE u.username LIKE :author_name";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':author_name', '%' . $authorName . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    }

    public function getStoriesByTag(string $tag): array
    {
        $query = "SELECT s.* FROM " . self::TABLE . " s
                JOIN story_tag st ON s.id = st.story_id
                JOIN tag t ON st.tag_id = t.id
                WHERE t.name LIKE :tag";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':tag', '%' . $tag . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
