<?php
$status = opcache_get_status(true);
print_r($status['scripts']);