<?php

$opcacheConfig = opcache_get_configuration();
$jitEnabled = $opcacheConfig['directives']['opcache.jit'];

echo "JIT status: " . ($jitEnabled ? "Enabled" : "Disabled") . PHP_EOL;

function fibonacci($n)
{
    if ($n <= 1) {
        return $n;
    }
    return fibonacci($n - 1) + fibonacci($n - 2);
}

$iterations = 1000;

// Executar o teste de desempenho
$start_time = microtime(true);

for ($i = 0; $i < $iterations; $i++) {
    fibonacci(20);
}

$end_time = microtime(true);

$execution_time = $end_time - $start_time;
echo "Tempo de execução: {$execution_time} segundos" . PHP_EOL;
