<?php
require_once 'db.php';

// Authentication Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ' . url('login.php'));
        exit;
    }
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function isAdmin() {
    return getUserRole() === 'admin';
}

function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Database Check Functions
function needsSetup() {
    global $pdo;
    
    // If database file doesn't exist, setup is needed
    if (!file_exists(DB_FILE)) {
        return true;
    }
    
    // If tables don't exist, setup is needed
    try {
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        if (!$stmt->fetch()) {
            return true;
        }
        
        // If no users exist, setup is needed
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        if ($result['count'] == 0) {
            return true;
        }
    } catch (PDOException $e) {
        return true;
    }
    
    return false;
}

// Chore Management Functions
function getChoresByUrgency($pdo) {
    $today = date('Y-m-d');
    $chores = [
        'overdue' => [],
        'today' => [],
        'upcoming' => []
    ];
    
    $stmt = $pdo->prepare("
        SELECT c.*, 
               CASE WHEN ch.chore_id IS NOT NULL AND ch.completed_at IS NULL 
                    THEN 1 ELSE 0 END as in_progress
        FROM chores c
        LEFT JOIN chore_history ch ON c.id = ch.chore_id 
            AND ch.completed_at IS NULL
        WHERE c.next_due_date IS NOT NULL
        ORDER BY c.next_due_date ASC
    ");
    $stmt->execute();
    
    while ($chore = $stmt->fetch()) {
        if ($chore['next_due_date'] < $today) {
            $chores['overdue'][] = $chore;
        } elseif ($chore['next_due_date'] == $today) {
            $chores['today'][] = $chore;
        } else {
            $chores['upcoming'][] = $chore;
        }
    }
    
    return $chores;
}

function formatDate($date) {
    if (!$date) return '';
    
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    
    if ($date == $today) {
        return t('time_today');
    }
    if ($date == $tomorrow) {
        return t('time_tomorrow');
    }
    
    $diff = floor((strtotime($date) - strtotime($today)) / (60 * 60 * 24));
    if ($diff < 0) {
        return sprintf(t('time_days_ago'), abs($diff));
    }
    
    return date('d.m.Y', strtotime($date));
}

function formatDateTime($datetime) {
    if (!$datetime) return '';
    return date('d.m.Y H:i', strtotime($datetime));
}

// Database Setup Functions
function createDatabaseTables($pdo) {
    try {
        // Users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'user',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Chores table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS chores (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT,
                estimated_duration INTEGER,
                next_due_date DATE,
                is_recurring BOOLEAN DEFAULT 0,
                recurring_interval INTEGER,
                created_by INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id)
            )
        ");
        
        // Chore history table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS chore_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                chore_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                completed_at DATETIME,
                actual_duration INTEGER,
                due_date DATE,
                FOREIGN KEY (chore_id) REFERENCES chores(id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
        
        return true;
    } catch (PDOException $e) {
        error_log("Error creating database tables: " . $e->getMessage());
        return false;
    }
}

