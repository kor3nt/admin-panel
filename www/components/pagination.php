<?php
// Create pagination
function pageUrl($p, $search)
{
    $params = [];

    if ($search !== '') {
        $params[] = "search=" . urlencode($search);
    }

    $params[] = "number=" . $p;

    return "?" . implode("&", $params);
}