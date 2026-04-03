<div class="panel" id="section-log">
    <div class="panel-head">
        <div class="panel-title"><i class="bi bi-activity"></i> Aktivitas Asisten AI</div>
        <span class="panel-badge">5 Log Terbaru</span>
    </div>
    <div class="panel-body" style="padding-top:1rem">
        <?php if (empty($list_log)): ?>
            <div class="empty-state" style="padding:2.5rem 1rem">
                <i class="bi bi-chat-square-dots"></i><p>Belum ada aktivitas chat AI.</p>
            </div>
        <?php else: ?>
        <div class="log-list">
            <?php foreach ($list_log as $log): 
                $resp = json_decode($log['ai_response'], true); 
                $reply_text = $resp['reply'] ?? '-'; 
            ?>
            <div class="log-card">
                <div class="log-card-head">
                    <div class="log-user-q"><i class="bi bi-person-fill"></i><span><?= htmlspecialchars($log['user_query']) ?></span></div>
                    <div class="log-time"><?= $log['created_at'] ?></div>
                </div>
                <div class="log-card-body">
                    <div class="log-ai-badge">AI Response</div>
                    <div class="log-reply"><?= htmlspecialchars($reply_text) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
