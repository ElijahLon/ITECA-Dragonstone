<?php
session_start();
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';

// Check if user is logged in
$user = current_user();
if (!$user) {
    // Show access denied page for non-logged-in users
    include 'inc/header.php';
    ?>
    <style>
    body {
        background-color: #ffffff;
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        color: #333;
        line-height: 1.6;
    }
    .access-denied-container {
        max-width: 600px;
        margin: 100px auto;
        padding: 40px 20px;
        text-align: center;
    }
    .access-denied-title {
        font-size: 2rem;
        font-weight: 300;
        margin-bottom: 20px;
        color: #000;
    }
    .access-denied-message {
        font-size: 1.1rem;
        margin-bottom: 30px;
        color: #666;
    }
    .access-denied-buttons a {
        display: inline-block;
        margin: 0 10px;
        padding: 12px 30px;
        background-color: #000;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        transition: background-color 0.2s ease;
    }
    .access-denied-buttons a:hover {
        background-color: #333;
    }
    </style>
    <div class="access-denied-container">
        <h1 class="access-denied-title">Access Restricted</h1>
        <p class="access-denied-message">
            The Community Hub is available only to registered users with an active premium subscription.
            Please log in or register to access this feature.
        </p>
        <div class="access-denied-buttons">
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </div>
    </div>
    <?php
    include 'inc/footer.php';
    exit;
}

// Check if user has an active premium subscription
$db = db();
$stmt = $db->prepare('SELECT * FROM subscriptions WHERE user_id = ? AND active = 1 ORDER BY id DESC LIMIT 1');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$result = $stmt->get_result();
$subscription = $result->fetch_assoc();

// Allow access to community hub for any logged-in user (no subscription required)

// Handle new post submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $new_message = trim($_POST['message']);
    if (!empty($new_message)) {
        $stmt = $db->prepare("INSERT INTO community_posts (user_id, message) VALUES (?, ?)");
        $stmt->bind_param('is', $user['id'], $new_message);
        if ($stmt->execute()) {
            $message = 'Post submitted successfully!';

            // Award EcoPoints for community participation (5 points per post, max 5 per day)
            $today = date('Y-m-d');
            $stmt = $db->prepare('SELECT COUNT(*) as post_count FROM ecopoints_ledger WHERE user_id = ? AND reason = ? AND DATE(created_at) = ?');
            $reason = "Community post";
            $stmt->bind_param('iss', $user['id'], $reason, $today);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if ($result['post_count'] < 5) {
                $points = 5;
                // Update user eco_points
                $stmt = $db->prepare('UPDATE users SET eco_points = eco_points + ? WHERE user_id = ?');
                $stmt->bind_param('ii', $points, $user['id']);
                $stmt->execute();

                // Log in ecopoints_ledger
                $stmt = $db->prepare('INSERT INTO ecopoints_ledger (user_id, points, reason) VALUES (?, ?, ?)');
                $stmt->bind_param('iis', $user['id'], $points, $reason);
                $stmt->execute();
            }
        } else {
            $message = 'Error submitting post.';
        }
    } else {
        $message = 'Please enter a message.';
    }
}

// Fetch all community posts
$posts = [];
$stmt = $db->prepare("
    SELECT cp.message, cp.created_at, u.first_name, u.surname
    FROM community_posts cp
    JOIN users u ON cp.user_id = u.user_id
    ORDER BY cp.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}

include 'inc/header.php';
?>

<style>
body {
    background-color: #f7f9fa;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    color: #14171a;
    line-height: 1.5;
    margin: 0;
    padding: 0;
}

.community-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    background-color: #fff;
    min-height: 100vh;
    border-left: 1px solid #e1e8ed;
    border-right: 1px solid #e1e8ed;
}

.community-title {
    text-align: center;
    font-size: 1.75rem;
    font-weight: 800;
    margin-bottom: 30px;
    color: #14171a;
    padding: 20px 0;
    border-bottom: 1px solid #e1e8ed;
}

.posts-container {
    margin-bottom: 30px;
}

