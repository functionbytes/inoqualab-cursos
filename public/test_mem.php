<?php

echo 'Memory limit: '.ini_get('memory_limit').PHP_EOL;
echo 'Peak memory: '.memory_get_peak_usage(true) / 1024 / 1024 .'MB'.PHP_EOL;
