<?php
// DB Migration Script - run via browser: http://localhost/EMS5/run_migration.php
require_once 'config.php';

$results = [];

function exec_sql($connection, $sql, $desc) {
    $result = $connection->query($sql);
    if ($result === false) {
        return "❌ FAILED - $desc: " . $connection->error;
    }
    return "✅ OK - $desc";
}

function col_exists($connection, $table, $col) {
    $r = $connection->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
    return $r && $r->num_rows > 0;
}

// events.tier
if (!col_exists($connection, 'events', 'tier')) {
    $results[] = exec_sql($connection, "ALTER TABLE events ADD COLUMN tier ENUM('Ordinary','Premium') DEFAULT 'Ordinary'", "Add events.tier");
} else { $results[] = "⏭ SKIP - events.tier already exists"; }

// registrations.amount_paid
if (!col_exists($connection, 'registrations', 'amount_paid')) {
    $results[] = exec_sql($connection, "ALTER TABLE registrations ADD COLUMN amount_paid DECIMAL(10,2) DEFAULT 0.00", "Add registrations.amount_paid");
} else { $results[] = "⏭ SKIP - registrations.amount_paid already exists"; }

// registrations.payment_status
if (!col_exists($connection, 'registrations', 'payment_status')) {
    $results[] = exec_sql($connection, "ALTER TABLE registrations ADD COLUMN payment_status VARCHAR(50) DEFAULT 'Pending'", "Add registrations.payment_status");
} else { $results[] = "⏭ SKIP - registrations.payment_status already exists"; }

// bookings.price
if (!col_exists($connection, 'bookings', 'price')) {
    $results[] = exec_sql($connection, "ALTER TABLE bookings ADD COLUMN price DECIMAL(10,2) DEFAULT 0.00", "Add bookings.price");
} else { $results[] = "⏭ SKIP - bookings.price already exists"; }

// bookings.tier
if (!col_exists($connection, 'bookings', 'tier')) {
    $results[] = exec_sql($connection, "ALTER TABLE bookings ADD COLUMN tier ENUM('Ordinary','Premium') DEFAULT 'Ordinary'", "Add bookings.tier");
} else { $results[] = "⏭ SKIP - bookings.tier already exists"; }

// bookings.amount_paid
if (!col_exists($connection, 'bookings', 'amount_paid')) {
    $results[] = exec_sql($connection, "ALTER TABLE bookings ADD COLUMN amount_paid DECIMAL(10,2) DEFAULT 0.00", "Add bookings.amount_paid");
} else { $results[] = "⏭ SKIP - bookings.amount_paid already exists"; }

// bookings.payment_status
if (!col_exists($connection, 'bookings', 'payment_status')) {
    $results[] = exec_sql($connection, "ALTER TABLE bookings ADD COLUMN payment_status VARCHAR(50) DEFAULT 'Pending'", "Add bookings.payment_status");
} else { $results[] = "⏭ SKIP - bookings.payment_status already exists"; }

// Update events tier based on price
$results[] = exec_sql($connection, "UPDATE events SET tier='Premium' WHERE price >= 100 AND tier='Ordinary'", "Set Premium tier for high-price events");
$results[] = exec_sql($connection, "UPDATE events SET tier='Ordinary' WHERE price < 100", "Set Ordinary tier for low-price events");

// Update booking prices by type
$results[] = exec_sql($connection, "UPDATE bookings SET price=5000.00, tier='Premium' WHERE event_type IN ('Wedding','Wedding Reception') AND price=0.00", "Set Wedding prices");
$results[] = exec_sql($connection, "UPDATE bookings SET price=3000.00, tier='Premium' WHERE event_type IN ('Corporate Event','Corporate Meeting') AND price=0.00", "Set Corporate prices");
$results[] = exec_sql($connection, "UPDATE bookings SET price=1500.00, tier='Ordinary' WHERE event_type IN ('Birthday Party','Baby Shower','Graduation Party') AND price=0.00", "Set Birthday prices");
$results[] = exec_sql($connection, "UPDATE bookings SET price=800.00, tier='Ordinary' WHERE event_type IN ('Anniversary','Reunion Dinner','Holiday Party') AND price=0.00", "Set Celebration prices");
$results[] = exec_sql($connection, "UPDATE bookings SET price=1200.00, tier='Ordinary' WHERE price=0.00", "Set default prices");

// Mark paid bookings
$results[] = exec_sql($connection, "UPDATE bookings SET amount_paid=price, payment_status='Paid' WHERE status IN ('Approved','Completed') AND (amount_paid=0.00 OR amount_paid IS NULL)", "Mark paid bookings");

// Create earnings view
$connection->query("DROP VIEW IF EXISTS earnings_summary");
$results[] = exec_sql($connection, "CREATE VIEW earnings_summary AS
    SELECT 'Booking Revenue' AS source, COUNT(*) AS total_transactions, SUM(amount_paid) AS total_earned,
    SUM(CASE WHEN tier='Premium' THEN amount_paid ELSE 0 END) AS premium_earned,
    SUM(CASE WHEN tier='Ordinary' THEN amount_paid ELSE 0 END) AS ordinary_earned
    FROM bookings WHERE payment_status='Paid'", "Create earnings_summary view");

echo "<h2>Migration Results</h2><ul>";
foreach ($results as $r) {
    echo "<li>$r</li>";
}
echo "</ul><p><strong>Migration complete!</strong> You can delete this file now.</p>";
?>
