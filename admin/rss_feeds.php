<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: " . APP_URL . "/index.php");
    exit;
}

$feeds = getRssFeeds();

// Handle adding new feed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_feed'])) {
    $title = trim($_POST['title']);
    $url = trim($_POST['url']);
    
    if (empty($title) || empty($url)) {
        $_SESSION['message'] = "Title and URL are required";
        $_SESSION['message_type'] = "danger";
    } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
        $_SESSION['message'] = "Invalid URL format";
        $_SESSION['message_type'] = "danger";
    } else {
        $success = addRssFeed($title, $url, $_SESSION['user_id']);
        $_SESSION['message'] = $success ? "RSS feed added successfully" : "Failed to add RSS feed";
        $_SESSION['message_type'] = $success ? "success" : "danger";
        header("Location: rss_feeds.php");
        exit;
    }
}

// Handle deleting feed
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM rss_feeds WHERE id = ?");
        $success = $stmt->execute([$_GET['id']]);
        $_SESSION['message'] = $success ? "RSS feed deleted successfully" : "Failed to delete RSS feed";
        $_SESSION['message_type'] = $success ? "success" : "danger";
        header("Location: rss_feeds.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error deleting RSS feed: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
        header("Location: rss_feeds.php");
        exit;
    }
}

$pageTitle = "Manage RSS Feeds";
require_once '../includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Manage RSS Feeds</h1>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Add New Feed</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="url" class="form-label">URL</label>
                            <input type="url" class="form-control" id="url" name="url" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="add_feed" class="btn btn-primary">Add Feed</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Current Feeds</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($feeds)): ?>
                        <p class="text-center">No RSS feeds configured</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($feeds as $feed): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($feed['title']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($feed['url']); ?></small>
                                    </div>
                                    <a href="rss_feeds.php?action=delete&id=<?php echo $feed['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this feed?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>