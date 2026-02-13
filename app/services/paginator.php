<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class Paginator {
    public static function build(int $total, int $page, int $perPage): array {
        $totalPages = max(1, (int)ceil($total / max(1, $perPage)));
        $page = min(max(1, $page), $totalPages);
        $offset = ($page - 1) * $perPage;

        return [
            'total' => $total,
            'per_page' => $perPage,
            'page' => $page,
            'total_pages' => $totalPages,
            'offset' => $offset,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
        ];
    }
}
