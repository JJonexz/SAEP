<?php
function db_read(string $file): array {
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}
function db_write(string $file, array $data): void {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
function db_find(array $records, string $field, $value): ?array {
    foreach ($records as $r) { if (isset($r[$field]) && $r[$field] === $value) return $r; }
    return null;
}
function db_find_index(array $records, string $field, $value): int {
    foreach ($records as $i => $r) { if (isset($r[$field]) && $r[$field] === $value) return $i; }
    return -1;
}
function generate_id(): int {
    static $lastId = 0;
    if ($lastId === 0) {
        $id = 1000; // o leer máximo de JSONs
    }
    return ++$lastId;
}
