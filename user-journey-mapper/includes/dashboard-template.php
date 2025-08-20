<div class="wrap">
    <h1><span class="dashicons dashicons-chart-line"></span> User Journey Mapper</h1>

    <div style="display: flex; gap: 30px; margin-top: 20px;">
        <div class="card">
            <h2>Total Sessions</h2>
            <p><?php echo esc_html($total_sessions); ?></p>
        </div>
        <div class="card">
            <h2>Average Duration</h2>
            <p><?php echo esc_html($average_duration); ?> seconds</p>
        </div>
    </div>

    <h2 style="margin-top: 40px;">User Journey Timeline</h2>
    <canvas id="ujmJourneyChart" width="100%" height="50"></canvas>

    <h2 style="margin-top: 40px;">Top Exit Pages</h2>
    <table class="widefat fixed striped">
        <thead>
            <tr><th>Page URL</th><th>Exit Count</th></tr>
        </thead>
        <tbody>
            <?php foreach ($exit_pages as $page): ?>
                <tr>
                    <td><?php echo esc_url($page->page_url); ?></td>
                    <td><?php echo intval($page->exits); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

