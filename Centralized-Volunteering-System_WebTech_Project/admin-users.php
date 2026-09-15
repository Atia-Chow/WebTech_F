<?php
session_start();
require 'includes/db.php';

// Security Check: Ensure user is a Super Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- Handle User Deletion ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $deleteId = (int)$_POST['delete_user_id'];
    
    // Deleting from the users table. 
    // (Assuming your database uses ON DELETE CASCADE, this will also clean up their tickets/profiles)
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    
    if ($stmt->execute([$deleteId])) {
        header("Location: admin-users.php?deleted=success");
        exit();
    } else {
        $error = "Failed to delete user.";
    }
}

// --- Fetch all users (Volunteers & Organizations) ---
try {
    // We join the organizations table so we can display the org name if they have one
    $stmt = $pdo->query("
        SELECT u.id, u.full_name, u.email, u.phone, u.role, u.created_at,
               o.org_name
        FROM users u
        LEFT JOIN organizations o ON u.id = o.user_id
        WHERE u.role != 'admin'
        ORDER BY u.created_at DESC
    ");
    $allUsers = $stmt->fetchAll();
} catch (PDOException $e) {
    $allUsers = [];
}

// Fetch pending tickets for the sidebar notification
$pendingTickets = (int) $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'Pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ImpactAdmin - User Management</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dashboard-layout">
    <!-- LEFT SIDEBAR -->
    <aside class="sidebar" style="background: #1E293B; width: 260px; display: flex; flex-direction: column;">
        <div class="logo-container" style="color: white; margin-bottom: 2rem;">
            <i class="fa-solid fa-shield-halved" style="color: #3B82F6;"></i> ImpactAdmin
        </div>
        
        <nav class="sidebar-nav" style="flex: 1;">
            <a href="admin-dashboard.php" class="sidebar-link"><i class="fa-solid fa-border-all"></i> Dashboard Overview</a>
            <a href="admin-users.php" class="sidebar-link active"><i class="fa-solid fa-users"></i> User Management</a>
            <a href="admin-support.php" class="sidebar-link"><i class="fa-solid fa-headset"></i> Support & Helpline <?php if ($pendingTickets > 0): ?><span style="background:#EF4444;color:white;border-radius:10px;padding:1px 7px;font-size:0.7rem;margin-left:auto;"><?php echo $pendingTickets; ?></span><?php endif; ?></a>
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
            <input type="text" placeholder="Search user by name, email, or role...">
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="color: var(--dark-navy); margin: 0;">User Management</h3>
            <span style="background: var(--primary-blue); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">Total Users: <?php echo count($allUsers); ?></span>
        </div>

        <?php if (isset($_GET['deleted']) && $_GET['deleted'] === 'success'): ?>
            <div style="background: #FEE2E2; border: 1px solid #F87171; color: #B91C1C; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-check"></i> User successfully deleted from the database.
            </div>
        <?php endif; ?>

        <!-- DARK THEME CARDS GRID -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
            
            <?php foreach ($allUsers as $user): ?>
                <!-- Individual Dark Card -->
                <div style="background: #352d2d; border: 1px solid #2A2A2A; border-radius: 10px; padding: 1.5rem; position: relative;">
                    
                    <!-- Top Right Action Buttons -->
                    <div style="position: absolute; top: 1.5rem; right: 1.5rem; display: flex; gap: 0.5rem;">
                        <!-- Shield / Permission Button -->
                        <button style="background: transparent; border: 1px solid #333; color: #A0A0A0; width: 34px; height: 34px; border-radius: 6px; cursor: pointer; display: flex; align-items:center; justify-content: center; transition: 0.2s;" onmouseover="this.style.color='#fff'; this.style.borderColor='#555'" onmouseout="this.style.color='#A0A0A0'; this.style.borderColor='#333'">
                            <i class="fa-solid fa-shield"></i>
                        </button>
                        
                        <!-- Delete Form -->
                        <form method="POST" action="admin-users.php" style="margin: 0;" onsubmit="return confirm('Are you sure you want to permanently delete <?php echo htmlspecialchars(addslashes($user['full_name'])); ?>? This cannot be undone.');">
                            <input type="hidden" name="delete_user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" style="background: transparent; border: 1px solid #333; color: #A0A0A0; width: 34px; height: 34px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;" onmouseover="this.style.color='#EF4444'; this.style.borderColor='#EF4444'" onmouseout="this.style.color='#A0A0A0'; this.style.borderColor='#333'">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Card Details Grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 1.5rem 1rem;">
                        
                        <!-- Row 1 -->
                        <div>
                            <span style="font-size: 0.65rem; color: #777; text-transform: uppercase; letter-spacing: 1px;">Name</span>
                            <div style="font-size: 1.1rem; color: #FFFFFF; font-weight: 500; margin-top: 0.3rem;"><?php echo htmlspecialchars($user['full_name']); ?></div>
                        </div>
                        <div style="padding-right: 50px;">
                            <span style="font-size: 0.65rem; color: #777; text-transform: uppercase; letter-spacing: 1px;">Email</span>
                            <div style="font-size: 1rem; color: #CCCCCC; margin-top: 0.3rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($user['email']); ?>">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div>
                            <span style="font-size: 0.65rem; color: #777; text-transform: uppercase; letter-spacing: 1px;">Organisation</span>
                            <div style="font-size: 1rem; color: #CCCCCC; margin-top: 0.3rem;">
                                <?php echo $user['org_name'] ? htmlspecialchars($user['org_name']) : '—'; ?>
                            </div>
                        </div>
                        <div>
                            <span style="font-size: 0.65rem; color: #777; text-transform: uppercase; letter-spacing: 1px;">Phone</span>
                            <div style="font-size: 1rem; color: #CCCCCC; margin-top: 0.3rem;">
                                <?php echo $user['phone'] ? htmlspecialchars($user['phone']) : '—'; ?>
                            </div>
                        </div>

                        <!-- Row 3 -->
                        <div>
                            <span style="font-size: 0.65rem; color: #777; text-transform: uppercase; letter-spacing: 1px;">Category</span>
                            <div style="font-size: 0.85rem; color: #DC2626; margin-top: 0.3rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">
                                <?php echo $user['role'] === 'organization' ? 'Partner' : 'Standard'; ?>
                            </div>
                        </div>
                        <div>
                            <span style="font-size: 0.65rem; color: #777; text-transform: uppercase; letter-spacing: 1px;">Role</span>
                            <div style="font-size: 0.9rem; color: #CCCCCC; margin-top: 0.3rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                <?php echo htmlspecialchars($user['role']); ?>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </main>
</div>

</body>
</html>