.post {
    border-bottom: 1px solid #e1e8ed;
    padding: 15px 0;
    transition: background-color 0.1s ease;
    display: flex;
    align-items: flex-start;
}

.post:hover {
    background-color: #f5f8fa;
}

.post-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #1da1f2;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    margin-right: 12px;
    flex-shrink: 0;
}

.post-content {
    flex: 1;
    min-width: 0;
}

.post-header {
    display: flex;
    align-items: center;
    margin-bottom: 4px;
}

.post-author {
    font-weight: 700;
    color: #14171a;
    font-size: 0.95rem;
    margin-right: 4px;
}

.post-handle {
    color: #657786;
    font-size: 0.9rem;
    margin-right: 4px;
}

.post-timestamp {
    color: #657786;
    font-size: 0.85rem;
}

.post-message {
    color: #14171a;
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 0;
    word-wrap: break-word;
}

.new-post-form {
    border: 1px solid #e1e8ed;
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 20px;
    background-color: #fff;
}

.new-post-form textarea {
    width: 100%;
    min-height: 80px;
    border: none;
    resize: none;
    font-family: inherit;
    font-size: 1.1rem;
    line-height: 1.4;
    color: #14171a;
    padding: 8px 0;
    outline: none;
    margin-bottom: 12px;
}

.new-post-form textarea::placeholder {
    color: #657786;
}

.new-post-form .form-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.new-post-form button {
    background-color: #1da1f2;
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.new-post-form button:hover {
    background-color: #1991db;
}

.new-post-form button:disabled {
    background-color: #aab8c2;
    cursor: not-allowed;
}

.char-count {
    color: #657786;
    font-size: 0.85rem;
}

.alert {
    padding: 12px 16px;
    margin-bottom: 20px;
    border-radius: 4px;
    font-weight: 500;
    font-size: 0.9rem;
}

.alert-success {
    background-color: #d1f2eb;
    color: #0c7d5f;
    border: 1px solid #a3d9cc;
}

.alert-error {
    background-color: #fadbd8;
    color: #a93226;
    border: 1px solid #f5b7b1;
}

@media (max-width: 768px) {
    .community-container {
        border: none;
        padding: 10px;
    }

    .community-title {
        font-size: 1.5rem;
        padding: 15px 0;
    }

    .post {
        padding: 12px 0;
    }

    .post-avatar {
        width: 36px;
        height: 36px;
        font-size: 1rem;
    }

    .new-post-form {
        padding: 12px;
    }
}
</style>

<div class="community-container">
    <h1 class="community-title">Community Hub</h1>

    <?php if ($message): ?>
        <div class="alert <?php echo strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form class="new-post-form" method="post">
        <textarea name="message" placeholder="What's happening?" maxlength="280" required></textarea>
        <div class="form-footer">
            <div class="char-count">0/280</div>
            <button type="submit">Tweet</button>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.querySelector('.new-post-form textarea');
        const charCount = document.querySelector('.char-count');
        const submitBtn = document.querySelector('.new-post-form button');

        function updateCharCount() {
            const count = textarea.value.length;
            charCount.textContent = count + '/280';
            if (count > 260) {
                charCount.style.color = '#e0245e';
            } else if (count > 240) {
                charCount.style.color = '#ffad1f';
            } else {
                charCount.style.color = '#657786';
            }
            submitBtn.disabled = count === 0 || count > 280;
        }

        textarea.addEventListener('input', updateCharCount);
        updateCharCount();
    });
    </script>

    <div class="posts-container">
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <div class="post-avatar">
                    <?php echo htmlspecialchars(substr($post['first_name'], 0, 1) . substr($post['surname'], 0, 1)); ?>
                </div>
                <div class="post-content">
                    <div class="post-header">
                        <span class="post-author"><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['surname']); ?></span>
                        <span class="post-handle">@<?php echo htmlspecialchars(strtolower($post['first_name'] . $post['surname'])); ?></span>
                        <span class="post-timestamp">· <?php echo date('M j', strtotime($post['created_at'])); ?></span>
                    </div>
                    <p class="post-message">
                        <?php echo htmlspecialchars($post['message']); ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'inc/footer.php'; ?>
