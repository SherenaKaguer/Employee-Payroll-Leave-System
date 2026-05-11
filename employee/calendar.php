<?php
require_once '../includes/header.php';
get_header("My Leave Calendar");

$user_id = $_SESSION['user_id'];

// Calendar logic
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$first_day = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day);
$month_name = date('F', $first_day);
$day_of_week = date('w', $first_day);

// Get MY leaves for this month
$start_date = "$year-$month-01";
$end_date = "$year-$month-$days_in_month";

$leaves_query = "SELECT * FROM leave_requests 
                 WHERE employee_id = $user_id 
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
        min-height: 100px;
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
        font-size: 0.65rem;
        padding: 4px 8px;
        border-radius: 6px;
        margin-bottom: 4px;
        font-weight: 800;
        text-transform: uppercase;
    }
    .event-approved { background: #ecfdf5; color: #10b981; border-left: 3px solid #10b981; }
    .event-pending { background: #fffbeb; color: #f59e0b; border-left: 3px solid #f59e0b; }
    .event-rejected { background: #fef2f2; color: #ef4444; border-left: 3px solid #ef4444; }
</style>

<div class="dashboard-grid" style="grid-template-columns: 2fr 1fr; gap: 30px;">
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
                for ($i = 0; $i < $day_of_week; $i++) echo '<div class="cal-day other-month"></div>';

                for ($d = 1; $d <= $days_in_month; $d++) {
                    $is_today = ($d == date('j') && $month == date('n') && $year == date('Y'));
                    $current_date = sprintf("%04d-%02d-%02d", $year, $month, $d);
                    
                    echo '<div class="cal-day ' . ($is_today ? 'today' : '') . '">';
                    echo '<div class="cal-date">' . $d . '</div>';
                    
                    foreach($calendar_data as $l) {
                        if ($current_date >= $l['start_date'] && $current_date <= $l['end_date']) {
                            $status = strtolower($l['status']);
                            echo '<div class="cal-event event-' . $status . '" title="' . ucfirst($l['leave_type']) . ' (' . $l['status'] . ')">';
                            echo ucfirst($l['leave_type']);
                            echo '</div>';
                        }
                    }
                    echo '</div>';
                }

                $remaining = (7 - (($day_of_week + $days_in_month) % 7)) % 7;
                for ($i = 0; $i < $remaining; $i++) echo '<div class="cal-day other-month"></div>';
                ?>
            </div>
        </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 30px;">
        <div class="card">
            <div class="card-header">
                <h3>Legend</h3>
            </div>
            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 15px; height: 15px; border-radius: 4px; background: #ecfdf5; border-left: 3px solid #10b981;"></div>
                        <span style="font-size: 0.85rem; font-weight: 600;">Approved Leave</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 15px; height: 15px; border-radius: 4px; background: #fffbeb; border-left: 3px solid #f59e0b;"></div>
                        <span style="font-size: 0.85rem; font-weight: 600;">Pending Review</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 15px; height: 15px; border-radius: 4px; background: #fef2f2; border-left: 3px solid #ef4444;"></div>
                        <span style="font-size: 0.85rem; font-weight: 600;">Rejected/Cancelled</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; border: none;">
            <div class="card-body" style="padding: 30px; text-align: center;">
                <i class="fas fa-plane-departure" style="font-size: 2rem; margin-bottom: 15px; opacity: 0.8;"></i>
                <h4 style="margin: 0 0 10px 0; font-weight: 800;">Planning a Trip?</h4>
                <p style="font-size: 0.8rem; opacity: 0.9; margin-bottom: 20px;">Check your balance and submit a new request in seconds.</p>
                <a href="leave_request.php" class="btn btn-primary btn-block" style="background: white; color: var(--primary); border: none;">Request Leave</a>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3><i class="fas fa-th" style="color: var(--primary); margin-right: 10px;"></i> Attendance Year-at-a-Glance</h3>
    </div>
    <div class="card-body">
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 20px;">Personal attendance distribution for <strong><?php echo date('Y'); ?></strong>. Colored cells indicate approved leave periods.</p>
        
        <div class="year-heatmap">
            <?php
            // Get all approved leaves for the year
            $y_start = date('Y') . '-01-01';
            $y_end = date('Y') . '-12-31';
            $y_leaves_q = mysqli_query($conn, "SELECT start_date, end_date FROM leave_requests WHERE employee_id = $user_id AND status = 'approved' AND (start_date <= '$y_end' AND end_date >= '$y_start')");
            $leave_days = [];
            while($yl = mysqli_fetch_assoc($y_leaves_q)) {
                $begin = new DateTime($yl['start_date']);
                $finish = new DateTime($yl['end_date']);
                $interval = new DateInterval('P1D');
                $daterange = new DatePeriod($begin, $interval, $finish->modify('+1 day'));
                foreach($daterange as $date) {
                    if ($date->format('Y') == date('Y')) {
                        $leave_days[] = $date->format('Y-m-d');
                    }
                }
            }

            // Render 365 days
            $year_begin = new DateTime(date('Y') . '-01-01');
            $today_str = date('Y-m-d');
            for ($i = 0; $i < 365; $i++) {
                $curr = clone $year_begin;
                $curr->modify("+$i days");
                $c_str = $curr->format('Y-m-d');
                
                $h_class = in_array($c_str, $leave_days) ? 'hd-leave' : '';
                if ($c_str == $today_str) $h_class .= ' hd-today';
                
                echo '<div class="heatmap-day ' . $h_class . '" title="' . $curr->format('M d, Y') . '"></div>';
            }
            ?>
        </div>
        
        <div style="display: flex; gap: 15px; margin-top: 15px; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">
            <div style="display: flex; align-items: center; gap: 5px;"><div class="heatmap-day" style="width: 10px; height: 10px;"></div> Working</div>
            <div style="display: flex; align-items: center; gap: 5px;"><div class="heatmap-day hd-leave" style="width: 10px; height: 10px;"></div> Approved Leave</div>
            <div style="display: flex; align-items: center; gap: 5px;"><div class="heatmap-day hd-today" style="width: 10px; height: 10px; border-width: 1px;"></div> Today</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
