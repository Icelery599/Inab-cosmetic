<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getPublicUserSessionId() {
    if (empty($_SESSION['public_user_id'])) {
        $_SESSION['public_user_id'] = bin2hex(random_bytes(16));
    }

    return $_SESSION['public_user_id'];
}

function getRequestIpAddress() {
    $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            return trim($ip);
        }
    }

    return null;
}


function ensurePublicUserActivitiesTable($db) {
    $query = "CREATE TABLE IF NOT EXISTS public_user_activities (
        id INT PRIMARY KEY AUTO_INCREMENT,
        session_id VARCHAR(64) NOT NULL,
        activity_type VARCHAR(100) NOT NULL,
        activity_details TEXT,
        page_url VARCHAR(500),
        product_id INT NULL,
        order_id INT NULL,
        ip_address VARCHAR(45),
        user_agent VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_session_id (session_id),
        INDEX idx_activity_type (activity_type),
        INDEX idx_created_at (created_at),
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
    )";
    $db->exec($query);
}

function logPublicUserActivity($activityType, $activityDetails = null, $productId = null, $orderId = null) {
    if (!class_exists('Database')) {
        include_once 'config.php';
    }

    try {
        $database = new Database();
        $db = $database->getConnection();

        if (!$db) {
            return;
        }

        ensurePublicUserActivitiesTable($db);

        $query = "INSERT INTO public_user_activities
            (session_id, activity_type, activity_details, page_url, product_id, order_id, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            getPublicUserSessionId(),
            $activityType,
            $activityDetails,
            $_SERVER['REQUEST_URI'] ?? null,
            $productId,
            $orderId,
            getRequestIpAddress(),
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        error_log('Activity log failed: ' . $e->getMessage());
    }
}
?>
