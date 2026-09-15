<?php
session_start();
require 'includes/db.php';

// Security Check: Ensure user is a Super Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- Handle Verify Organization action ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_org_id'])) {
    $orgId = (int) $_POST['verify_org_id'];
    $stmt = $pdo->prepare("UPDATE organizations SET is_verified = 1 WHERE id = ?");
    $stmt->execute([$orgId]);
    header("Location: admin_dashboard.php");
    exit();
}

// --- Top Stat Cards: live counts from the database ---
try {
    $pendingOrgApprovals = (int) $pdo->query("SELECT COUNT(*) FROM organizations WHERE is_verified = 0")->fetchColumn();
    $flaggedContent = 6; 
    $pendingTickets = (int) $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'Pending'")->fetchColumn();
    
    // Calculate total verified users (Volunteers + Verified Orgs)
    $totalVolunteers = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'volunteer'")->fetchColumn();
    $verifiedOrgs = (int) $pdo->query("SELECT COUNT(*) FROM organizations WHERE is_verified = 1")->fetchColumn();
    $totalUsers = $totalVolunteers + $verifiedOrgs;
} catch (PDOException $e) {
    $pendingOrgApprovals = $flaggedContent = $pendingTickets = $totalUsers = 0;
}

// --- User Account Administration: latest volunteers & organizations ---
try {
    $accounts = $pdo->query("
        SELECT u.id, u.full_name, u.email, u.role, u.created_at,
               o.id AS org_id, o.org_name, o.is_verified
        FROM users u
        LEFT JOIN organizations o ON o.user_id = u.id
        WHERE u.role IN ('volunteer', 'organization')
        ORDER BY u.created_at DESC
        LIMIT 15
    ")->fetchAll();
} catch (PDOException $e) {
    $accounts = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ImpactAdmin - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dashboard-layout">
    <!-- LEFT SIDEBAR -->
    <aside class="sidebar" style="background: #1E293B; width: 260px;">
        <div class="logo-container" style="color: white; margin-bottom: 2rem;">
            <i class="fa-solid fa-shield-halved" style="color: #3B82F6;"></i> ImpactAdmin
        </div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php" class="sidebar-link active"><i class="fa-solid fa-border-all"></i> Dashboard Overview</a>
          
            <a href="admin-users.php" class="sidebar-link"><i class="fa-solid fa-users"></i> User Management</a>
            <a href="admin-support.php" class="sidebar-link"><i class="fa-solid fa-headset"></i> Support & Helpline <?php if ($pendingTickets > 0): ?><span style="background:#EF4444;color:white;border-radius:10px;padding:1px 7px;font-size:0.7rem;margin-left:auto;"><?php echo $pendingTickets; ?></span><?php endif; ?></a>
           
        </nav>
        
        <div style="margin-top: auto; padding: 1rem; background: #0F172A; border-radius: 8px; display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
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
            <input type="text" placeholder="Search users, organizations, ticket IDs...">
        </div>

        <!-- NEW UPDATED STAT CARDS -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
            
            <div style="background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="background: #FFFBEB; color: #F59E0B; width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-regular fa-clock"></i>
                </div>
                <div style="text-align: right;">
                    <h2 style="margin: 0; font-size: 2.2rem; color: var(--dark-navy); font-weight: 700; line-height: 1.1;"><?php echo $pendingOrgApprovals; ?></h2>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">Pending Org Approvals</p>
                </div>
            </div>

            <div style="background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="background: #FEF2F2; color: #EF4444; width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-regular fa-flag"></i>
                </div>
                <div style="text-align: right;">
                    <h2 style="margin: 0; font-size: 2.2rem; color: var(--dark-navy); font-weight: 700; line-height: 1.1;"><?php echo $flaggedContent; ?></h2>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">Flagged Content / Reports</p>
                </div>
            </div>

            <div style="background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="background: #EFF6FF; color: #3B82F6; width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-regular fa-message"></i>
                </div>
                <div style="text-align: right;">
                    <h2 style="margin: 0; font-size: 2.2rem; color: var(--dark-navy); font-weight: 700; line-height: 1.1;"><?php echo $pendingTickets; ?></h2>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">Active Support Tickets</p>
                </div>
            </div>

            <div style="background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="background: #ECFDF5; color: #10B981; width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-regular fa-circle-check"></i>
                </div>
                <div style="text-align: right;">
                    <h2 style="margin: 0; font-size: 2.2rem; color: var(--dark-navy); font-weight: 700; line-height: 1.1;"><?php echo number_format($totalUsers); ?></h2>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">Total Verified Users</p>
                </div>
            </div>

        </div>

        <!-- User Admin -->
        <div class="opp-list">
            <h3 style="margin-bottom: 1rem;">User Account Administration</h3>
            <table class="data-table">
                <tr><th>Name / Org</th><th>Account Type</th><th>Email</th><th>Status</th><th>Actions</th></tr>
                <?php if (empty($accounts)): ?>
                <tr><td colspan="5" style="text-align:center; color: var(--text-muted);">No accounts found yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($accounts as $acc): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="applicant-avatar" style="background: <?php echo $acc['role'] === 'organization' ? '#0F172A' : '#3B82F6'; ?>; width: 30px; height: 30px;">
                                <?php
                                    $label = $acc['role'] === 'organization' && $acc['org_name'] ? $acc['org_name'] : $acc['full_name'];
                                    $parts = preg_split('/\s+/', trim($label));
                                    $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
                                    echo htmlspecialchars($initials);
                                ?>
                            </div>
                            <?php echo htmlspecialchars($acc['role'] === 'organization' && $acc['org_name'] ? $acc['org_name'] : $acc['full_name']); ?>
                        </div>
                    </td>
                    <td><?php echo $acc['role'] === 'organization' ? 'Organization' : 'Volunteer'; ?></td>
                    <td><?php echo htmlspecialchars($acc['email']); ?></td>
                    <td>
                        <?php if ($acc['role'] === 'organization'): ?>
                            <?php if ((int) $acc['is_verified'] === 1): ?>
                                <span class="status-badge status-success">Verified</span>
                            <?php else: ?>
                                <span class="status-badge status-pending">Pending</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="status-badge status-neutral">Active</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($acc['role'] === 'organization' && (int) $acc['is_verified'] === 0): ?>
                            <form method="POST" action="admin_dashboard.php" style="display:inline;">
                                <input type="hidden" name="verify_org_id" value="<?php echo (int) $acc['org_id']; ?>">
                                <button type="submit" class="btn-tiny btn-approve">Verify</button>
                            </form>
                        <?php else: ?>
                            <button class="btn-tiny btn-preview" disabled>Verified</button>
                        <?php endif; ?>
                        <button class="btn-tiny btn-takedown">Suspend</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </main>

</div>
</body>
</html>