<?php
class CategoryModel
{
    /** @return array<int, string> [id => name] */
    public static function all(mysqli $conn): array
    {
        $sql = "SELECT id, name FROM categories ORDER BY id";
        $res = $conn->query($sql);
        if (!$res) return [];

        $out = [];
        while ($row = $res->fetch_assoc()) {
            $out[(int)$row['id']] = $row['name'];
        }
        return $out;
    }
}
