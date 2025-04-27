<?php
// Connect to database
require_once 'includes/config.php';
require_once 'includes/auth.php';

$pageTitle = "Manage RSS Feeds";
require_once 'includes/header.php';

// Fetch all RSS feeds
try {
    $stmt = $pdo->query("SELECT id, title, url, added_by, added_date, is_active FROM rss_feeds");
    $feeds = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error fetching RSS feeds: ' . $e->getMessage());
}
?>

<div class="container">
    <h1 class="mb-4">Cyber Security News Feed</h1>

    <?php if (empty($feeds)): ?>
        <div class="alert alert-info">
            No RSS feeds available.
        </div>
    <?php else: ?>

<div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                <th>ID</th>
                <th>Title</th>
                <th>URL</th>
            </tr>
                    </thead>
                    <tbody id="devicesTableBody">
                    <?php foreach ($feeds as $feed): ?>
                <tr>
                    <td><?php echo htmlspecialchars($feed['id']); ?></td>
                    <td><?php echo htmlspecialchars($feed['title']); ?></td>
                    <td><a href="<?php echo htmlspecialchars($feed['url']); ?>" target="_blank"><?php echo htmlspecialchars($feed['url']); ?></a></td>
                </tr>
            <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>











    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>
