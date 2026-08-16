<?php include 'header.php'; ?>
<?php include_once 'config.php'; ?>
<?php include_once 'activity_logger.php'; ?>

<?php
$database = new Database();
$db = $database->getConnection();

$summary = [
    'total_activities' => 0,
    'unique_visitors' => 0,
    'cart_actions' => 0,
    'completed_checkouts' => 0
];
$recentActivities = [];
$activityBreakdown = [];
$popularProducts = [];

if ($db) {
    ensurePublicUserActivitiesTable($db);

    $summary['total_activities'] = (int) $db->query("SELECT COUNT(*) FROM public_user_activities")->fetchColumn();
    $summary['unique_visitors'] = (int) $db->query("SELECT COUNT(DISTINCT session_id) FROM public_user_activities")->fetchColumn();
    $summary['cart_actions'] = (int) $db->query("SELECT COUNT(*) FROM public_user_activities WHERE activity_type IN ('add_to_cart', 'cart_add', 'cart_update', 'cart_remove')")->fetchColumn();
    $summary['completed_checkouts'] = (int) $db->query("SELECT COUNT(*) FROM public_user_activities WHERE activity_type = 'checkout_completed'")->fetchColumn();

    $recentQuery = "SELECT a.*, p.name AS product_name
        FROM public_user_activities a
        LEFT JOIN products p ON a.product_id = p.id
        ORDER BY a.created_at DESC
        LIMIT 50";
    $recentActivities = $db->query($recentQuery)->fetchAll(PDO::FETCH_ASSOC);

    $breakdownQuery = "SELECT activity_type, COUNT(*) AS total
        FROM public_user_activities
        GROUP BY activity_type
        ORDER BY total DESC";
    $activityBreakdown = $db->query($breakdownQuery)->fetchAll(PDO::FETCH_ASSOC);

    $popularQuery = "SELECT p.name, COUNT(*) AS total_actions
        FROM public_user_activities a
        INNER JOIN products p ON a.product_id = p.id
        WHERE a.product_id IS NOT NULL
        GROUP BY p.id, p.name
        ORDER BY total_actions DESC
        LIMIT 10";
    $popularProducts = $db->query($popularQuery)->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Admin Dashboard</h2>
            <p class="text-muted mb-0">Track public user activity across browsing, carts, and checkout.</p>
        </div>
        <span class="badge bg-dark">Activity Monitor</span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card text-white bg-primary"><div class="card-body"><h6>Total Activities</h6><h3><?php echo number_format($summary['total_activities']); ?></h3></div></div></div>
        <div class="col-md-3"><div class="card text-white bg-success"><div class="card-body"><h6>Unique Visitors</h6><h3><?php echo number_format($summary['unique_visitors']); ?></h3></div></div></div>
        <div class="col-md-3"><div class="card text-white bg-warning"><div class="card-body"><h6>Cart Actions</h6><h3><?php echo number_format($summary['cart_actions']); ?></h3></div></div></div>
        <div class="col-md-3"><div class="card text-white bg-danger"><div class="card-body"><h6>Completed Checkouts</h6><h3><?php echo number_format($summary['completed_checkouts']); ?></h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Recent Public User Activities</h5></div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead><tr><th>Time</th><th>Visitor</th><th>Activity</th><th>Product</th><th>Page</th><th>IP</th></tr></thead>
                        <tbody>
                            <?php if (empty($recentActivities)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No activities recorded yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($recentActivities as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['created_at']); ?></td>
                                    <td><code><?php echo htmlspecialchars(substr($activity['session_id'], 0, 10)); ?>...</code></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($activity['activity_type']); ?></span><br><small><?php echo htmlspecialchars($activity['activity_details'] ?? ''); ?></small></td>
                                    <td><?php echo htmlspecialchars($activity['product_name'] ?? '-'); ?></td>
                                    <td><small><?php echo htmlspecialchars($activity['page_url'] ?? '-'); ?></small></td>
                                    <td><small><?php echo htmlspecialchars($activity['ip_address'] ?? '-'); ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Activity Breakdown</h5></div><ul class="list-group list-group-flush"><?php foreach ($activityBreakdown as $row): ?><li class="list-group-item d-flex justify-content-between"><span><?php echo htmlspecialchars($row['activity_type']); ?></span><strong><?php echo number_format($row['total']); ?></strong></li><?php endforeach; ?></ul></div>
            <div class="card"><div class="card-header"><h5 class="mb-0">Most Engaged Products</h5></div><ul class="list-group list-group-flush"><?php foreach ($popularProducts as $row): ?><li class="list-group-item d-flex justify-content-between"><span><?php echo htmlspecialchars($row['name']); ?></span><strong><?php echo number_format($row['total_actions']); ?></strong></li><?php endforeach; ?></ul></div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
