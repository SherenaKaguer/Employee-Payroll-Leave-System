<?php
require_once '../includes/header.php';
get_header("Workforce Calendar");

// Calendar logic
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$first_day = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day);
$month_name = date('F', $first_day);
$day_of_week = date('w', $first_day);

// Get leaves for this month
$start_date = "$year-$month-01";
$end_date = "$year-$month-$days_in_month";

$leaves_query = "SELECT lr.*, e.full_name 
                 FROM leave_requests lr 
                 JOIN employees e ON lr.employee_id = e.id 
                 WHERE lr.status = 'approved' 
                 AND (
                    (start_date BETWEEN '$start_date' AND '$end_date') OR 
                    (end_date BETWEEN '$start_date' AND '$end_date') OR
                    (start_date <= '$start_date' AND end_date >= '$end_date')
                 )";
$leaves_res = mysqli_query($conn, $leaves_query);
$calendar_data = [];
while($l = mysqli_fetch_assoc($leaves_res)) {
    $calendar_data[] = $l;
}

// Helper for prev/next
$prev_month = $month == 1 ? 12 : $month - 1;
$prev_year = $month == 1 ? $year - 1 : $year;
$next_month = $month == 12 ? 1 : $month + 1;
$next_year = $month == 12 ? $year + 1 : $year;
?>

<style>
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: var(--border);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
    }
    .cal-day-head {
        background: #f8fafc;
        padding: 15px;
        text-align: center;
        font-weight: 800;
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .cal-day {
        background: white;
        min-height: 120px;
        padding: 10px;
        position: relative;
    }
    .cal-day.today { background: #f0f9ff; }
    .cal-day.other-month { background: #f8fafc; opacity: 0.5; }
    .cal-date {
        font-weight: 800;
        font-size: 0.9rem;
        margin-bottom: 10px;
        color: var(--text-muted);
    }
    .today .cal-date { color: var(--primary); }
    
    .cal-event {
        font-size: 0.7rem;
        padding: 4px 8px;
        border-radius: 6px;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 700;
    }
    .event-annual { background: #eef2ff; color: #6366f1; border-left: 3px solid #6366f1; }
    .event-sick { background: #fef2f2; color: #ef4444; border-left: 3px solid #ef4444; }
    .event-unpaid { background: #f1f5f9; color: #475569; border-left: 3px solid #475569; }
</style>

<div class="card">
    <div class="card-header">
        <div style="display: flex; align-items: center; gap: 20px;">
            <h3><i class="fas fa-calendar-alt" style="color: var(--primary); margin-right: 10px;"></i> <?php echo $month_name . ' ' . $year; ?></h3>
            <div style="display: flex; gap: 5px;">
                <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="btn btn-outline" style="padding: 5px 12px;"><i class="fas fa-chevron-left"></i></a>
                <a href="?month=<?php echo date('n'); ?>&year=<?php echo date('Y'); ?>" class="btn btn-outline" style="padding: 5px 15px; font-size: 0.8rem;">Today</a>
                <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="btn btn-outline" style="padding: 5px 12px;"><i class="fas fa-chevron-right"></i></a>
            </div>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="calendar-grid">
            <div class="cal-day-head">Sun</div>
            <div class="cal-day-head">Mon</div>
            <div class="cal-day-head">Tue</div>
            <div class="cal-day-head">Wed</div>
            <div class="cal-day-head">Thu</div>
            <div class="cal-day-head">Fri</div>
            <div class="cal-day-head">Sat</div>

            <?php
            // Padding for first week
            for ($i = 0; $i < $day_of_week; $i++) {
                echo '<div class="cal-day other-month"></div>';
            }

            // Days of month
            $today_full = date('Y-m-d');
            for ($d = 1; $d <= $days_in_month; $d++) {
                $is_today = ($d == date('j') && $month == date('n') && $year == date('Y'));
                $current_date = sprintf("%04d-%02d-%02d", $year, $month, $d);
                
                echo '<div class="cal-day ' . ($is_today ? 'today' : '') . '">';
                echo '<div class="cal-date">' . $d . '</div>';
                
                // Show events
                $ev_count = 0;
                foreach($calendar_data as $l) {
                    if ($current_date >= $l['start_date'] && $current_date <= $l['end_date']) {
                        $ev_count++;
                        if ($ev_count <= 3) {
                            $class = 'event-' . strtolower($l['leave_type']);
                            echo '<div class="cal-event ' . $class . '" title="' . $l['full_name'] . ' (' . $l['leave_type'] . ')">';
                            echo htmlspecialchars(explode(' ', $l['full_name'])[0]);
                            echo '</div>';
                        }
                    }
                }
                if ($ev_count > 3) {
                    echo '<div style="font-size: 0.6rem; color: var(--text-muted); font-weight: 800; text-align: center;">+' . ($ev_count - 3) . ' more</div>';
                }
                
                echo '</div>';
            }

            // Padding for last week
            $remaining = (7 - (($day_of_week + $days_in_month) % 7)) % 7;
            for ($i = 0; $i < $remaining; $i++) {
                echo '<div class="cal-day other-month"></div>';
            }
            ?>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3><i class="fas fa-th" style="color: var(--primary); margin-right: 10px;"></i> Workforce Availability Heatmap</h3>
    </div>
    <div class="card-body">
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 20px;">Visual density of staff presence for <strong><?php echo $month_name; ?></strong>. Darker cells indicate higher availability.</p>
        <div class="heatmap-grid">
            <?php
            for ($d = 1; $d <= $days_in_month; $d++) {
                $current_date = sprintf("%04d-%02d-%02d", $year, $month, $d);
                $leaves_on_day = 0;
                foreach($calendar_data as $l) {
                    if ($current_date >= $l['start_date'] && $current_date <= $l['end_date']) {
                        $leaves_on_day++;
                    }
                }
                
                $total_active = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM employees WHERE is_active = 1"))['count'];
                $presence = $total_active - $leaves_on_day;
                $ratio = $total_active > 0 ? ($presence / $total_active) : 1;
                
                $hm_class = 'hm-high';
                if ($ratio < 0.5) $hm_class = 'hm-low';
                elseif ($ratio < 0.8) $hm_class = 'hm-mid';
                if ($leaves_on_day > ($total_active * 0.5)) $hm_class = 'hm-out';
                
                echo '<div class="heatmap-cell ' . $hm_class . '" title="' . date('M d', strtotime($current_date)) . ': ' . $presence . '/' . $total_active . ' available"></div>';
            }
            ?>
        </div>
        <div style="display: flex; gap: 15px; margin-top: 20px; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">
            <div style="display: flex; align-items: center; gap: 5px;"><div class="heatmap-cell hm-high" style="width: 12px; height: 12px;"></div> High Presence</div>
            <div style="display: flex; align-items: center; gap: 5px;"><div class="heatmap-cell hm-mid" style="width: 12px; height: 12px;"></div> Moderate</div>
            <div style="display: flex; align-items: center; gap: 5px;"><div class="heatmap-cell hm-low" style="width: 12px; height: 12px;"></div> Low Staffing</div>
            <div style="display: flex; align-items: center; gap: 5px;"><div class="heatmap-cell hm-out" style="width: 12px; height: 12px;"></div> Critical Gap</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
