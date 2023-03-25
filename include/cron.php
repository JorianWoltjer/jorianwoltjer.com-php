<?php
// This script is ran every hour using a Cron Job

// Clear views table (only used for rate-limiting)
sql_query("DELETE FROM views");
