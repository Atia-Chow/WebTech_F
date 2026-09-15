<?php
session_start();
require 'includes/db.php';

// Security Check: Ensure user is a Super Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle Admin Reply Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'], $_POST['reply_message'])) {
    $ticketId = (int)$_POST['ticket_id'];
    $reply = trim($_POST['reply_message']);
    
    if ($reply !== '') {
        $stmt = $pdo->prepare("UPDATE support_tickets SET admin_reply = ?, status = 'Replied', replied_at = NOW() WHERE id = ?");
        $stmt->execute([$reply, $ticketId]);
    }
    header("Location: admin-support.php");
    exit();
}

// Fetch Live Stats for the 3 Cards
try {
    $totalTickets = (int) $pdo->query("SELECT COUNT(*) FROM support_tickets")->fetchColumn();
    $pendingTickets = (int) $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'Pending'")->fetchColumn();
    $repliedTickets = (int) $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'Replied'")->fetchColumn();
} catch (PDOException $e) {
    $totalTickets = $pendingTickets = $repliedTickets = 0;
}

// Fetch all tickets with user information
try {
    $stmt = $pdo->query("
        SELECT t.*, u.full_name, u.role, u.email 
        FROM support_tickets t 
        JOIN users u ON t.user_id = u.id 
        ORDER BY t.created_at DESC
    ");
    $tickets = $stmt->fetchAll();
} catch (PDOException $e) {
    $tickets = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ImpactAdmin - Support & Helpline</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dashboard-layout">
    <!-- LEFT SIDEBAR (Perfectly Synced) -->
    <aside class="sidebar" style="background: #1E293B; width: 260px; display: flex; flex-direction: column;">
        <div class="logo-container" style="color: white; margin-bottom: 2rem;">
            <i class="fa-solid fa-shield-halved" style="color: #3B82F6;"></i> ImpactAdmin
        </div>
        
        <nav class="sidebar-nav" style="flex: 1;">
            <a href="admin-dashboard.php" class="sidebar-link"><i class="fa-solid fa-border-all"></i> Dashboard Overview</a>
            <a href="#" class="sidebar-link"><i class="fa-solid fa-users"></i> User Management</a>
            <a href="admin-support.php" class="sidebar-link active"><i class="fa-solid fa-headset"></i> Support & Helpline <?php if ($pendingTickets > 0): ?><span style="background:#EF4444;color:white;border-radius:10px;padding:1px 7px;font-size:0.7rem;margin-left:auto;"><?php echo $pendingTickets; ?></span><?php endif; ?></a>
        </nav>
        
        <div style="padding: 1rem; background: #0F172A; border-radius: 8px; display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <div style="background: #3B82F6; padding: 8px; border-radius: 50%; color: white; font-size: 0.8rem;">SA</div>
            <div>
                <div style="font-size: 0.9rem; font-weight: bold; color: white;">Super Admin</div>
                <div style="font-size: 0.75rem; color: #10B981;">Full Access</div>
            </div>
        </div>
        <a href="logout.php" class="sidebar-link sidebar-logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-dashboard" style="background: #F1F5F9;">
        <div class="top-search" style="margin-bottom: 2rem;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Search tickets...">
        </div>

        <!-- 3 STAT CARDS -->
        <div class="causes-grid" style="grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
            <div style="background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="background: #EFF6FF; color: #3B82F6; width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-regular fa-message"></i>
                </div>
                <div style="text-align: right;">
                    <h2 style="margin: 0; font-size: 2.2rem; color: var(--dark-navy); font-weight: 700; line-height: 1.1;"><?php echo $totalTickets; ?></h2>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">Total Tickets</p>
                </div>
            </div>

            <div style="background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="background: #FFFBEB; color: #F59E0B; width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-regular fa-clock"></i>
                </div>
                <div style="text-align: right;">
                    <h2 style="margin: 0; font-size: 2.2rem; color: var(--dark-navy); font-weight: 700; line-height: 1.1;"><?php echo $pendingTickets; ?></h2>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">Pending Replies</p>
                </div>
            </div>

            <div style="background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="background: #ECFDF5; color: #10B981; width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-regular fa-circle-check"></i>
                </div>
                <div style="text-align: right;">
                    <h2 style="margin: 0; font-size: 2.2rem; color: var(--dark-navy); font-weight: 700; line-height: 1.1;"><?php echo $repliedTickets; ?></h2>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">Replied</p>
                </div>
            </div>
        </div>

        <!-- TICKETS LIST -->
        <div style="background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <h3 style="margin-bottom: 0.5rem; color: var(--dark-navy);">Support Tickets</h3>
            
            <!-- Filter Pills -->
            <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
                <button class="filter-btn" onclick="filterTickets('All', this)" style="background: var(--primary-blue); color: white; border: none; padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; cursor: pointer;">All (<?php echo $totalTickets; ?>)</button>
                <button class="filter-btn" onclick="filterTickets('Pending', this)" style="background: #FFFBEB; color: #F59E0B; border: none; padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; cursor: pointer; opacity: 0.5;">Pending (<?php echo $pendingTickets; ?>)</button>
                <button class="filter-btn" onclick="filterTickets('Replied', this)" style="background: #ECFDF5; color: #10B981; border: none; padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; cursor: pointer; opacity: 0.5;">Replied (<?php echo $repliedTickets; ?>)</button>
            </div>

            <!-- Dynamic Ticket Display -->
            <div id="ticket-container">
                <?php if (empty($tickets)): ?>
                    <p id="empty-state" style="color: var(--text-muted); font-size: 0.9rem;">No tickets in this view.</p>
                <?php else: ?>
                    <p id="empty-state" style="color: var(--text-muted); font-size: 0.9rem; display: none;">No tickets in this view.</p>
                    
                    <?php foreach ($tickets as $t): ?>
                        <div class="ticket-row" data-status="<?php echo $t['status']; ?>" style="border: 1px solid var(--border-color); padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem;">
                            
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                <div>
                                    <h4 style="margin: 0; color: var(--dark-navy);"><?php echo htmlspecialchars($t['subject']); ?></h4>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">From: <strong><?php echo htmlspecialchars($t['full_name']); ?></strong> (<?php echo ucfirst($t['role']); ?>) - <?php echo date('M j, Y g:i A', strtotime($t['created_at'])); ?></span>
                                </div>
                                <?php if ($t['status'] === 'Replied'): ?>
                                    <span style="background: #D1FAE5; color: #065F46; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">Replied</span>
                                <?php else: ?>
                                    <span style="background: #FEF3C7; color: #D97706; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">Pending</span>
                                <?php endif; ?>
                            </div>

                            <p style="font-size: 0.9rem; color: var(--text-main); background: #F8FAFC; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                                <?php echo nl2br(htmlspecialchars($t['message'])); ?>
                            </p>

                            <?php if ($t['status'] === 'Pending'): ?>
                                <!-- Reply Form for Pending Tickets -->
                                <form method="POST" action="admin-support.php" style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                    <input type="hidden" name="ticket_id" value="<?php echo $t['id']; ?>">
                                    <input type="text" name="reply_message" class="form-control" placeholder="Write your reply to <?php echo htmlspecialchars($t['full_name']); ?>..." required style="flex: 1; padding: 10px; border: 1px solid #E2E8F0; border-radius: 6px;">
                                    <button type="submit" style="background: var(--primary-blue); color: white; border: none; padding: 10px 16px; border-radius: 6px; font-weight: 600; cursor: pointer;"><i class="fa-solid fa-paper-plane"></i> Reply</button>
                                </form>
                            <?php else: ?>
                                <!-- Show Admin's past reply -->
                                <div style="background: #EFF6FF; border-left: 3px solid var(--primary-blue); padding: 1rem; border-radius: 6px;">
                                    <strong style="font-size: 0.85rem; color: var(--primary-blue);"><i class="fa-solid fa-reply"></i> Your Reply:</strong>
                                    <p style="font-size: 0.9rem; color: var(--dark-navy); margin: 0.5rem 0 0 0;"><?php echo nl2br(htmlspecialchars($t['admin_reply'])); ?></p>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
function filterTickets(status, btnElement) {
    const rows = document.querySelectorAll('.ticket-row');
    let visibleCount = 0;

    rows.forEach(row => {
        if (status === 'All' || row.getAttribute('data-status') === status) {
            row.style.display = 'block';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Show or hide the "No tickets" message
    document.getElementById('empty-state').style.display = (visibleCount === 0) ? 'block' : 'none';

    // Update active button styling
    const buttons = document.querySelectorAll('.filter-btn');
    buttons.forEach(btn => btn.style.opacity = '0.5'); // dim all
    btnElement.style.opacity = '1'; // highlight active
}
</script>

</body>
</html>