// Example data for initial setup
function addExampleChores($pdo, $userId) {
    $chores = [
        [
            'title' => 'Staubsaugen',
            'description' => 'Alle Räume staubsaugen',
            'estimated_duration' => 30,
            'next_due_date' => date('Y-m-d', strtotime('+1 day')),
            'is_recurring' => 1,
            'recurring_interval' => 7
        ],
        [
            'title' => 'Wäsche waschen',
            'description' => 'Wäsche waschen und aufhängen',
            'estimated_duration' => 60,
            'next_due_date' => date('Y-m-d'),
            'is_recurring' => 1,
            'recurring_interval' => 3
        ],
        [
            'title' => 'Pflanzen gießen',
            'description' => 'Alle Zimmerpflanzen gießen',
            'estimated_duration' => 15,
            'next_due_date' => date('Y-m-d', strtotime('+2 days')),
            'is_recurring' => 1,
            'recurring_interval' => 2
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO chores (
            title, description, estimated_duration, next_due_date,
            is_recurring, recurring_interval, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($chores as $chore) {
        try {
            $stmt->execute([
                $chore['title'],
                $chore['description'],
                $chore['estimated_duration'],
                $chore['next_due_date'],
                $chore['is_recurring'],
                $chore['recurring_interval'],
                $userId
            ]);
        } catch (PDOException $e) {
            error_log("Error adding example chore: " . $e->getMessage());
        }
    }
}

function calculateCompletionStats($pdo, $userId = null, $days = 30) {
    try {
        $params = [];
        $userCondition = '';
        
        if ($userId !== null) {
            $userCondition = 'AND ch.user_id = ?';
            $params[] = $userId;
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_completed,
                SUM(CASE WHEN ch.completed_at <= ch.due_date THEN 1 ELSE 0 END) as completed_on_time,
                COUNT(DISTINCT DATE(ch.completed_at)) as active_days
            FROM chore_history ch
            WHERE ch.completed_at >= DATE('now', '-{$days} days')
            AND ch.completed_at IS NOT NULL
            {$userCondition}
        ");
        
        $stmt->execute($params);
        $stats = $stmt->fetch();
        
        // Calculate streak
        $streak = 0;
        if ($userId !== null) {
            $streak = calculateUserStreak($pdo, $userId);
        }
        
        return [
            'total_completed' => (int)$stats['total_completed'],
            'completed_on_time' => (int)$stats['completed_on_time'],
            'active_days' => (int)$stats['active_days'],
            'streak' => $streak
        ];
    } catch (PDOException $e) {
        error_log("Error calculating completion stats: " . $e->getMessage());
        return [
            'total_completed' => 0,
            'completed_on_time' => 0,
            'active_days' => 0,
            'streak' => 0
        ];
    }
}

function calculateUserStreak($pdo, $userId) {
    try {
        // Get all completed chores for the user, ordered by completion date
        $stmt = $pdo->prepare("
            SELECT 
                DATE(completed_at) as completion_date,
                DATE(due_date) as due_date
            FROM chore_history
            WHERE user_id = ? 
            AND completed_at IS NOT NULL
            ORDER BY completed_at DESC
        ");
        $stmt->execute([$userId]);
        $completions = $stmt->fetchAll();
        
        if (empty($completions)) {
            return 0;
        }
        
        $streak = 0;
        $currentDate = new DateTime('today');
        $lastDate = null;
        
        foreach ($completions as $completion) {
            $completionDate = new DateTime($completion['completion_date']);
            $dueDate = new DateTime($completion['due_date']);
            
            // If this is the first iteration
            if ($lastDate === null) {
                // If the last completion was more than a day ago, break
                if ($currentDate->diff($completionDate)->days > 1) {
                    break;
                }
                $lastDate = $completionDate;
            } else {
                // If there's a gap in the dates, break
                if ($lastDate->diff($completionDate)->days > 1) {
                    break;
                }
            }
            
            // Only count if completed on or before due date
            if ($completionDate <= $dueDate) {
                $streak++;
            }
            
            $lastDate = $completionDate;
        }
        
        return $streak;
    } catch (PDOException $e) {
        error_log("Error calculating streak: " . $e->getMessage());
        return 0;
    }
}

function calculateTodayProgress($pdo, $userId = null) {
    try {
        $params = [];
        $userCondition = '';
        
        if ($userId !== null) {
            $userCondition = 'AND (ch.user_id = ? OR ch.user_id IS NULL)';
            $params[] = $userId;
        }
        
        // Get total chores due today
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM chores c
            LEFT JOIN chore_history ch ON c.id = ch.chore_id 
                AND DATE(ch.due_date) = DATE('now')
                AND ch.completed_at IS NULL
            WHERE (
                (c.next_due_date = DATE('now') AND ch.id IS NULL)
                OR DATE(ch.due_date) = DATE('now')
            )
            {$userCondition}
        ");
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Get completed chores for today
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as completed
            FROM chore_history ch
            WHERE DATE(ch.completed_at) = DATE('now')
            {$userCondition}
        ");
        $stmt->execute($params);
        $completed = $stmt->fetch()['completed'];
        
        return [
            'total' => (int)$total,
            'completed' => (int)$completed
        ];
    } catch (PDOException $e) {
        error_log("Error calculating today's progress: " . $e->getMessage());
        return [
            'total' => 0,
            'completed' => 0
        ];
    }
}
