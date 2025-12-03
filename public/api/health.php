<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'timestamp' => date('Y-m-d\TH:i:s\Z'),
    'version' => '1.0.0',
    'message' => '雅虎 B2B 采购门户 API - 简化模式运行中'
]);
?>